<?php

namespace Modules\Commerce\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Catalog\Database\Seeders\ServiziNecrologioSeeder;
use Modules\Catalog\Models\Product;
use Modules\Commerce\Enums\Occasione;
use Modules\Commerce\Enums\StatoOrdine;
use Modules\Commerce\Models\Carrello;
use Modules\Commerce\Models\MovimentoCredito;
use Modules\Commerce\Models\Ordine;
use Modules\Commerce\Tests\Concerns\CreaSoggetti;
use Tests\TestCase;

/**
 * I servizi (funerale/trigesimo/anniversario): prodotti che costano crediti
 * invece di soldi, e portano l'occasione sull'ordine — vedi
 * [[Occasione]] e [[CreaOrdine]].
 */
class ServizioNecrologioTest extends TestCase
{
    use CreaSoggetti, RefreshDatabase;

    private const CONSEGNA = [
        'nome' => 'Giulia Ferrari',
        'telefono' => '3391234567',
        'indirizzo' => 'Via Manzoni 4',
        'cap' => '20121',
        'citta' => 'Milano',
        'provincia' => 'MI',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        (new ServiziNecrologioSeeder)->run();
    }

    private function accreditaCrediti(\App\Models\User $referente, int $quantita): void
    {
        MovimentoCredito::create([
            'agenzia_id' => $referente->agenzia_id,
            'quantita' => $quantita,
            'causale' => 'Accredito di prova',
        ]);
    }

    private function servizio(string $sku): Product
    {
        return Product::where('sku', $sku)->firstOrFail();
    }

    public function test_aprire_il_servizio_funerale_crea_un_ordine_con_l_occasione_e_consuma_i_crediti(): void
    {
        $referente = $this->referenteAgenzia(attributi: ['ordine_minimo_pezzi' => 0]);
        $this->accreditaCrediti($referente, 50);

        $carrello = Carrello::firstOrCreate(['user_id' => $referente->id]);
        $carrello->righe()->create(['product_id' => $this->servizio('SRV-NECRO-FUNERALE')->id, 'quantita' => 1]);

        $this->actingAs($referente)->post('/ordine/conferma', array_merge(self::CONSEGNA, [
            'metodo_pagamento' => 'fattura',
        ]));

        $ordine = Ordine::firstOrFail();
        $this->assertSame(Occasione::Funerale, $ordine->occasione);
        $this->assertTrue($ordine->richiede_lavorazione, 'un servizio deve poter aprire la lavorazione');
        $this->assertSame(StatoOrdine::InLavorazione, $ordine->stato);

        $movimento = MovimentoCredito::where('ordine_id', $ordine->id)->firstOrFail();
        $this->assertSame(-20, $movimento->quantita);
        $this->assertSame(30, $referente->agenzia->fresh()->creditiSaldo());
    }

    public function test_il_servizio_anniversario_porta_il_numero_sull_ordine(): void
    {
        $referente = $this->referenteAgenzia(attributi: ['ordine_minimo_pezzi' => 0]);
        $this->accreditaCrediti($referente, 50);

        $this->actingAs($referente)->post('/carrello', [
            'product_id' => $this->servizio('SRV-NECRO-ANNIVERSARIO')->id,
            'quantita' => 1,
            'numero_anniversario' => 2,
        ]);

        $this->actingAs($referente)->post('/ordine/conferma', array_merge(self::CONSEGNA, [
            'metodo_pagamento' => 'fattura',
        ]));

        $ordine = Ordine::firstOrFail();
        $this->assertSame(Occasione::Anniversario, $ordine->occasione);
        $this->assertSame(2, $ordine->numero_anniversario);
        $this->assertSame(2, $ordine->righe->first()->numero_anniversario);
    }

    public function test_l_anniversario_senza_numero_non_si_aggiunge_al_carrello(): void
    {
        $referente = $this->referenteAgenzia(attributi: ['ordine_minimo_pezzi' => 0]);

        $this->actingAs($referente)->post('/carrello', [
            'product_id' => $this->servizio('SRV-NECRO-ANNIVERSARIO')->id,
            'quantita' => 1,
        ]);

        $this->assertDatabaseCount('righe_carrello', 0);
    }

    public function test_crediti_insufficienti_bloccano_il_checkout(): void
    {
        $referente = $this->referenteAgenzia(attributi: ['ordine_minimo_pezzi' => 0]);
        $this->accreditaCrediti($referente, 5); // il funerale ne costa 20

        $carrello = Carrello::firstOrCreate(['user_id' => $referente->id]);
        $carrello->righe()->create(['product_id' => $this->servizio('SRV-NECRO-FUNERALE')->id, 'quantita' => 1]);

        $this->actingAs($referente)->post('/ordine/conferma', array_merge(self::CONSEGNA, [
            'metodo_pagamento' => 'fattura',
        ]));

        $this->assertDatabaseCount('ordini', 0);
        $this->assertSame(5, $referente->agenzia->fresh()->creditiSaldo());
    }

    public function test_un_secondo_servizio_di_occasione_diversa_non_si_aggiunge_al_carrello(): void
    {
        $referente = $this->referenteAgenzia(attributi: ['ordine_minimo_pezzi' => 0]);

        $this->actingAs($referente)->post('/carrello', [
            'product_id' => $this->servizio('SRV-NECRO-FUNERALE')->id,
            'quantita' => 1,
        ]);
        $this->actingAs($referente)->post('/carrello', [
            'product_id' => $this->servizio('SRV-NECRO-TRIGESIMO')->id,
            'quantita' => 1,
        ]);

        $this->assertDatabaseCount('righe_carrello', 1);
        $riga = Carrello::where('user_id', $referente->id)->first()->righe->first();
        $this->assertSame('SRV-NECRO-FUNERALE', $riga->product->sku);
    }

    public function test_un_privato_senza_agenzia_non_puo_aprire_un_servizio(): void
    {
        $privato = \App\Models\User::factory()->create();

        $carrello = Carrello::firstOrCreate(['user_id' => $privato->id]);
        $carrello->righe()->create(['product_id' => $this->servizio('SRV-NECRO-FUNERALE')->id, 'quantita' => 1]);

        $this->actingAs($privato)->post('/ordine/conferma', array_merge(self::CONSEGNA, [
            'metodo_pagamento' => 'contrassegno',
        ]));

        $this->assertDatabaseCount('ordini', 0);
    }
}
