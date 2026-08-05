<?php

namespace Modules\Commerce\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Commerce\Enums\StatoPagamento;
use Modules\Commerce\Models\Carrello;
use Modules\Commerce\Models\Ordine;
use Modules\Commerce\Tests\Concerns\CreaSoggetti;
use Tests\TestCase;

/**
 * La contabilità dell'agenzia: emissione e saldo delle fatture (vedi
 * Ordine::emettiFattura/registraPagamento), e le due viste che le mostrano —
 * gestione/agenzie/{agenzia}/movimenti (staff) e account/fatture (agenzia).
 */
class ContabilitaAgenziaTest extends TestCase
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
        $categoria = Category::firstOrCreate(['slug' => 'test-contabilita'], ['name' => 'Test contabilità']);

        return Product::create(array_merge([
            'category_id' => $categoria->id,
            'sku' => 'CONT-'.uniqid(),
            'slug' => 'cont-'.uniqid(),
            'name' => 'Articolo di prova',
            'price' => 4900,
            'is_active' => true,
        ], $attributi));
    }

    /** Un ordine a fattura vero, passando dal checkout come farebbe un'agenzia (25 pezzi: sopra il minimo di 20). */
    private function ordineAFattura(User $referente): Ordine
    {
        $carrello = Carrello::firstOrCreate(['user_id' => $referente->id]);
        $carrello->righe()->create(['product_id' => $this->prodotto()->id, 'quantita' => 25]);

        $this->actingAs($referente)->post('/ordine/conferma', array_merge(self::CONSEGNA, [
            'metodo_pagamento' => 'fattura',
        ]));

        return Ordine::where('user_id', $referente->id)->latest()->firstOrFail();
    }

    public function test_lo_staff_emette_fattura_su_un_ordine_a_termini(): void
    {
        $referente = $this->referenteAgenzia();
        $ordine = $this->ordineAFattura($referente);

        $this->actingAs($this->staff())
            ->post("/gestione/ordini/{$ordine->numero}/fattura", ['fattura_numero' => 'FT-2026-0042'])
            ->assertRedirect(route('gestione.ordini.show', $ordine));

        $ordine->refresh();
        $this->assertSame('FT-2026-0042', $ordine->fattura_numero);
        $this->assertNotNull($ordine->fattura_emessa_at);
        $this->assertTrue($ordine->fatturata());
        // Emettere la fattura non tocca da solo lo stato del saldo.
        $this->assertSame(StatoPagamento::InAttesa, $ordine->stato_pagamento);
    }

    public function test_emettere_fattura_e_bloccato_su_un_ordine_non_a_fattura(): void
    {
        $privato = User::factory()->create();
        $carrello = Carrello::firstOrCreate(['user_id' => $privato->id]);
        $carrello->righe()->create(['product_id' => $this->prodotto()->id, 'quantita' => 1]);
        $this->actingAs($privato)->post('/ordine/conferma', array_merge(self::CONSEGNA, [
            'metodo_pagamento' => 'contrassegno',
        ]));
        $ordine = Ordine::where('user_id', $privato->id)->latest()->firstOrFail();

        $this->actingAs($this->staff())
            ->post("/gestione/ordini/{$ordine->numero}/fattura", ['fattura_numero' => 'FT-2026-0043'])
            ->assertNotFound();
    }

    public function test_lo_staff_segna_una_fattura_saldata(): void
    {
        $referente = $this->referenteAgenzia();
        $ordine = $this->ordineAFattura($referente);
        $ordine->emettiFattura('FT-2026-0050');

        $this->actingAs($this->staff())
            ->post("/gestione/ordini/{$ordine->numero}/fattura/pagata", ['riferimento_pagamento' => 'Bonifico del 10/08'])
            ->assertRedirect(route('gestione.ordini.show', $ordine));

        $ordine->refresh();
        $this->assertSame(StatoPagamento::Pagato, $ordine->stato_pagamento);
        $this->assertSame('Bonifico del 10/08', $ordine->riferimento_pagamento);
        $this->assertNotNull($ordine->pagato_at);
    }

    public function test_segnare_saldata_e_bloccato_se_non_ancora_fatturato(): void
    {
        $referente = $this->referenteAgenzia();
        $ordine = $this->ordineAFattura($referente);

        $this->actingAs($this->staff())
            ->post("/gestione/ordini/{$ordine->numero}/fattura/pagata", [])
            ->assertNotFound();
    }

    public function test_lagenzia_vede_i_propri_ordini_in_fatture(): void
    {
        $referente = $this->referenteAgenzia();
        $ordine = $this->ordineAFattura($referente);
        $ordine->emettiFattura('FT-2026-0060');

        $this->actingAs($referente)
            ->get('/account/fatture')
            ->assertOk()
            ->assertSee($ordine->numero)
            ->assertSee('FT-2026-0060');
    }

    public function test_unagenzia_non_ancora_approvata_non_accede_a_fatture(): void
    {
        $referente = $this->referenteAgenzia(approvata: false);

        $this->actingAs($referente)
            ->get('/account/fatture')
            ->assertRedirect(route('account'));
    }

    public function test_un_privato_non_accede_a_fatture(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/account/fatture')
            ->assertRedirect(route('account'));
    }

    public function test_i_movimenti_dello_staff_mostrano_i_totali_corretti(): void
    {
        $referente = $this->referenteAgenzia();

        $daFatturare = $this->ordineAFattura($referente);

        $daSaldare = $this->ordineAFattura($referente);
        $daSaldare->emettiFattura('FT-2026-0070');

        $pagata = $this->ordineAFattura($referente);
        $pagata->emettiFattura('FT-2026-0071');
        $pagata->registraPagamento('Bonifico del 01/08');

        $risposta = $this->actingAs($this->staff())
            ->get('/gestione/agenzie/'.$referente->agenzia->id.'/movimenti')
            ->assertOk();

        $risposta->assertSee($daFatturare->numero);
        $risposta->assertSee($daSaldare->numero);
        $risposta->assertSee($pagata->numero);

        $risposta->assertViewHas('daFatturare', $daFatturare->totale);
        $risposta->assertViewHas('daSaldare', $daSaldare->totale);
        $risposta->assertViewHas('incassatoCarta', 0);
    }

    public function test_i_movimenti_sono_riservati_allo_staff(): void
    {
        $referente = $this->referenteAgenzia();

        $this->actingAs($referente)
            ->get('/gestione/agenzie/'.$referente->agenzia->id.'/movimenti')
            ->assertNotFound();
    }
}
