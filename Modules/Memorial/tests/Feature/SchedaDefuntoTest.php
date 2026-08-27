<?php

namespace Modules\Memorial\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Commerce\Models\Carrello;
use Modules\Commerce\Models\Ordine;
use Modules\Commerce\Tests\Concerns\CreaSoggetti;
use Modules\Memorial\Models\Defunto;
use Modules\Memorial\Models\Manifesto;
use Modules\PhotoPrint\Servizi\LavorazioneCorrente;
use Tests\TestCase;

/**
 * La Scheda Defunto: hub del percorso canalizzato Foto → Manifesto →
 * Necrologio. Dopo i dati del defunto un'agenzia arriva qui (non più sulla
 * lavorazione, riservata al B2C); il necrologio del funerale non si crea
 * senza un manifesto già composto.
 */
class SchedaDefuntoTest extends TestCase
{
    use CreaSoggetti, RefreshDatabase;

    private function ordineInLavorazione(): Ordine
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

        $utente = User::factory()->create();
        $carrello = Carrello::create(['user_id' => $utente->id]);
        $carrello->righe()->create(['product_id' => $prodotto->id, 'quantita' => 1]);

        $this->actingAs($utente)->post('/ordine/conferma', [
            'nome' => 'Giulia Ferrari', 'telefono' => '3391234567',
            'indirizzo' => 'Via Manzoni 4', 'cap' => '20121',
            'citta' => 'Milano', 'provincia' => 'MI',
            'metodo_pagamento' => 'contrassegno',
        ]);

        return Ordine::latest('id')->firstOrFail();
    }

    private function conFotoPrincipale(Ordine $ordine): void
    {
        DB::table('foto_pratica')->insert([
            'ordine_id' => $ordine->id,
            'path' => 'photoprint/pratica/principale.jpg',
            'tipo' => 'originale',
            'is_principale' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function salvaDefunto(Ordine $ordine)
    {
        $this->actingAs($ordine->user)
            ->get("/account/ordini/{$ordine->numero}/lavorazione")
            ->assertOk();

        return $this->actingAs($ordine->user)->post("/account/ordini/{$ordine->numero}/lavorazione/defunto", [
            'nome' => 'Luigia', 'cognome' => 'Rossetti',
            'gdpr_parentela' => 'Figlia', 'gdpr_consenso' => '1',
        ]);
    }

    public function test_dopo_i_dati_del_defunto_l_agenzia_arriva_alla_scheda_defunto(): void
    {
        $referente = $this->referenteAgenzia();
        $ordine = $this->ordineInLavorazione();
        $ordine->forceFill(['agenzia_id' => $referente->agenzia_id])->save();

        $risposta = $this->salvaDefunto($ordine);

        $defunto = Defunto::firstOrFail();
        $risposta->assertRedirect(route('defunti.show', $defunto));
    }

    public function test_un_ordine_privato_resta_sulla_lavorazione(): void
    {
        $ordine = $this->ordineInLavorazione();

        $this->salvaDefunto($ordine)->assertRedirect(route('lavorazione', $ordine));
    }

    public function test_il_necrologio_del_funerale_richiede_un_manifesto(): void
    {
        $referente = $this->referenteAgenzia();
        $ordine = $this->ordineInLavorazione();
        $ordine->forceFill(['agenzia_id' => $referente->agenzia_id])->save();
        $this->salvaDefunto($ordine);
        $defunto = Defunto::firstOrFail();

        $this->actingAs($referente)->post(route('necrologi.salva'), [
            'defunto_id' => $defunto->id,
            'occasione' => 'funerale',
        ])->assertRedirect(route('defunti.show', $defunto));

        $this->assertDatabaseCount('necrologi', 0);
    }

    public function test_il_necrologio_del_funerale_si_crea_con_un_manifesto_esistente(): void
    {
        $referente = $this->referenteAgenzia();
        $ordine = $this->ordineInLavorazione();
        $ordine->forceFill(['agenzia_id' => $referente->agenzia_id])->save();
        $this->salvaDefunto($ordine);
        $defunto = Defunto::firstOrFail();
        Manifesto::create(['defunto_id' => $defunto->id, 'etichetta' => 'Manifesto funerale', 'formato' => 'a3l', 'principale' => true]);

        $this->actingAs($referente)->post(route('necrologi.salva'), [
            'defunto_id' => $defunto->id,
            'occasione' => 'funerale',
        ])->assertRedirect();

        $this->assertDatabaseCount('necrologi', 1);
    }

    public function test_il_necrologio_di_trigesimo_non_richiede_manifesto(): void
    {
        $referente = $this->referenteAgenzia();
        $ordine = $this->ordineInLavorazione();
        $ordine->forceFill(['agenzia_id' => $referente->agenzia_id])->save();
        $this->salvaDefunto($ordine);
        $defunto = Defunto::firstOrFail();

        $this->actingAs($referente)->post(route('necrologi.salva'), [
            'defunto_id' => $defunto->id,
            'occasione' => 'trigesimo',
        ])->assertRedirect();

        $this->assertDatabaseCount('necrologi', 1);
    }

    public function test_la_scheda_defunto_mostra_il_percorso(): void
    {
        $referente = $this->referenteAgenzia();
        $ordine = $this->ordineInLavorazione();
        $ordine->forceFill(['agenzia_id' => $referente->agenzia_id])->save();
        $this->salvaDefunto($ordine);
        $this->conFotoPrincipale($ordine);
        $defunto = Defunto::firstOrFail();

        $this->actingAs($referente)
            ->get(route('defunti.show', $defunto))
            ->assertOk()
            ->assertSee('Luigia Rossetti')
            ->assertSee('Foto')
            ->assertSee('Manifesto')
            ->assertSee('Necrologio')
            ->assertSee('Ricordino');
    }

    public function test_un_estraneo_non_accede_alla_scheda_defunto_altrui(): void
    {
        $referente = $this->referenteAgenzia();
        $ordine = $this->ordineInLavorazione();
        $ordine->forceFill(['agenzia_id' => $referente->agenzia_id])->save();
        $this->salvaDefunto($ordine);
        $defunto = Defunto::firstOrFail();

        $altro = $this->referenteAgenzia(attributi: [
            'ragione_sociale' => 'Casa Funeraria Aurora S.n.c.',
            'partita_iva' => '12485671007',
        ]);

        $this->actingAs($altro)->get(route('defunti.show', $defunto))->assertNotFound();
    }

    public function test_lo_staff_accede_alla_scheda_defunto_di_un_ordine_non_suo(): void
    {
        $referente = $this->referenteAgenzia();
        $ordine = $this->ordineInLavorazione();
        $ordine->forceFill(['agenzia_id' => $referente->agenzia_id])->save();
        $this->salvaDefunto($ordine);
        $defunto = Defunto::firstOrFail();

        $this->actingAs($this->staff())->get(route('defunti.show', $defunto))->assertOk();
    }

    /**
     * A differenza della lavorazione (che mette l'ordine in sessione da sola
     * arrivandoci), la Scheda del Defunto no: un'agenzia che segue più
     * pratiche e ci torna con la sessione lavorazione puntata su un ALTRO
     * ordine (o vuota) trovava il bottone "Apri il Designer" attivo ma il
     * click rimbalzava sull'archivio pratiche — l'overlay mostrava l'intero
     * sito annidato dentro sé stesso invece del designer. I link della
     * Scheda ora passano da una rotta che rimette l'ordine giusto in
     * sessione prima di entrare (vedi PhotoPrintController::apriRicordinoDefunto).
     */
    public function test_apri_il_designer_dalla_scheda_defunto_funziona_anche_con_la_sessione_su_un_altro_ordine(): void
    {
        $referente = $this->referenteAgenzia();
        $ordine = $this->ordineInLavorazione();
        $ordine->forceFill(['agenzia_id' => $referente->agenzia_id])->save();
        $this->salvaDefunto($ordine);
        $this->conFotoPrincipale($ordine);
        $defunto = Defunto::firstOrFail();

        // La sessione lavorazione non è mai stata impostata per QUESTO
        // ordine in questo "login" (o punta altrove) — il caso normale di
        // chi arriva alla Scheda da "I miei defunti", non dalla lavorazione.
        app(LavorazioneCorrente::class)->dimentica();

        $this->actingAs($referente)
            ->get(route('studio.ricordino.defunto', $defunto))
            ->assertRedirect(route('studio.ricordino'));

        $this->actingAs($referente)
            ->get(route('studio.ricordino'))
            ->assertOk()
            ->assertViewHas('praticaId', $defunto->id);
    }

    public function test_apri_il_designer_dalla_scheda_defunto_rifiuta_un_estraneo(): void
    {
        $referente = $this->referenteAgenzia();
        $ordine = $this->ordineInLavorazione();
        $ordine->forceFill(['agenzia_id' => $referente->agenzia_id])->save();
        $this->salvaDefunto($ordine);
        $this->conFotoPrincipale($ordine);
        $defunto = Defunto::firstOrFail();

        $altro = $this->referenteAgenzia(attributi: [
            'ragione_sociale' => 'Casa Funeraria Aurora S.n.c.',
            'partita_iva' => '12485671007',
        ]);

        $this->actingAs($altro)->get(route('studio.ricordino.defunto', $defunto))->assertNotFound();
    }
}
