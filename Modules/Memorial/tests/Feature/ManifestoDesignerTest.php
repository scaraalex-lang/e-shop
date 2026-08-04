<?php

namespace Modules\Memorial\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Commerce\Database\Seeders\ServiziEditorSeeder;
use Modules\Commerce\Enums\MetodoPagamento;
use Modules\Commerce\Enums\RuoloUtente;
use Modules\Commerce\Models\Agenzia;
use Modules\Commerce\Models\Ordine;
use Modules\Commerce\Models\ServizioEditor;
use Modules\Memorial\Models\Defunto;
use Modules\Memorial\Models\Manifesto;
use Modules\Memorial\Models\ManifestoTemplate;
use Tests\TestCase;

/**
 * I manifesti: collegati al defunto (non al necrologio), un solo principale
 * alla volta, gate di creazione su foto pronta + servizio attivato — vedi
 * ManifestiController. Stesso schema di ownership del card designer.
 */
class ManifestoDesignerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        (new ServiziEditorSeeder)->run();
    }

    private function staff(): User
    {
        $user = User::factory()->create();
        $user->ruolo = RuoloUtente::Staff;
        $user->save();

        return $user;
    }

    private function agenziaConReferente(string $ragioneSociale = 'Onoranze Funebri Bianchi S.r.l.', string $partitaIva = '00743110157'): array
    {
        $staff = $this->staff();

        $agenzia = Agenzia::create([
            'ragione_sociale' => $ragioneSociale,
            'partita_iva' => $partitaIva,
            'indirizzo' => 'Via Roma 12', 'cap' => '20121',
            'citta' => 'Milano', 'provincia' => 'MI', 'telefono' => '0212345678',
        ]);
        $agenzia->approva($staff);

        $referente = User::factory()->create();
        $referente->ruolo = RuoloUtente::Agenzia;
        $referente->agenzia()->associate($agenzia);
        $referente->save();

        return [$referente->fresh(), $agenzia->fresh()];
    }

    /** Un defunto con un ordine dell'agenzia dietro: la base per i gate di ManifestiController. */
    private function defuntoConOrdine(Agenzia $agenzia, User $referente, bool $fotoPrincipale = true, array $serviziCodici = ['manifesti']): Defunto
    {
        $defunto = Defunto::create(['nome' => 'Luigia', 'cognome' => 'Rossetti']);

        $ordine = Ordine::create([
            'numero' => Ordine::prossimoNumero(),
            'user_id' => $referente->id,
            'agenzia_id' => $agenzia->id,
            'metodo_pagamento' => MetodoPagamento::Fattura->value,
            'totale_pieno' => 0, 'totale_merce' => 0, 'totale' => 0,
            'consegna_nome' => 'Prova', 'consegna_telefono' => '000',
            'consegna_indirizzo' => 'Via Prova 1', 'consegna_cap' => '20121',
            'consegna_citta' => 'Milano', 'consegna_provincia' => 'MI',
            'richiede_lavorazione' => true,
        ]);
        $ordine->forceFill(['defunto_id' => $defunto->id])->save();
        $defunto->forceFill(['ordine_id' => $ordine->id])->save();

        foreach ($serviziCodici as $codice) {
            $servizio = ServizioEditor::where('codice', $codice)->first();
            $ordine->servizi()->create(['servizio_editor_id' => $servizio->id, 'costo_crediti' => $servizio->costo_crediti]);
        }

        if ($fotoPrincipale) {
            DB::table('foto_pratica')->insert([
                'ordine_id' => $ordine->id, 'path' => 'photoprint/pratica/principale.jpg',
                'tipo' => 'originale', 'is_principale' => true, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        return $defunto->fresh();
    }

    private function manifesto(Defunto $defunto, bool $principale = true): Manifesto
    {
        return Manifesto::create([
            'defunto_id' => $defunto->id,
            'etichetta' => 'Manifesto funerale',
            'formato' => 'a3l',
            'principale' => $principale,
        ]);
    }

    private function canvasNonVuoto(): string
    {
        return json_encode(['objects' => [['type' => 'textbox', 'text' => 'Prova']]]);
    }

    private function pdfDataUrl(): string
    {
        return 'data:application/pdf;base64,'.base64_encode('%PDF-1.4 contenuto di prova');
    }

    /**
     * jsPDF .output('datauristring') produce sempre questo formato, con un
     * "filename=generated.pdf;" prima di "base64,": non un data URI "pulito".
     */
    private function pdfDataUrlComeJsPdf(): string
    {
        return 'data:application/pdf;filename=generated.pdf;base64,'.base64_encode('%PDF-1.4 contenuto di prova');
    }

    private function webDataUrl(string $contenuto = 'finto jpeg web'): string
    {
        return 'data:image/jpeg;base64,'.base64_encode($contenuto);
    }

    // ---- creazione (gate foto + servizio) ------------------------------------

    public function test_creare_un_manifesto_richiede_la_foto_principale(): void
    {
        [$referente, $agenzia] = $this->agenziaConReferente();
        $defunto = $this->defuntoConOrdine($agenzia, $referente, fotoPrincipale: false);

        $this->actingAs($referente)
            ->post(route('defunti.manifesti.store', $defunto), ['etichetta' => 'Manifesto funerale', 'formato' => 'a3l'])
            ->assertForbidden();

        $this->assertDatabaseCount('manifesti', 0);
    }

    public function test_creare_un_manifesto_richiede_il_servizio_attivato(): void
    {
        [$referente, $agenzia] = $this->agenziaConReferente();
        // Solo "ricordini" attivato: la collezione servizi non è vuota, quindi
        // "manifesti" non è incluso nella regola del kit fisico.
        $defunto = $this->defuntoConOrdine($agenzia, $referente, serviziCodici: ['ricordini']);

        $this->actingAs($referente)
            ->post(route('defunti.manifesti.store', $defunto), ['etichetta' => 'Manifesto funerale', 'formato' => 'a3l'])
            ->assertForbidden();

        $this->assertDatabaseCount('manifesti', 0);
    }

    public function test_creare_un_manifesto_con_foto_e_servizio_riesce(): void
    {
        [$referente, $agenzia] = $this->agenziaConReferente();
        $defunto = $this->defuntoConOrdine($agenzia, $referente);

        $this->actingAs($referente)
            ->post(route('defunti.manifesti.store', $defunto), ['etichetta' => 'Manifesto funerale', 'formato' => 'a3l'])
            ->assertRedirect();

        $manifesto = Manifesto::firstOrFail();
        $this->assertSame($defunto->id, $manifesto->defunto_id);
        $this->assertTrue($manifesto->principale, 'il primo manifesto del defunto è principale da solo');
    }

    public function test_il_secondo_manifesto_non_diventa_principale_da_solo(): void
    {
        [$referente, $agenzia] = $this->agenziaConReferente();
        $defunto = $this->defuntoConOrdine($agenzia, $referente);
        $this->manifesto($defunto);

        $this->actingAs($referente)->post(route('defunti.manifesti.store', $defunto), [
            'etichetta' => 'Partecipazioni', 'formato' => 'a4p',
        ]);

        $nuovo = Manifesto::where('etichetta', 'Partecipazioni')->firstOrFail();
        $this->assertFalse($nuovo->principale);
    }

    // ---- accesso -----------------------------------------------------------

    public function test_solo_l_agenzia_proprietaria_apre_il_designer_manifesti(): void
    {
        [$referente, $agenzia] = $this->agenziaConReferente();
        $defunto = $this->defuntoConOrdine($agenzia, $referente);
        $manifesto = $this->manifesto($defunto);

        [$altroReferente] = $this->agenziaConReferente('Casa Funeraria Aurora S.n.c.', '12485671007');

        $this->actingAs($referente)->get(route('manifesti.designer', $manifesto))->assertOk();
        $this->actingAs($altroReferente)->get(route('manifesti.designer', $manifesto))->assertNotFound();
    }

    public function test_un_privato_non_puo_disegnare_manifesti(): void
    {
        [$referente, $agenzia] = $this->agenziaConReferente();
        $defunto = $this->defuntoConOrdine($agenzia, $referente);
        $manifesto = $this->manifesto($defunto);

        $this->actingAs(User::factory()->create())
            ->get(route('manifesti.designer', $manifesto))
            ->assertNotFound();
    }

    public function test_lo_staff_apre_il_designer_di_un_manifesto_non_suo(): void
    {
        [$referente, $agenzia] = $this->agenziaConReferente();
        $defunto = $this->defuntoConOrdine($agenzia, $referente);
        $manifesto = $this->manifesto($defunto);

        $this->actingAs($this->staff())->get(route('manifesti.designer', $manifesto))->assertOk();
    }

    // ---- salvataggio (PDF di stampa + JPEG web automatico) ------------------

    public function test_salvare_il_manifesto_aggiorna_canvas_formato_pdf_e_web(): void
    {
        Storage::fake('public');
        [$referente, $agenzia] = $this->agenziaConReferente();
        $defunto = $this->defuntoConOrdine($agenzia, $referente);
        $manifesto = $this->manifesto($defunto);

        $risposta = $this->actingAs($referente)->postJson("/admin/api/manifesti/{$manifesto->id}/salva", [
            'canvas' => $this->canvasNonVuoto(), 'formato' => 'a3l',
            'pdf' => $this->pdfDataUrl(), 'web' => $this->webDataUrl(),
        ])->assertOk()->json();

        $this->assertTrue($risposta['success']);

        $manifesto->refresh();
        $this->assertSame('a3l', $manifesto->formato);
        $this->assertSame('Prova', $manifesto->canvas['objects'][0]['text']);
        $this->assertNotNull($manifesto->pdf);
        $this->assertNotNull($manifesto->web);
        Storage::disk('public')->assertExists($manifesto->pdf);
        Storage::disk('public')->assertExists($manifesto->web);
    }

    public function test_salvare_accetta_il_pdf_cosi_come_lo_produce_jspdf(): void
    {
        Storage::fake('public');
        [$referente, $agenzia] = $this->agenziaConReferente();
        $defunto = $this->defuntoConOrdine($agenzia, $referente);
        $manifesto = $this->manifesto($defunto);

        $this->actingAs($referente)->postJson("/admin/api/manifesti/{$manifesto->id}/salva", [
            'canvas' => $this->canvasNonVuoto(), 'formato' => 'a3l', 'pdf' => $this->pdfDataUrlComeJsPdf(),
        ])->assertOk()->assertJson(['success' => true]);

        Storage::disk('public')->assertExists($manifesto->fresh()->pdf);
    }

    public function test_risalvare_toglie_pdf_e_web_vecchi(): void
    {
        Storage::fake('public');
        [$referente, $agenzia] = $this->agenziaConReferente();
        $defunto = $this->defuntoConOrdine($agenzia, $referente);
        $manifesto = $this->manifesto($defunto);

        $this->actingAs($referente)->postJson("/admin/api/manifesti/{$manifesto->id}/salva", [
            'canvas' => $this->canvasNonVuoto(), 'formato' => 'a3l',
            'pdf' => $this->pdfDataUrl(), 'web' => $this->webDataUrl('prima'),
        ]);
        $primoPdf = $manifesto->fresh()->pdf;
        $primoWeb = $manifesto->fresh()->web;

        $this->actingAs($referente)->postJson("/admin/api/manifesti/{$manifesto->id}/salva", [
            'canvas' => $this->canvasNonVuoto(), 'formato' => 'a3l',
            'pdf' => $this->pdfDataUrl(), 'web' => $this->webDataUrl('seconda'),
        ]);

        $manifesto->refresh();
        $this->assertNotSame($primoPdf, $manifesto->pdf);
        $this->assertNotSame($primoWeb, $manifesto->web);
        Storage::disk('public')->assertMissing($primoPdf);
        Storage::disk('public')->assertMissing($primoWeb);
        Storage::disk('public')->assertExists($manifesto->pdf);
        Storage::disk('public')->assertExists($manifesto->web);
    }

    public function test_salvare_senza_pdf_conserva_il_file_esistente(): void
    {
        Storage::fake('public');
        [$referente, $agenzia] = $this->agenziaConReferente();
        $defunto = $this->defuntoConOrdine($agenzia, $referente);
        $manifesto = $this->manifesto($defunto);
        $manifesto->update(['pdf' => 'agenzie/prova/social/manifesti/gia-esistente.pdf']);
        Storage::disk('public')->put('agenzie/prova/social/manifesti/gia-esistente.pdf', 'x');

        $this->actingAs($referente)->postJson("/admin/api/manifesti/{$manifesto->id}/salva", [
            'canvas' => $this->canvasNonVuoto(), 'formato' => 'a4p',
        ])->assertOk();

        $manifesto->refresh();
        $this->assertSame('a4p', $manifesto->formato);
        $this->assertSame('agenzie/prova/social/manifesti/gia-esistente.pdf', $manifesto->pdf);
        Storage::disk('public')->assertExists($manifesto->pdf);
    }

    public function test_un_altra_agenzia_non_puo_salvare_il_manifesto_di_un_defunto_non_suo(): void
    {
        [$referente, $agenzia] = $this->agenziaConReferente();
        $defunto = $this->defuntoConOrdine($agenzia, $referente);
        $manifesto = $this->manifesto($defunto);
        [$altroReferente] = $this->agenziaConReferente('Casa Funeraria Aurora S.n.c.', '12485671007');

        $this->actingAs($altroReferente)->postJson("/admin/api/manifesti/{$manifesto->id}/salva", [
            'canvas' => $this->canvasNonVuoto(), 'formato' => 'a3l',
        ])->assertNotFound();
    }

    // ---- duplica / principale / elimina -------------------------------------

    public function test_duplicare_un_manifesto_clona_canvas_e_formato(): void
    {
        [$referente, $agenzia] = $this->agenziaConReferente();
        $defunto = $this->defuntoConOrdine($agenzia, $referente);
        $manifesto = $this->manifesto($defunto);
        $manifesto->update(['canvas' => ['objects' => [['type' => 'textbox', 'text' => 'Funerale']]]]);

        $this->actingAs($referente)
            ->post(route('manifesti.duplica', $manifesto), ['etichetta' => 'Manifesto trigesimo'])
            ->assertRedirect();

        $duplicato = Manifesto::where('etichetta', 'Manifesto trigesimo')->firstOrFail();
        $this->assertSame('Funerale', $duplicato->canvas['objects'][0]['text']);
        $this->assertFalse($duplicato->principale);
        $this->assertSame($defunto->id, $duplicato->defunto_id);
    }

    public function test_impostare_principale_toglie_il_flag_dal_precedente(): void
    {
        [$referente, $agenzia] = $this->agenziaConReferente();
        $defunto = $this->defuntoConOrdine($agenzia, $referente);
        $primo = $this->manifesto($defunto, principale: true);
        $secondo = $this->manifesto($defunto, principale: false);

        $this->actingAs($referente)->post(route('manifesti.principale', $secondo))->assertRedirect();

        $this->assertFalse($primo->fresh()->principale);
        $this->assertTrue($secondo->fresh()->principale);
    }

    public function test_eliminare_un_manifesto_toglie_i_file(): void
    {
        Storage::fake('public');
        [$referente, $agenzia] = $this->agenziaConReferente();
        $defunto = $this->defuntoConOrdine($agenzia, $referente);
        $manifesto = $this->manifesto($defunto);
        Storage::disk('public')->put('finto.pdf', 'x');
        $manifesto->update(['pdf' => 'finto.pdf']);

        $this->actingAs($referente)->delete(route('manifesti.destroy', $manifesto))->assertRedirect();

        $this->assertDatabaseCount('manifesti', 0);
        Storage::disk('public')->assertMissing('finto.pdf');
    }

    // ---- template ------------------------------------------------------------

    public function test_una_agenzia_salva_un_template_manifesto_nel_proprio_archivio(): void
    {
        [$referente, $agenzia] = $this->agenziaConReferente();

        $risposta = $this->actingAs($referente)->postJson('/admin/api/manifesto-templates', [
            'nome' => 'Il nostro layout', 'formato' => 'a3l', 'fronte' => $this->canvasNonVuoto(),
        ])->assertOk()->json();

        $template = ManifestoTemplate::findOrFail($risposta['id']);
        $this->assertSame($agenzia->id, $template->agenzia_id);
    }

    public function test_lo_staff_salva_un_template_manifesto_globale(): void
    {
        $staff = $this->staff();

        $risposta = $this->actingAs($staff)->postJson('/admin/api/manifesto-templates', [
            'nome' => 'Layout per tutti', 'formato' => 'a3l', 'fronte' => $this->canvasNonVuoto(),
        ])->assertOk()->json();

        $template = ManifestoTemplate::findOrFail($risposta['id']);
        $this->assertNull($template->agenzia_id);
    }

    public function test_un_privato_non_puo_salvare_un_template_manifesto(): void
    {
        $privato = User::factory()->create();

        $this->actingAs($privato)->postJson('/admin/api/manifesto-templates', [
            'nome' => 'Tentativo', 'formato' => 'a3l', 'fronte' => $this->canvasNonVuoto(),
        ])->assertForbidden();

        $this->assertDatabaseCount('manifesto_templates', 0);
    }

    public function test_l_elenco_non_mostra_il_template_di_un_altra_agenzia(): void
    {
        [$mia] = $this->agenziaConReferente('Onoranze Funebri Bianchi S.r.l.', '00743110157');
        [$altra] = $this->agenziaConReferente('Casa Funeraria Aurora S.n.c.', '12485671007');

        $this->actingAs($altra)->postJson('/admin/api/manifesto-templates', [
            'nome' => 'Solo loro', 'formato' => 'a3l', 'fronte' => $this->canvasNonVuoto(),
        ])->assertOk();

        $elenco = $this->actingAs($mia)->getJson('/admin/api/manifesto-templates')->assertOk()->json();

        $this->assertEmpty(array_filter($elenco, fn ($t) => $t['nome'] === 'Solo loro'));
    }

    public function test_una_agenzia_non_modifica_il_template_di_un_altra(): void
    {
        [$mia] = $this->agenziaConReferente('Onoranze Funebri Bianchi S.r.l.', '00743110157');
        [$altra] = $this->agenziaConReferente('Casa Funeraria Aurora S.n.c.', '12485671007');

        $risposta = $this->actingAs($altra)->postJson('/admin/api/manifesto-templates', [
            'nome' => 'Loro', 'formato' => 'a3l', 'fronte' => $this->canvasNonVuoto(),
        ])->assertOk()->json();

        $this->actingAs($mia)->putJson('/admin/api/manifesto-templates/'.$risposta['id'], [
            'nome' => 'Rubato', 'fronte' => $this->canvasNonVuoto(),
        ])->assertForbidden();

        $this->actingAs($mia)->deleteJson('/admin/api/manifesto-templates/'.$risposta['id'])
            ->assertForbidden();

        $this->assertSame('Loro', ManifestoTemplate::find($risposta['id'])->nome);
    }

    public function test_una_agenzia_non_elimina_un_template_globale_dello_staff(): void
    {
        [$referente] = $this->agenziaConReferente();
        $staff = $this->staff();

        $risposta = $this->actingAs($staff)->postJson('/admin/api/manifesto-templates', [
            'nome' => 'Globale', 'formato' => 'a3l', 'fronte' => $this->canvasNonVuoto(),
        ])->assertOk()->json();

        $this->actingAs($referente)->deleteJson('/admin/api/manifesto-templates/'.$risposta['id'])
            ->assertForbidden();

        $this->assertNotNull(ManifestoTemplate::find($risposta['id']));
    }

    public function test_lo_staff_elimina_un_suo_template_globale(): void
    {
        $staff = $this->staff();

        $risposta = $this->actingAs($staff)->postJson('/admin/api/manifesto-templates', [
            'nome' => 'Globale', 'formato' => 'a3l', 'fronte' => $this->canvasNonVuoto(),
        ])->assertOk()->json();

        $this->actingAs($staff)->deleteJson('/admin/api/manifesto-templates/'.$risposta['id'])
            ->assertOk()->assertJson(['success' => true]);

        $this->assertNull(ManifestoTemplate::find($risposta['id']));
    }

    public function test_un_template_vuoto_viene_rifiutato(): void
    {
        [$referente] = $this->agenziaConReferente();

        $this->actingAs($referente)->postJson('/admin/api/manifesto-templates', [
            'nome' => 'Vuoto', 'formato' => 'a3l', 'fronte' => json_encode(['objects' => []]),
        ])->assertStatus(422);
    }
}
