<?php

namespace Modules\Memorial\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Modules\Commerce\Enums\RuoloUtente;
use Modules\Commerce\Models\Agenzia;
use Modules\Memorial\Models\Defunto;
use Modules\Memorial\Models\ManifestoTemplate;
use Modules\Memorial\Models\Necrologio;
use Tests\TestCase;

/**
 * Il designer manifesti: canvas Fabric per comporre il manifesto funebre,
 * col QR del necrologio generato in locale. Stesso schema di ownership del
 * card designer (agenzia proprietaria) e dei template del ricordino
 * (globale/agenzia, editabile solo da chi ne è proprietario).
 */
class ManifestoDesignerTest extends TestCase
{
    use RefreshDatabase;

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

    private function necrologio(Agenzia $agenzia): Necrologio
    {
        $defunto = Defunto::create(['nome' => 'Luigia', 'cognome' => 'Rossetti']);

        return Necrologio::create([
            'defunto_id' => $defunto->id,
            'agenzia_id' => $agenzia->id,
            'percorso' => Necrologio::componiPercorso($defunto),
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

    // ---- accesso -----------------------------------------------------------

    public function test_solo_l_agenzia_proprietaria_apre_il_designer_manifesti(): void
    {
        [$referente, $agenzia] = $this->agenziaConReferente();
        $n = $this->necrologio($agenzia);

        [$altroReferente] = $this->agenziaConReferente('Casa Funeraria Aurora S.n.c.', '12485671007');

        $this->actingAs($referente)->get(route('necrologi.manifesto', $n))->assertOk();
        $this->actingAs($altroReferente)->get(route('necrologi.manifesto', $n))->assertNotFound();
    }

    public function test_un_privato_non_puo_disegnare_manifesti(): void
    {
        [, $agenzia] = $this->agenziaConReferente();
        $n = $this->necrologio($agenzia);

        $this->actingAs(User::factory()->create())
            ->get(route('necrologi.manifesto', $n))
            ->assertForbidden();
    }

    // ---- salvataggio ---------------------------------------------------------

    public function test_salvare_il_manifesto_aggiorna_canvas_formato_e_file(): void
    {
        Storage::fake('public');
        [$referente, $agenzia] = $this->agenziaConReferente();
        $n = $this->necrologio($agenzia);

        $risposta = $this->actingAs($referente)->postJson(
            "/admin/api/necrologi/{$n->id}/salva-manifesto",
            ['canvas' => $this->canvasNonVuoto(), 'formato' => 'a3l', 'pdf' => $this->pdfDataUrl()]
        )->assertOk()->json();

        $this->assertTrue($risposta['success']);

        $n->refresh();
        $this->assertSame('a3l', $n->manifesto_formato);
        $this->assertNotNull($n->manifesto_canvas);
        $this->assertSame('Prova', $n->manifesto_canvas['objects'][0]['text']);
        $this->assertNotNull($n->manifesto);
        Storage::disk('public')->assertExists($n->manifesto);
    }

    public function test_salvare_accetta_il_pdf_cosi_come_lo_produce_jspdf(): void
    {
        Storage::fake('public');
        [$referente, $agenzia] = $this->agenziaConReferente();
        $n = $this->necrologio($agenzia);

        $risposta = $this->actingAs($referente)->postJson(
            "/admin/api/necrologi/{$n->id}/salva-manifesto",
            ['canvas' => $this->canvasNonVuoto(), 'formato' => 'a3l', 'pdf' => $this->pdfDataUrlComeJsPdf()]
        )->assertOk()->json();

        $this->assertTrue($risposta['success']);
        Storage::disk('public')->assertExists($n->fresh()->manifesto);
    }

    public function test_risalvare_toglie_il_pdf_vecchio(): void
    {
        Storage::fake('public');
        [$referente, $agenzia] = $this->agenziaConReferente();
        $n = $this->necrologio($agenzia);

        $this->actingAs($referente)->postJson(
            "/admin/api/necrologi/{$n->id}/salva-manifesto",
            ['canvas' => $this->canvasNonVuoto(), 'formato' => 'a3l', 'pdf' => $this->pdfDataUrl()]
        )->assertOk();
        $primoPath = $n->fresh()->manifesto;

        $this->actingAs($referente)->postJson(
            "/admin/api/necrologi/{$n->id}/salva-manifesto",
            ['canvas' => $this->canvasNonVuoto(), 'formato' => 'a3l', 'pdf' => $this->pdfDataUrl()]
        )->assertOk();

        $n->refresh();
        $this->assertNotSame($primoPath, $n->manifesto);
        Storage::disk('public')->assertMissing($primoPath);
        Storage::disk('public')->assertExists($n->manifesto);
    }

    public function test_salvare_con_anteprima_genera_la_miniatura_pubblica(): void
    {
        Storage::fake('public');
        [$referente, $agenzia] = $this->agenziaConReferente();
        $n = $this->necrologio($agenzia);

        $this->actingAs($referente)->postJson(
            "/admin/api/necrologi/{$n->id}/salva-manifesto",
            ['canvas' => $this->canvasNonVuoto(), 'formato' => 'a3l', 'anteprima' => 'data:image/jpeg;base64,'.base64_encode('finta miniatura')]
        )->assertOk();

        $n->refresh();
        $this->assertNotNull($n->manifesto_anteprima);
        Storage::disk('public')->assertExists($n->manifesto_anteprima);
        $this->assertNotNull($n->manifestoAnteprimaUrl());
    }

    public function test_risalvare_toglie_l_anteprima_vecchia(): void
    {
        Storage::fake('public');
        [$referente, $agenzia] = $this->agenziaConReferente();
        $n = $this->necrologio($agenzia);

        $this->actingAs($referente)->postJson(
            "/admin/api/necrologi/{$n->id}/salva-manifesto",
            ['canvas' => $this->canvasNonVuoto(), 'formato' => 'a3l', 'anteprima' => 'data:image/jpeg;base64,'.base64_encode('prima')]
        )->assertOk();
        $primaAnteprima = $n->fresh()->manifesto_anteprima;

        $this->actingAs($referente)->postJson(
            "/admin/api/necrologi/{$n->id}/salva-manifesto",
            ['canvas' => $this->canvasNonVuoto(), 'formato' => 'a3l', 'anteprima' => 'data:image/jpeg;base64,'.base64_encode('seconda')]
        )->assertOk();

        $n->refresh();
        $this->assertNotSame($primaAnteprima, $n->manifesto_anteprima);
        Storage::disk('public')->assertMissing($primaAnteprima);
        Storage::disk('public')->assertExists($n->manifesto_anteprima);
    }

    public function test_salvare_senza_pdf_aggiorna_solo_lo_stato_del_canvas(): void
    {
        Storage::fake('public');
        [$referente, $agenzia] = $this->agenziaConReferente();
        $n = $this->necrologio($agenzia);
        $n->update(['manifesto' => 'necrologi/manifesti/gia-esistente.pdf']);
        Storage::disk('public')->put('necrologi/manifesti/gia-esistente.pdf', 'x');

        $this->actingAs($referente)->postJson(
            "/admin/api/necrologi/{$n->id}/salva-manifesto",
            ['canvas' => $this->canvasNonVuoto(), 'formato' => 'a4p']
        )->assertOk();

        $n->refresh();
        $this->assertSame('a4p', $n->manifesto_formato);
        $this->assertSame('necrologi/manifesti/gia-esistente.pdf', $n->manifesto, 'senza un pdf nuovo il file esistente resta');
        Storage::disk('public')->assertExists($n->manifesto);
    }

    public function test_un_altra_agenzia_non_puo_salvare_il_manifesto_di_un_necrologio_non_suo(): void
    {
        [, $agenzia] = $this->agenziaConReferente();
        $n = $this->necrologio($agenzia);
        [$altroReferente] = $this->agenziaConReferente('Casa Funeraria Aurora S.n.c.', '12485671007');

        $this->actingAs($altroReferente)->postJson(
            "/admin/api/necrologi/{$n->id}/salva-manifesto",
            ['canvas' => $this->canvasNonVuoto(), 'formato' => 'a3l']
        )->assertNotFound();
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
