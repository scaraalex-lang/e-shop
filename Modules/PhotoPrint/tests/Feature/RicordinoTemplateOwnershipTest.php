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
use Modules\Memorial\Models\RicordinoTemplate;
use Tests\TestCase;

/**
 * Chi vede e chi può toccare un template salvato del Ricordino Designer.
 *
 * Decisione presa (skill studio-editor, punto 2): i predefiniti MemorAI
 * restano solo nel seeder; un'agenzia ha il proprio archivio, invisibile
 * alle altre; lo staff può salvare un layout globale (visibile a tutti,
 * ma non un predefinito vero — resta modificabile); un privato non ha un
 * archivio personale, parte da un predefinito.
 */
class RicordinoTemplateOwnershipTest extends TestCase
{
    use RefreshDatabase;

    private function canvasNonVuoto(): string
    {
        return json_encode(['objects' => [['type' => 'textbox', 'text' => 'Prova']]]);
    }

    private function staff(): User
    {
        $user = User::factory()->create();
        $user->ruolo = RuoloUtente::Staff;
        $user->save();

        return $user;
    }

    private function agenziaApprovata(string $ragioneSociale, string $partitaIva): User
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

    /** Un privato con un ordine proprio aperto in lavorazione: l'unico modo in cui entra negli editor senza agenzia. */
    private function privatoConLavorazioneAperta(): User
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
        $ordine = Ordine::latest('id')->firstOrFail();

        $this->actingAs($utente)->get("/account/ordini/{$ordine->numero}/lavorazione")->assertOk();

        return $utente;
    }

    public function test_una_agenzia_salva_nel_proprio_archivio(): void
    {
        $referente = $this->agenziaApprovata('Onoranze Funebri Bianchi S.r.l.', '00743110157');

        $risposta = $this->actingAs($referente)->postJson('/admin/api/ricordino-templates', [
            'name' => 'Il nostro layout', 'canvas_fronte' => $this->canvasNonVuoto(),
        ])->assertOk()->json();

        $template = RicordinoTemplate::findOrFail($risposta['id']);
        $this->assertSame($referente->agenzia->id, $template->agenzia_id);
        $this->assertFalse($template->is_predefinito);
    }

    public function test_lo_staff_salva_un_template_globale(): void
    {
        $staff = $this->staff();

        $risposta = $this->actingAs($staff)->postJson('/admin/api/ricordino-templates', [
            'name' => 'Layout per tutti', 'canvas_fronte' => $this->canvasNonVuoto(),
        ])->assertOk()->json();

        $template = RicordinoTemplate::findOrFail($risposta['id']);
        $this->assertNull($template->agenzia_id);
        $this->assertFalse($template->is_predefinito, 'globale non vuol dire predefinito: la promozione resta un\'azione a parte');
    }

    public function test_un_privato_non_puo_salvare_un_template(): void
    {
        $privato = $this->privatoConLavorazioneAperta();

        $this->actingAs($privato)->postJson('/admin/api/ricordino-templates', [
            'name' => 'Tentativo', 'canvas_fronte' => $this->canvasNonVuoto(),
        ])->assertForbidden();

        $this->assertDatabaseCount('ricordino_templates', 0);
    }

    public function test_l_elenco_non_mostra_il_template_di_un_altra_agenzia(): void
    {
        $mia = $this->agenziaApprovata('Onoranze Funebri Bianchi S.r.l.', '00743110157');
        $altra = $this->agenziaApprovata('Casa Funeraria Aurora S.n.c.', '12485671007');

        $this->actingAs($altra)->postJson('/admin/api/ricordino-templates', [
            'name' => 'Solo loro', 'canvas_fronte' => $this->canvasNonVuoto(),
        ])->assertOk();

        $elenco = $this->actingAs($mia)->getJson('/admin/api/ricordino-templates')->assertOk()->json();

        $this->assertEmpty(array_filter($elenco, fn ($t) => $t['name'] === 'Solo loro'));
    }

    public function test_un_privato_vede_solo_i_globali(): void
    {
        $agenzia = $this->agenziaApprovata('Onoranze Funebri Bianchi S.r.l.', '00743110157');
        $staff = $this->staff();
        $privato = $this->privatoConLavorazioneAperta();

        $this->actingAs($agenzia)->postJson('/admin/api/ricordino-templates', [
            'name' => 'Dell\'agenzia', 'canvas_fronte' => $this->canvasNonVuoto(),
        ])->assertOk();
        $this->actingAs($staff)->postJson('/admin/api/ricordino-templates', [
            'name' => 'Globale', 'canvas_fronte' => $this->canvasNonVuoto(),
        ])->assertOk();

        $elenco = $this->actingAs($privato)->getJson('/admin/api/ricordino-templates')->assertOk()->json();
        $nomi = array_column($elenco, 'name');

        $this->assertContains('Globale', $nomi);
        $this->assertNotContains('Dell\'agenzia', $nomi);
    }

    public function test_l_elenco_espone_i_flag_globale_ed_editabile(): void
    {
        $agenzia = $this->agenziaApprovata('Onoranze Funebri Bianchi S.r.l.', '00743110157');
        $staff = $this->staff();
        $this->actingAs($staff)->postJson('/admin/api/ricordino-templates', [
            'name' => 'Globale', 'canvas_fronte' => $this->canvasNonVuoto(),
        ])->assertOk();

        $elenco = collect($this->actingAs($agenzia)->getJson('/admin/api/ricordino-templates')->assertOk()->json())
            ->keyBy('name');

        $this->assertTrue($elenco['Globale']['globale'], 'agenzia_id null = globale');
        $this->assertFalse($elenco['Globale']['editabile'], 'un layout dello staff non e\' dell\'agenzia che lo guarda');
    }

    public function test_l_elenco_espone_editabile_per_il_proprietario_e_non_per_gli_altri(): void
    {
        $mia = $this->agenziaApprovata('Onoranze Funebri Bianchi S.r.l.', '00743110157');
        $altra = $this->agenziaApprovata('Casa Funeraria Aurora S.n.c.', '12485671007');

        $this->actingAs($mia)->postJson('/admin/api/ricordino-templates', [
            'name' => 'Mio', 'canvas_fronte' => $this->canvasNonVuoto(),
        ])->assertOk();

        $mioLato = collect($this->actingAs($mia)->getJson('/admin/api/ricordino-templates')->assertOk()->json())->keyBy('name');
        $this->assertTrue($mioLato['Mio']['editabile']);

        $altroLato = collect($this->actingAs($altra)->getJson('/admin/api/ricordino-templates')->assertOk()->json())->keyBy('name');
        $this->assertArrayNotHasKey('Mio', $altroLato, 'invisibile, non solo non modificabile');
    }

    public function test_una_agenzia_non_modifica_il_template_di_un_altra(): void
    {
        $mia = $this->agenziaApprovata('Onoranze Funebri Bianchi S.r.l.', '00743110157');
        $altra = $this->agenziaApprovata('Casa Funeraria Aurora S.n.c.', '12485671007');

        $risposta = $this->actingAs($altra)->postJson('/admin/api/ricordino-templates', [
            'name' => 'Loro', 'canvas_fronte' => $this->canvasNonVuoto(),
        ])->assertOk()->json();

        $this->actingAs($mia)->putJson('/admin/api/ricordino-templates/'.$risposta['id'], [
            'name' => 'Rubato', 'canvas_fronte' => $this->canvasNonVuoto(),
        ])->assertForbidden();

        $this->actingAs($mia)->deleteJson('/admin/api/ricordino-templates/'.$risposta['id'])
            ->assertForbidden();

        $this->assertSame('Loro', RicordinoTemplate::find($risposta['id'])->nome);
    }

    public function test_una_agenzia_non_modifica_un_template_globale_dello_staff(): void
    {
        $agenzia = $this->agenziaApprovata('Onoranze Funebri Bianchi S.r.l.', '00743110157');
        $staff = $this->staff();

        $risposta = $this->actingAs($staff)->postJson('/admin/api/ricordino-templates', [
            'name' => 'Globale', 'canvas_fronte' => $this->canvasNonVuoto(),
        ])->assertOk()->json();

        $this->actingAs($agenzia)->deleteJson('/admin/api/ricordino-templates/'.$risposta['id'])
            ->assertForbidden();

        $this->assertNotNull(RicordinoTemplate::find($risposta['id']));
    }

    public function test_lo_staff_elimina_un_suo_template_globale_ma_non_un_predefinito(): void
    {
        $staff = $this->staff();

        $mio = RicordinoTemplate::create([
            'nome' => 'Mio globale', 'formato' => '7x10', 'agenzia_id' => null,
            'fronte' => ['objects' => []], 'is_predefinito' => false,
        ]);
        $predefinito = RicordinoTemplate::create([
            'nome' => 'Classico', 'formato' => '7x10', 'agenzia_id' => null,
            'fronte' => ['objects' => []], 'is_predefinito' => true,
        ]);

        $this->actingAs($staff)->deleteJson('/admin/api/ricordino-templates/'.$mio->id)
            ->assertOk()->assertJson(['success' => true]);

        $this->actingAs($staff)->deleteJson('/admin/api/ricordino-templates/'.$predefinito->id)
            ->assertForbidden();

        $this->assertNull(RicordinoTemplate::find($mio->id));
        $this->assertNotNull(RicordinoTemplate::find($predefinito->id));
    }
}
