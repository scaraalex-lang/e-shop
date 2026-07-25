<?php

namespace Modules\PhotoPrint\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Commerce\Enums\StatoOrdine;
use Modules\Commerce\Models\Carrello;
use Modules\Commerce\Models\Ordine;
use Modules\Memorial\Models\Defunto;
use Modules\Memorial\Models\RevisioneRicordino;
use Modules\Memorial\Models\Ricordino;
use Modules\PhotoPrint\Mail\BozzaDaApprovare;
use Modules\Commerce\Tests\Concerns\CreaSoggetti;
use Tests\TestCase;

/**
 * La bozza mandata alla famiglia: il flusso che vende il B2B.
 *
 * Chi riceve il link non ha un account e non deve averlo: il link E' la
 * credenziale, e vale finché l'ordine è aperto.
 */
class BozzaCondivisibileTest extends TestCase
{
    use CreaSoggetti, RefreshDatabase;

    private function ordineConBozza(): array
    {
        $categoria = Category::firstOrCreate(['slug' => 'articoli-trigesimali'], ['name' => 'Articoli trigesimali']);
        $prodotto = Product::create([
            'category_id' => $categoria->id,
            'sku' => 'TRG-'.uniqid(), 'slug' => 'trg-'.uniqid(),
            'name' => 'Cofanetto di prova', 'price' => 4900,
            'is_photo_printable' => true, 'is_active' => true,
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

        $ordine = Ordine::latest('id')->firstOrFail();

        $defunto = Defunto::create([
            'nome' => 'Luigia', 'cognome' => 'Rossetti', 'ordine_id' => $ordine->id,
        ]);
        $ordine->forceFill(['defunto_id' => $defunto->id])->save();

        $ricordino = $defunto->ricordini()->create([
            'formato' => '7x10', 'stato' => Ricordino::BOZZA,
            'anteprima_fronte' => 'ricordini/anteprime/prova.jpg',
        ]);

        // apre la lavorazione: è cosi' che gli editor sanno su cosa si lavora
        $this->actingAs($utente)->get("/account/ordini/{$ordine->numero}/lavorazione")->assertOk();

        return [$utente, $ordine, $ricordino];
    }

    public function test_il_cliente_manda_la_bozza_alla_famiglia(): void
    {
        Mail::fake();
        [$utente, , $ricordino] = $this->ordineConBozza();

        $risposta = $this->actingAs($utente)
            ->postJson("/admin/api/ricordino/{$ricordino->id}/invia-approvazione", [
                'email' => 'famiglia@esempio.it',
            ]);

        $risposta->assertOk()->assertJson(['success' => true, 'email' => 'famiglia@esempio.it']);
        $risposta->assertJsonStructure(['approva_url', 'whatsapp_url', 'email_inviata']);

        $revisione = RevisioneRicordino::firstOrFail();
        $this->assertSame('famiglia@esempio.it', $revisione->inviata_a);
        $this->assertTrue($revisione->inAttesa());
        $this->assertSame(Ricordino::IN_APPROVAZIONE, $ricordino->fresh()->stato);

        Mail::assertSent(BozzaDaApprovare::class, fn ($m) => $m->hasTo('famiglia@esempio.it'));
    }

    /**
     * B2B: l'email parte dal nostro SMTP, ma chi risponde scrive all'agenzia.
     *
     * Il mittente resta nostro di proposito: mettere l'agenzia nel From
     * farebbe fallire SPF sul suo dominio. La famiglia però sta parlando con
     * l'agenzia, non con noi.
     */
    public function test_la_risposta_della_famiglia_arriva_all_agenzia(): void
    {
        Mail::fake();
        [$utente, $ordine, $ricordino] = $this->ordineConBozza();

        $referente = $this->referenteAgenzia();
        $ordine->forceFill(['agenzia_id' => $referente->agenzia_id])->save();

        $this->actingAs($utente)
            ->postJson("/admin/api/ricordino/{$ricordino->id}/invia-approvazione", [
                'email' => 'famiglia@esempio.it',
            ])->assertOk();

        Mail::assertSent(BozzaDaApprovare::class, function (BozzaDaApprovare $mail) use ($referente) {
            $mail->assertHasReplyTo($referente->email);

            // Nessun mittente proprio: resta quello globale, cioè il nostro
            // SMTP. Se un giorno qualcuno ci mette l'agenzia, questo salta.
            $this->assertEmpty($mail->from, 'il mittente deve restare il nostro');

            return $mail->hasTo('famiglia@esempio.it');
        });
    }

    /** B2C: nessuna agenzia dietro, la risposta torna a noi. */
    public function test_senza_agenzia_la_risposta_torna_a_noi(): void
    {
        Mail::fake();
        [$utente, , $ricordino] = $this->ordineConBozza();

        $this->actingAs($utente)
            ->postJson("/admin/api/ricordino/{$ricordino->id}/invia-approvazione", [
                'email' => 'famiglia@esempio.it',
            ])->assertOk();

        Mail::assertSent(BozzaDaApprovare::class, function (BozzaDaApprovare $mail) {
            $this->assertEmpty($mail->replyTo);

            return true;
        });
    }

    public function test_senza_email_non_si_manda_niente(): void
    {
        Mail::fake();
        [$utente, , $ricordino] = $this->ordineConBozza();

        $this->actingAs($utente)
            ->postJson("/admin/api/ricordino/{$ricordino->id}/invia-approvazione", ['email' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->assertDatabaseCount('revisioni_ricordino', 0);
        Mail::assertNothingSent();
    }

    public function test_la_famiglia_apre_il_link_senza_account_e_approva(): void
    {
        Mail::fake();
        [$utente, , $ricordino] = $this->ordineConBozza();
        $this->actingAs($utente)->postJson("/admin/api/ricordino/{$ricordino->id}/invia-approvazione", [
            'email' => 'famiglia@esempio.it',
        ]);
        $revisione = RevisioneRicordino::firstOrFail();

        // da ospite: nessun account
        $this->post('/esci');
        $this->get("/bozza/{$revisione->token}")
            ->assertOk()
            ->assertSee('Luigia Rossetti');

        $this->assertNotNull($revisione->fresh()->vista_at, 'si registra quando la famiglia guarda');

        $this->post("/bozza/{$revisione->token}/approva")
            ->assertRedirect(route('bozza', $revisione->token));

        $this->assertSame(RevisioneRicordino::APPROVATA, $revisione->fresh()->esito);
        $this->assertSame(Ricordino::APPROVATO, $ricordino->fresh()->stato);
    }

    public function test_la_famiglia_puo_chiedere_una_correzione_e_il_ricordino_torna_bozza(): void
    {
        Mail::fake();
        [$utente, , $ricordino] = $this->ordineConBozza();
        $this->actingAs($utente)->postJson("/admin/api/ricordino/{$ricordino->id}/invia-approvazione", [
            'email' => 'famiglia@esempio.it',
        ]);
        $revisione = RevisioneRicordino::firstOrFail();

        $this->post("/bozza/{$revisione->token}/modifiche", [
            'nota' => 'La data di nascita è 10 ottobre, non 10 novembre.',
        ])->assertRedirect(route('bozza', $revisione->token));

        $revisione->refresh();
        $this->assertSame(RevisioneRicordino::MODIFICHE, $revisione->esito);
        $this->assertStringContainsString('10 ottobre', $revisione->nota);

        // torna lavorabile: c'è da rimetterci mano
        $this->assertSame(Ricordino::BOZZA, $ricordino->fresh()->stato);
    }

    public function test_una_correzione_senza_spiegazione_non_si_accetta(): void
    {
        Mail::fake();
        [$utente, , $ricordino] = $this->ordineConBozza();
        $this->actingAs($utente)->postJson("/admin/api/ricordino/{$ricordino->id}/invia-approvazione", [
            'email' => 'famiglia@esempio.it',
        ]);
        $revisione = RevisioneRicordino::firstOrFail();

        $this->post("/bozza/{$revisione->token}/modifiche", ['nota' => ''])
            ->assertSessionHasErrors('nota');

        $this->assertTrue($revisione->fresh()->inAttesa());
    }

    public function test_ogni_invio_e_un_giro_a_se_e_lo_storico_resta(): void
    {
        Mail::fake();
        [$utente, , $ricordino] = $this->ordineConBozza();

        // primo giro: la famiglia chiede una correzione
        $this->actingAs($utente)->postJson("/admin/api/ricordino/{$ricordino->id}/invia-approvazione", ['email' => 'famiglia@esempio.it']);
        $primo = RevisioneRicordino::latest('id')->firstOrFail();
        $this->post("/bozza/{$primo->token}/modifiche", ['nota' => 'Il cognome è Rossetti con due t.']);

        // secondo giro: approvano
        $this->actingAs($utente)->postJson("/admin/api/ricordino/{$ricordino->id}/invia-approvazione", ['email' => 'famiglia@esempio.it']);
        $secondo = RevisioneRicordino::latest('id')->firstOrFail();
        $this->post("/bozza/{$secondo->token}/approva");

        $this->assertDatabaseCount('revisioni_ricordino', 2);
        $this->assertSame(RevisioneRicordino::MODIFICHE, $primo->fresh()->esito, 'il primo giro resta scritto');
        $this->assertSame(Ricordino::APPROVATO, $ricordino->fresh()->stato);

        // sulla pagina del secondo giro si legge cosa era stato chiesto prima
        $this->get("/bozza/{$secondo->token}")->assertOk()->assertSee('due t', false);
    }

    public function test_una_risposta_gia_data_non_si_cambia(): void
    {
        Mail::fake();
        [$utente, , $ricordino] = $this->ordineConBozza();
        $this->actingAs($utente)->postJson("/admin/api/ricordino/{$ricordino->id}/invia-approvazione", ['email' => 'famiglia@esempio.it']);
        $revisione = RevisioneRicordino::firstOrFail();

        $this->post("/bozza/{$revisione->token}/approva");
        $this->post("/bozza/{$revisione->token}/modifiche", ['nota' => 'Ci ho ripensato.']);

        $this->assertSame(RevisioneRicordino::APPROVATA, $revisione->fresh()->esito);
    }

    public function test_il_link_muore_quando_l_ordine_si_chiude(): void
    {
        Mail::fake();
        [$utente, $ordine, $ricordino] = $this->ordineConBozza();
        $this->actingAs($utente)->postJson("/admin/api/ricordino/{$ricordino->id}/invia-approvazione", ['email' => 'famiglia@esempio.it']);
        $revisione = RevisioneRicordino::firstOrFail();

        $this->get("/bozza/{$revisione->token}")->assertOk();

        $ordine->passaA(StatoOrdine::Consegnato);

        $this->get("/bozza/{$revisione->token}")->assertNotFound();
    }

    public function test_un_token_inventato_non_apre_niente(): void
    {
        $this->get('/bozza/'.str_repeat('a', 64))->assertNotFound();
    }

    public function test_non_si_manda_la_bozza_di_una_pratica_altrui(): void
    {
        Mail::fake();
        [, , $ricordino] = $this->ordineConBozza();

        // un altro cliente, con la SUA lavorazione aperta
        [$altro] = $this->ordineConBozza();

        $this->actingAs($altro)
            ->postJson("/admin/api/ricordino/{$ricordino->id}/invia-approvazione", ['email' => 'ladro@esempio.it'])
            ->assertForbidden();

        Mail::assertNothingSent();
    }
}
