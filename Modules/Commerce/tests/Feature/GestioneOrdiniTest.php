<?php

namespace Modules\Commerce\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Commerce\Enums\StatoOrdine;
use Modules\Commerce\Models\Carrello;
use Modules\Commerce\Models\Ordine;
use Modules\Commerce\Tests\Concerns\CreaSoggetti;
use Tests\TestCase;

class GestioneOrdiniTest extends TestCase
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
        $categoria = Category::firstOrCreate(['slug' => 'test-ordini'], ['name' => 'Test ordini']);

        return Product::create(array_merge([
            'category_id' => $categoria->id,
            'sku' => 'ORD-'.uniqid(),
            'slug' => 'ord-'.uniqid(),
            'name' => 'Articolo di prova',
            'price' => 4900,
            'is_active' => true,
        ], $attributi));
    }

    /** Un ordine vero, passando dal checkout come farebbe un cliente. */
    private function creaOrdine(User $acquirente, array $righe, string $metodo = 'contrassegno'): Ordine
    {
        $carrello = Carrello::firstOrCreate(['user_id' => $acquirente->id]);
        foreach ($righe as [$prodotto, $quantita]) {
            $carrello->righe()->create(['product_id' => $prodotto->id, 'quantita' => $quantita]);
        }

        $this->actingAs($acquirente)->post('/ordine/conferma', array_merge(self::CONSEGNA, [
            'metodo_pagamento' => $metodo,
        ]));

        return Ordine::where('user_id', $acquirente->id)->latest()->firstOrFail();
    }

    public function test_chi_non_e_staff_non_vede_nemmeno_che_gestione_esiste(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/gestione/ordini')
            ->assertNotFound();
    }

    public function test_lo_staff_vede_ordini_b2b_e_b2c_insieme(): void
    {
        $privato = User::factory()->create();
        $ordinePrivato = $this->creaOrdine($privato, [[$this->prodotto(), 1]]);

        $referente = $this->referenteAgenzia();
        $ordineAgenzia = $this->creaOrdine($referente, [[$this->prodotto(), 25]], 'fattura');

        $this->actingAs($this->staff())
            ->get('/gestione/ordini')
            ->assertOk()
            ->assertSee($ordinePrivato->numero)
            ->assertSee($ordineAgenzia->numero);
    }

    public function test_il_filtro_provenienza_isola_i_due_gruppi(): void
    {
        $privato = User::factory()->create();
        $ordinePrivato = $this->creaOrdine($privato, [[$this->prodotto(), 1]]);

        $referente = $this->referenteAgenzia();
        $ordineAgenzia = $this->creaOrdine($referente, [[$this->prodotto(), 25]], 'fattura');

        $this->actingAs($this->staff())
            ->get('/gestione/ordini?provenienza=b2c')
            ->assertSee($ordinePrivato->numero)
            ->assertDontSee($ordineAgenzia->numero);

        $this->actingAs($this->staff())
            ->get('/gestione/ordini?provenienza=b2b')
            ->assertSee($ordineAgenzia->numero)
            ->assertDontSee($ordinePrivato->numero);
    }

    public function test_spedire_richiede_corriere_e_tracking_e_sposta_lo_stato(): void
    {
        $ordine = $this->creaOrdine(User::factory()->create(), [[$this->prodotto(['price' => 9000]), 1]]);
        $this->assertSame(StatoOrdine::InProduzione, $ordine->stato);

        $this->actingAs($this->staff())
            ->post(route('gestione.ordini.spedisci', $ordine), [])
            ->assertSessionHasErrors(['corriere', 'tracking_numero']);

        $this->actingAs($this->staff())
            ->post(route('gestione.ordini.spedisci', $ordine), [
                'corriere' => 'BRT',
                'tracking_numero' => 'ABC123456IT',
            ])
            ->assertRedirect(route('gestione.ordini.show', $ordine));

        $ordine->refresh();
        $this->assertSame(StatoOrdine::Spedito, $ordine->stato);
        $this->assertSame('BRT', $ordine->corriere);
        $this->assertSame('ABC123456IT', $ordine->tracking_numero);
    }

    public function test_segnare_consegnato_sposta_lo_stato(): void
    {
        $ordine = $this->creaOrdine(User::factory()->create(), [[$this->prodotto(['price' => 9000]), 1]]);
        $ordine->spedisci('BRT', 'ABC123456IT');

        $this->actingAs($this->staff())
            ->post(route('gestione.ordini.consegnato', $ordine))
            ->assertRedirect(route('gestione.ordini.show', $ordine));

        $this->assertSame(StatoOrdine::Consegnato, $ordine->fresh()->stato);
    }

    public function test_un_ordine_senza_foto_non_mostra_il_link_alla_lavorazione(): void
    {
        $ordine = $this->creaOrdine(User::factory()->create(), [[$this->prodotto(['price' => 9000]), 1]]);
        $this->assertFalse($ordine->richiede_lavorazione);

        $this->actingAs($this->staff())
            ->get(route('gestione.ordini.show', $ordine))
            ->assertOk()
            ->assertDontSee('Apri la lavorazione');
    }

    public function test_lo_staff_apre_la_lavorazione_di_un_ordine_che_non_ha_comprato(): void
    {
        $conFoto = $this->prodotto(['is_photo_printable' => true, 'price' => 4900]);
        $ordine = $this->creaOrdine(User::factory()->create(), [[$conFoto, 1]]);
        $this->assertTrue($ordine->richiede_lavorazione);

        $this->actingAs($this->staff())
            ->get(route('lavorazione', $ordine))
            ->assertOk();
    }
}
