<?php

namespace Modules\Commerce\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Commerce\Enums\MetodoPagamento;
use Modules\Commerce\Enums\StatoOrdine;
use Modules\Commerce\Enums\StatoPagamento;
use Modules\Commerce\Models\Ordine;
use Tests\TestCase;

/**
 * Il webhook Stripe è l'unico posto in cui un pagamento a redirect diventa
 * "confermato" sull'ordine — vedi StripeWebhookController. Questi test
 * coprono il punto di sicurezza critico (firma verificata) e l'idempotenza
 * (Stripe può reinviare lo stesso evento più volte).
 */
class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const SEGRETO = 'whsec_test_segreto';

    protected function setUp(): void
    {
        parent::setUp();

        config(['commerce.stripe.webhook_secret' => self::SEGRETO]);
    }

    private function ordineACarta(StatoPagamento $statoPagamento = StatoPagamento::InAttesa): Ordine
    {
        $utente = User::factory()->create();

        return Ordine::create([
            'numero' => 'MEM-'.date('Y').'-'.random_int(1000, 9999),
            'user_id' => $utente->id,
            'metodo_pagamento' => MetodoPagamento::Carta,
            'stato_pagamento' => $statoPagamento,
            'totale_pieno' => 4900,
            'totale_merce' => 4900,
            'spedizione' => 0,
            'totale' => 4900,
            'consegna_nome' => 'Prova',
            'consegna_telefono' => '000',
            'consegna_indirizzo' => 'Via Prova 1',
            'consegna_cap' => '20100',
            'consegna_citta' => 'Milano',
            'consegna_provincia' => 'MI',
        ]);
    }

    /** Stesso algoritmo di Stripe\WebhookSignature: HMAC-SHA256 di "timestamp.payload". */
    private function firma(string $payload, ?int $timestamp = null): string
    {
        $timestamp ??= time();
        $firma = hash_hmac('sha256', "{$timestamp}.{$payload}", self::SEGRETO);

        return "t={$timestamp},v1={$firma}";
    }

    private function payloadSessioneCompletata(Ordine $ordine, string $paymentIntent = 'pi_test_123'): string
    {
        return json_encode([
            'id' => 'evt_test_1',
            'object' => 'event',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_1',
                    'object' => 'checkout.session',
                    'payment_intent' => $paymentIntent,
                    'metadata' => ['ordine_id' => (string) $ordine->id],
                ],
            ],
        ]);
    }

    public function test_una_firma_non_valida_viene_rifiutata_e_non_tocca_l_ordine(): void
    {
        $ordine = $this->ordineACarta();
        $payload = $this->payloadSessioneCompletata($ordine);

        $this->call('POST', '/webhook/stripe', [], [], [], [
            'HTTP_Stripe-Signature' => 't='.time().',v1=firma-fasulla',
            'CONTENT_TYPE' => 'application/json',
        ], $payload)->assertStatus(400);

        $this->assertSame(StatoPagamento::InAttesa, $ordine->fresh()->stato_pagamento);
    }

    public function test_nessuna_firma_viene_rifiutata(): void
    {
        $ordine = $this->ordineACarta();
        $payload = $this->payloadSessioneCompletata($ordine);

        $this->call('POST', '/webhook/stripe', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $payload)->assertStatus(400);

        $this->assertSame(StatoPagamento::InAttesa, $ordine->fresh()->stato_pagamento);
    }

    public function test_checkout_completato_conferma_il_pagamento_e_avvia_l_ordine(): void
    {
        $ordine = $this->ordineACarta();
        $payload = $this->payloadSessioneCompletata($ordine, 'pi_test_ok');

        $this->call('POST', '/webhook/stripe', [], [], [], [
            'HTTP_Stripe-Signature' => $this->firma($payload),
            'CONTENT_TYPE' => 'application/json',
        ], $payload)->assertOk();

        $ordine->refresh();
        $this->assertSame(StatoPagamento::Pagato, $ordine->stato_pagamento);
        $this->assertSame('pi_test_ok', $ordine->riferimento_pagamento);
        $this->assertNotNull($ordine->pagato_at);
        $this->assertSame(StatoOrdine::InProduzione, $ordine->stato, 'senza articoli da lavorare va dritto in produzione');
    }

    public function test_lo_stesso_evento_ricevuto_due_volte_e_innocuo(): void
    {
        $ordine = $this->ordineACarta();
        $payload = $this->payloadSessioneCompletata($ordine, 'pi_test_dup');
        $headers = ['HTTP_Stripe-Signature' => $this->firma($payload), 'CONTENT_TYPE' => 'application/json'];

        $this->call('POST', '/webhook/stripe', [], [], [], $headers, $payload)->assertOk();
        $this->call('POST', '/webhook/stripe', [], [], [], $headers, $payload)->assertOk();

        $ordine->refresh();
        $this->assertSame(StatoPagamento::Pagato, $ordine->stato_pagamento);
        $this->assertSame('pi_test_dup', $ordine->riferimento_pagamento);
    }

    public function test_sessione_scaduta_segna_il_pagamento_fallito(): void
    {
        $ordine = $this->ordineACarta();

        $payload = json_encode([
            'id' => 'evt_test_2',
            'object' => 'event',
            'type' => 'checkout.session.expired',
            'data' => [
                'object' => [
                    'id' => 'cs_test_2',
                    'object' => 'checkout.session',
                    'metadata' => ['ordine_id' => (string) $ordine->id],
                ],
            ],
        ]);

        $this->call('POST', '/webhook/stripe', [], [], [], [
            'HTTP_Stripe-Signature' => $this->firma($payload),
            'CONTENT_TYPE' => 'application/json',
        ], $payload)->assertOk();

        $this->assertSame(StatoPagamento::Fallito, $ordine->fresh()->stato_pagamento);
    }

    public function test_un_ordine_gia_pagato_non_si_tocca_su_un_evento_di_fallimento_tardivo(): void
    {
        $ordine = $this->ordineACarta(StatoPagamento::Pagato);
        $ordine->registraPagamento('pi_già_pagato');

        $payload = json_encode([
            'id' => 'evt_test_3',
            'object' => 'event',
            'type' => 'checkout.session.expired',
            'data' => [
                'object' => [
                    'id' => 'cs_test_3',
                    'object' => 'checkout.session',
                    'metadata' => ['ordine_id' => (string) $ordine->id],
                ],
            ],
        ]);

        $this->call('POST', '/webhook/stripe', [], [], [], [
            'HTTP_Stripe-Signature' => $this->firma($payload),
            'CONTENT_TYPE' => 'application/json',
        ], $payload)->assertOk();

        $this->assertSame(StatoPagamento::Pagato, $ordine->fresh()->stato_pagamento, 'un ordine già pagato non torna fallito');
    }
}
