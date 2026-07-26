<?php

namespace Modules\PhotoPrint\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Commerce\Enums\RuoloUtente;
use Modules\Commerce\Models\Agenzia;
use Modules\Commerce\Models\Carrello;
use Modules\Commerce\Models\Ordine;
use Modules\Memorial\Models\Defunto;
use Tests\TestCase;

/**
 * L'archivio delle pratiche: sostituisce l'atterraggio automatico sulla
 * pratica di esempio quando si entra negli editor senza un ordine in
 * sessione. Copre anche l'allentamento dell'ownership della lavorazione: un
 * ordine B2B è dell'agenzia, non del singolo login che l'ha comprato.
 */
class PraticheArchivioTest extends TestCase
{
    use RefreshDatabase;

    private function staff(): User
    {
        $user = User::factory()->create();
        $user->ruolo = RuoloUtente::Staff;
        $user->save();

        return $user;
    }

    private function referenteAgenzia(string $ragioneSociale, string $partitaIva): User
    {
        $agenzia = Agenzia::create([
            'ragione_sociale' => $ragioneSociale,
            'partita_iva' => $partitaIva,
            'indirizzo' => 'Via Roma 12', 'cap' => '20121',
            'citta' => 'Milano', 'provincia' => 'MI', 'telefono' => '0212345678',
        ]);
        $agenzia->approva($this->staff());

        $referente = User::factory()->create();
        $referente->ruolo = RuoloUtente::Agenzia;
        $referente->agenzia()->associate($agenzia);
        $referente->save();

        return $referente->fresh();
    }

    private function ordinePer(User $utente): Ordine
    {
        $categoria = Category::firstOrCreate(
            ['slug' => 'articoli-trigesimali'],
            ['name' => 'Articoli trigesimali'],
        );
        $prodotto = Product::create([
            'category_id' => $categoria->id,
            'sku' => 'TRG-TEST-'.uniqid(),
            'slug' => 'trg-test-'.uniqid(),
            'name' => 'Cofanetto di prova',
            'price' => 4900,
            'is_photo_printable' => true,
            'is_active' => true,
        ]);

        // 20 pezzi: il minimo d'ordine B2B di default (config('commerce.ordine_minimo_pezzi')).
        // Innocuo per un privato, che quella soglia non ce l'ha.
        $carrello = Carrello::create(['user_id' => $utente->id]);
        $carrello->righe()->create(['product_id' => $prodotto->id, 'quantita' => 20]);

        $this->actingAs($utente)->post('/ordine/conferma', [
            'nome' => 'Giulia Ferrari', 'telefono' => '3391234567',
            'indirizzo' => 'Via Manzoni 4', 'cap' => '20121',
            'citta' => 'Milano', 'provincia' => 'MI',
            'metodo_pagamento' => 'contrassegno',
        ]);

        return Ordine::latest('id')->firstOrFail();
    }

    public function test_senza_ordine_in_sessione_si_va_all_archivio(): void
    {
        $staff = $this->staff();

        $this->actingAs($staff)->get('/studio/ricordino')->assertRedirect(route('pratiche.index'));
        $this->actingAs($staff)->get('/studio/foto')->assertRedirect(route('pratiche.index'));
    }

    public function test_con_demo_si_apre_comunque_la_pratica_di_esempio(): void
    {
        Defunto::create(['nome' => 'Maria', 'cognome' => 'Verdi']);
        $staff = $this->staff();

        $this->actingAs($staff)->get('/studio/foto?demo=1')->assertOk();
        $this->actingAs($staff)->get('/studio/ricordino?demo=1')->assertOk();
    }

    /**
     * `users.agenzia_id` è UNIQUE: un'agenzia ha un login alla volta, non due
     * referenti insieme. Lo scenario reale che l'ownership per agenzia_id
     * copre è il turnover: chi ha comprato lascia, un nuovo login prende il
     * suo posto sulla stessa agenzia e deve continuare a vedere le sue pratiche.
     */
    public function test_l_archivio_resta_dell_agenzia_anche_se_cambia_il_referente(): void
    {
        $agenzia = Agenzia::create([
            'ragione_sociale' => 'Onoranze Funebri Bianchi S.r.l.', 'partita_iva' => '00743110157',
            'indirizzo' => 'Via Roma 12', 'cap' => '20121', 'citta' => 'Milano', 'provincia' => 'MI', 'telefono' => '0212345678',
        ]);
        $agenzia->approva($this->staff());

        $vecchioReferente = User::factory()->create(['ruolo' => RuoloUtente::Agenzia]);
        $vecchioReferente->agenzia()->associate($agenzia);
        $vecchioReferente->save();

        $ordine = $this->ordinePer($vecchioReferente->fresh());
        $this->assertSame($agenzia->id, $ordine->agenzia_id, 'checkout con agenzia approvata deve legare l\'ordine all\'agenzia');

        // il vecchio referente lascia l'agenzia, un nuovo login prende il suo posto
        $vecchioReferente->agenzia()->dissociate();
        $vecchioReferente->save();

        $nuovoReferente = User::factory()->create(['ruolo' => RuoloUtente::Agenzia]);
        $nuovoReferente->agenzia()->associate($agenzia);
        $nuovoReferente->save();
        $nuovoReferente = $nuovoReferente->fresh();

        $this->actingAs($nuovoReferente)->get('/studio/pratiche')->assertOk()->assertSee($ordine->numero);
    }

    public function test_l_agenzia_non_vede_le_pratiche_di_un_altra_agenzia(): void
    {
        $mia = $this->referenteAgenzia('Onoranze Funebri Bianchi S.r.l.', '00743110157');
        $altra = $this->referenteAgenzia('Casa Funeraria Aurora S.n.c.', '12485671007');

        $ordineAltrui = $this->ordinePer($altra);

        $this->actingAs($mia)->get('/studio/pratiche')->assertOk()->assertDontSee($ordineAltrui->numero);
    }

    public function test_lo_staff_vede_tutte_le_pratiche(): void
    {
        $agenzia = $this->referenteAgenzia('Onoranze Funebri Bianchi S.r.l.', '00743110157');
        $ordine = $this->ordinePer($agenzia);

        $this->actingAs($this->staff())->get('/studio/pratiche')->assertOk()->assertSee($ordine->numero);
    }

    public function test_un_nuovo_referente_della_stessa_agenzia_apre_la_lavorazione_di_un_ordine_del_precedente(): void
    {
        $agenzia = Agenzia::create([
            'ragione_sociale' => 'Onoranze Funebri Bianchi S.r.l.', 'partita_iva' => '00743110157',
            'indirizzo' => 'Via Roma 12', 'cap' => '20121', 'citta' => 'Milano', 'provincia' => 'MI', 'telefono' => '0212345678',
        ]);
        $agenzia->approva($this->staff());

        $vecchioReferente = User::factory()->create(['ruolo' => RuoloUtente::Agenzia]);
        $vecchioReferente->agenzia()->associate($agenzia);
        $vecchioReferente->save();

        $ordine = $this->ordinePer($vecchioReferente->fresh());

        $vecchioReferente->agenzia()->dissociate();
        $vecchioReferente->save();

        $nuovoReferente = User::factory()->create(['ruolo' => RuoloUtente::Agenzia]);
        $nuovoReferente->agenzia()->associate($agenzia);
        $nuovoReferente->save();
        $nuovoReferente = $nuovoReferente->fresh();

        $this->actingAs($nuovoReferente)
            ->get(route('lavorazione', $ordine))
            ->assertOk();
    }

    public function test_lo_staff_apre_la_lavorazione_di_qualunque_ordine(): void
    {
        $agenzia = $this->referenteAgenzia('Onoranze Funebri Bianchi S.r.l.', '00743110157');
        $ordine = $this->ordinePer($agenzia);

        $this->actingAs($this->staff())->get(route('lavorazione', $ordine))->assertOk();
    }

    public function test_un_agenzia_diversa_non_apre_la_lavorazione_di_un_ordine_non_suo(): void
    {
        $mia = $this->referenteAgenzia('Onoranze Funebri Bianchi S.r.l.', '00743110157');
        $altra = $this->referenteAgenzia('Casa Funeraria Aurora S.n.c.', '12485671007');

        $ordineAltrui = $this->ordinePer($altra);

        $this->actingAs($mia)->get(route('lavorazione', $ordineAltrui))->assertNotFound();
    }

    public function test_l_archivio_di_un_privato_mostra_solo_i_suoi_ordini(): void
    {
        $privato = User::factory()->create();
        $altroPrivato = User::factory()->create();

        $mioOrdine = $this->ordinePer($privato);
        $this->ordinePer($altroPrivato);

        // Entra nella propria lavorazione: è così che un privato passa AccessoStudio.
        $this->actingAs($privato)->get(route('lavorazione', $mioOrdine))->assertOk();

        $risposta = $this->actingAs($privato)->get('/studio/pratiche')->assertOk();
        $risposta->assertSee($mioOrdine->numero);
    }
}
