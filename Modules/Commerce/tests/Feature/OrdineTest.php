<?php

namespace Modules\Commerce\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Commerce\Enums\MetodoPagamento;
use Modules\Commerce\Enums\StatoOrdine;
use Modules\Commerce\Enums\StatoPagamento;
use Modules\Commerce\Models\Carrello;
use Modules\Commerce\Models\Ordine;
use Modules\Commerce\Models\ScaglionePrezzo;
use Modules\Commerce\Tests\Concerns\CreaSoggetti;
use Tests\TestCase;

class OrdineTest extends TestCase
{
    use CreaSoggetti, RefreshDatabase;

    private const CONSEGNA = [
        'nome' => 'Giulia Ferrari',
        'telefono' => '3391234567',
        'indirizzo' => 'Via Manzoni 4',
        'cap' => '20121',
        'citta' => 'Milano',
        'provincia' => 'mi',
    ];

    private function prodotto(array $attributi = []): Product
    {
        $categoria = Category::firstOrCreate(
            ['slug' => 'articoli-trigesimali'],
            ['name' => 'Articoli trigesimali'],
        );

        return Product::create(array_merge([
            'category_id' => $categoria->id,
            'sku' => 'TEST-'.uniqid(),
            'slug' => 'test-'.uniqid(),
            'name' => 'Articolo di prova',
            'price' => 2600,
            'is_active' => true,
        ], $attributi));
    }

    private function conCarrello(array $righe): User
    {
        $utente = User::factory()->create();
        $carrello = Carrello::create(['user_id' => $utente->id]);

        foreach ($righe as [$prodotto, $quantita]) {
            $carrello->righe()->create(['product_id' => $prodotto->id, 'quantita' => $quantita]);
        }

        return $utente;
    }

    public function test_il_checkout_chiede_di_accedere(): void
    {
        $this->get('/ordine/conferma')->assertRedirect(route('login'));
    }

    public function test_con_il_carrello_vuoto_si_torna_al_carrello(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/ordine/conferma')
            ->assertRedirect(route('carrello'));
    }

    public function test_un_privato_conclude_un_ordine_con_contrassegno(): void
    {
        $prodotto = $this->prodotto(['price' => 4900]);
        $utente = $this->conCarrello([[$prodotto, 2]]);

        $risposta = $this->actingAs($utente)->post('/ordine/conferma', array_merge(self::CONSEGNA, [
            'metodo_pagamento' => 'contrassegno',
        ]));

        $ordine = Ordine::firstOrFail();
        $risposta->assertRedirect(route('ordine', $ordine));

        $this->assertSame(9800, $ordine->totale_merce);
        $this->assertSame(0, $ordine->spedizione, 'sopra i 79 € la spedizione è inclusa');
        $this->assertSame(9800, $ordine->totale);
        $this->assertSame(MetodoPagamento::Contrassegno, $ordine->metodo_pagamento);
        $this->assertSame(StatoPagamento::InAttesa, $ordine->stato_pagamento, 'il contrassegno si salda alla consegna');
        $this->assertSame('MI', $ordine->consegna_provincia);
        $this->assertStringStartsWith('MEM-', $ordine->numero);
    }

    public function test_il_carrello_si_svuota_e_i_prezzi_restano_scritti_sull_ordine(): void
    {
        $prodotto = $this->prodotto(['price' => 2600]);
        $utente = $this->conCarrello([[$prodotto, 3]]);

        $this->actingAs($utente)->post('/ordine/conferma', array_merge(self::CONSEGNA, [
            'metodo_pagamento' => 'contrassegno',
        ]));

        $this->assertDatabaseCount('righe_carrello', 0);

        // il listino cambia DOPO l'ordine: la riga non si muove
        $prodotto->update(['price' => 9900]);

        $riga = Ordine::firstOrFail()->righe()->first();
        $this->assertSame(7800, $riga->prezzo);
        $this->assertSame('Articolo di prova', $riga->nome);
        $this->assertSame($prodotto->sku, $riga->sku);
    }

    public function test_la_carta_incassa_subito_e_una_carta_rifiutata_non_manda_avanti_l_ordine(): void
    {
        $prodotto = $this->prodotto(['price' => 4900]);

        // carta che finisce per 0: il simulatore la rifiuta
        $utente = $this->conCarrello([[$prodotto, 1]]);
        $this->actingAs($utente)
            ->post('/ordine/conferma', array_merge(self::CONSEGNA, [
                'metodo_pagamento' => 'carta',
                'carta' => '4242424242424240',
            ]))
            ->assertSessionHasErrors('carta');

        $rifiutato = Ordine::firstOrFail();
        $this->assertSame(StatoPagamento::Fallito, $rifiutato->stato_pagamento);
        $this->assertSame(StatoOrdine::Nuovo, $rifiutato->stato, 'un ordine non pagato non parte');

        // carta buona
        $altro = $this->conCarrello([[$prodotto, 1]]);
        $this->actingAs($altro)->post('/ordine/conferma', array_merge(self::CONSEGNA, [
            'metodo_pagamento' => 'carta',
            'carta' => '4242 4242 4242 4242',
        ]));

        $pagato = Ordine::where('user_id', $altro->id)->firstOrFail();
        $this->assertSame(StatoPagamento::Pagato, $pagato->stato_pagamento);
        $this->assertNotNull($pagato->pagato_at);
        $this->assertStringStartsWith('SIM-', $pagato->riferimento_pagamento);
    }

    public function test_un_privato_non_puo_scegliere_la_fattura(): void
    {
        $utente = $this->conCarrello([[$this->prodotto(), 1]]);

        $this->actingAs($utente)
            ->post('/ordine/conferma', array_merge(self::CONSEGNA, ['metodo_pagamento' => 'fattura']))
            ->assertSessionHasErrors('metodo_pagamento');

        $this->assertDatabaseCount('ordini', 0);
    }

    public function test_un_ordine_con_articoli_da_personalizzare_va_in_lavorazione(): void
    {
        $conFoto = $this->prodotto(['is_photo_printable' => true, 'price' => 4900]);
        $utente = $this->conCarrello([[$conFoto, 1]]);

        $this->actingAs($utente)->post('/ordine/conferma', array_merge(self::CONSEGNA, [
            'metodo_pagamento' => 'contrassegno',
        ]));

        $ordine = Ordine::firstOrFail();
        $this->assertTrue($ordine->richiede_lavorazione);
        $this->assertSame(StatoOrdine::InLavorazione, $ordine->stato);
        $this->assertTrue($ordine->righe->first()->richiede_foto);
    }

    public function test_un_ordine_senza_foto_va_dritto_in_produzione(): void
    {
        $utente = $this->conCarrello([[$this->prodotto(['price' => 8000]), 1]]);

        $this->actingAs($utente)->post('/ordine/conferma', array_merge(self::CONSEGNA, [
            'metodo_pagamento' => 'contrassegno',
        ]));

        $ordine = Ordine::firstOrFail();
        $this->assertFalse($ordine->richiede_lavorazione);
        $this->assertSame(StatoOrdine::InProduzione, $ordine->stato);
    }

    public function test_la_spedizione_si_paga_sotto_i_79_euro(): void
    {
        $utente = $this->conCarrello([[$this->prodotto(['price' => 2600]), 1]]);

        $this->actingAs($utente)->post('/ordine/conferma', array_merge(self::CONSEGNA, [
            'metodo_pagamento' => 'contrassegno',
        ]));

        $ordine = Ordine::firstOrFail();
        $this->assertSame(790, $ordine->spedizione);
        $this->assertSame(3390, $ordine->totale);
    }

    public function test_lo_sconto_agenzia_finisce_scritto_sulla_riga(): void
    {
        $prodotto = $this->prodotto(['price' => 2600]);
        ScaglionePrezzo::create([
            'product_id' => $prodotto->id, 'quantita_minima' => 25, 'sconto_percentuale' => 10,
        ]);

        $referente = $this->referenteAgenzia();
        $carrello = Carrello::create(['user_id' => $referente->id]);
        $carrello->righe()->create(['product_id' => $prodotto->id, 'quantita' => 30]);

        $this->actingAs($referente)->post('/ordine/conferma', array_merge(self::CONSEGNA, [
            'metodo_pagamento' => 'fattura',
        ]));

        $ordine = Ordine::firstOrFail();
        $riga = $ordine->righe->first();

        $this->assertSame(78_000, $riga->prezzo_pieno);
        $this->assertSame(70_200, $riga->prezzo);
        $this->assertSame('10.00', (string) $riga->sconto_percentuale);
        $this->assertNotNull($ordine->agenzia_id, 'l\'agenzia resta scritta sull\'ordine');
        $this->assertSame(0, $ordine->spedizione, 'per le agenzie la spedizione è inclusa');
    }

    public function test_un_ordine_di_un_altro_non_si_vede(): void
    {
        $utente = $this->conCarrello([[$this->prodotto(), 1]]);
        $this->actingAs($utente)->post('/ordine/conferma', array_merge(self::CONSEGNA, [
            'metodo_pagamento' => 'contrassegno',
        ]));
        $ordine = Ordine::firstOrFail();

        $this->actingAs(User::factory()->create())
            ->get("/account/ordini/{$ordine->numero}")
            ->assertNotFound();
    }

    public function test_i_numeri_d_ordine_non_si_ripetono(): void
    {
        foreach (range(1, 3) as $i) {
            $utente = $this->conCarrello([[$this->prodotto(['price' => 9000]), 1]]);
            $this->actingAs($utente)->post('/ordine/conferma', array_merge(self::CONSEGNA, [
                'metodo_pagamento' => 'contrassegno',
            ]));
        }

        $numeri = Ordine::pluck('numero');
        $this->assertCount(3, $numeri->unique(), 'tre ordini, tre numeri diversi');
        $this->assertSame(
            [sprintf('MEM-%d-0001', date('Y')), sprintf('MEM-%d-0002', date('Y')), sprintf('MEM-%d-0003', date('Y'))],
            $numeri->sort()->values()->all(),
        );
    }
}
