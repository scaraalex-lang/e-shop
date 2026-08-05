<?php

namespace Modules\Commerce\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Commerce\Enums\StatoPagamento;
use Modules\Commerce\Models\Carrello;
use Modules\Commerce\Models\MovimentoCredito;
use Modules\Commerce\Models\Ordine;
use Modules\Commerce\Tests\Concerns\CreaSoggetti;
use Tests\TestCase;

/**
 * I crediti dell'agenzia spesi su articoli fisici a catalogo, non solo sui
 * servizi editor (vedi CreaOrdine::da). Cambio fisso 1 credito = 100
 * centesimi. Un ordine può pagarsi anche in parte: il resto passa dal
 * metodo di pagamento scelto, come oggi.
 */
class CreditiCatalogoTest extends TestCase
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

    private function prodotto(array $attributi = []): Product
    {
        $categoria = Category::firstOrCreate(['slug' => 'test-crediti-catalogo'], ['name' => 'Test crediti catalogo']);

        return Product::create(array_merge([
            'category_id' => $categoria->id,
            'sku' => 'CRED-'.uniqid(),
            'slug' => 'cred-'.uniqid(),
            'name' => 'Articolo di prova',
            'price' => 1000, // 10,00 € a pezzo, 25 pezzi = 250,00 €
            'is_active' => true,
        ], $attributi));
    }

    private function conSaldo(int $crediti): User
    {
        $referente = $this->referenteAgenzia();

        MovimentoCredito::create([
            'agenzia_id' => $referente->agenzia->id,
            'quantita' => $crediti,
            'causale' => 'Ricarica di prova',
        ]);

        return $referente;
    }

    private function ordina(User $referente, array $extra = []): Ordine
    {
        $carrello = Carrello::firstOrCreate(['user_id' => $referente->id]);
        $carrello->righe()->create(['product_id' => $this->prodotto()->id, 'quantita' => 25]);

        $this->actingAs($referente)->post('/ordine/conferma', array_merge(self::CONSEGNA, [
            'metodo_pagamento' => 'contrassegno',
        ], $extra));

        return Ordine::where('user_id', $referente->id)->latest()->firstOrFail();
    }

    public function test_un_ordine_coperto_interamente_dai_crediti_e_pagato_subito_senza_incasso(): void
    {
        $referente = $this->conSaldo(300); // totale ordine: 250,00 € = 250 crediti

        $ordine = $this->ordina($referente, ['crediti_usati' => 250]);

        $this->assertSame(250, $ordine->crediti_usati);
        $this->assertSame(0, $ordine->valoreInDenaro());
        $this->assertSame(StatoPagamento::Pagato, $ordine->stato_pagamento);
        $this->assertNotNull($ordine->pagato_at);
        $this->assertSame(50, $referente->agenzia->fresh()->creditiSaldo());
    }

    public function test_un_ordine_misto_lascia_il_resto_da_pagare_col_metodo_scelto(): void
    {
        $referente = $this->conSaldo(100); // meno del totale (250 crediti): resta un resto in denaro

        $ordine = $this->ordina($referente, ['crediti_usati' => 100, 'metodo_pagamento' => 'fattura']);

        $this->assertSame(100, $ordine->crediti_usati);
        $this->assertSame(15_000, $ordine->valoreInDenaro()); // 250,00€ - 100€ di crediti = 150,00€
        // Il resto è a fattura: non incassato subito, ma l'ordine parte comunque.
        $this->assertSame(StatoPagamento::InAttesa, $ordine->stato_pagamento);
        $this->assertSame(0, $referente->agenzia->fresh()->creditiSaldo());
    }

    public function test_i_crediti_richiesti_oltre_il_saldo_vengono_capati_al_saldo_disponibile(): void
    {
        $referente = $this->conSaldo(40);

        $ordine = $this->ordina($referente, ['crediti_usati' => 500]);

        $this->assertSame(40, $ordine->crediti_usati);
        $this->assertSame(0, $referente->agenzia->fresh()->creditiSaldo());
    }

    public function test_i_crediti_richiesti_oltre_il_totale_non_sforano_lordine(): void
    {
        $referente = $this->conSaldo(10_000); // saldo enorme, molto più del totale (250 crediti)

        $ordine = $this->ordina($referente, ['crediti_usati' => 9_000]);

        // Capato al totale dell'ordine, non a quanto richiesto né al saldo.
        $this->assertSame(250, $ordine->crediti_usati);
        $this->assertSame(0, $ordine->valoreInDenaro());
        $this->assertSame(StatoPagamento::Pagato, $ordine->stato_pagamento);
        $this->assertSame(9_750, $referente->agenzia->fresh()->creditiSaldo());
    }

    public function test_un_movimento_credito_e_registrato_e_agganciato_allordine(): void
    {
        $referente = $this->conSaldo(300);

        $ordine = $this->ordina($referente, ['crediti_usati' => 250]);

        $movimento = MovimentoCredito::where('ordine_id', $ordine->id)->sole();
        $this->assertSame(-250, $movimento->quantita);
    }

    public function test_un_privato_non_ha_agenzia_da_cui_attingere_crediti(): void
    {
        $privato = User::factory()->create();
        $carrello = Carrello::firstOrCreate(['user_id' => $privato->id]);
        $carrello->righe()->create(['product_id' => $this->prodotto()->id, 'quantita' => 1]);

        $this->actingAs($privato)->post('/ordine/conferma', array_merge(self::CONSEGNA, [
            'metodo_pagamento' => 'contrassegno',
            'crediti_usati' => 999,
        ]));

        $ordine = Ordine::where('user_id', $privato->id)->latest()->firstOrFail();
        $this->assertSame(0, $ordine->crediti_usati);
    }
}
