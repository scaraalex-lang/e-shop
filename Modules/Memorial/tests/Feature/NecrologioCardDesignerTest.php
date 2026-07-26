<?php

namespace Modules\Memorial\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Commerce\Enums\RuoloUtente;
use Modules\Commerce\Models\Agenzia;
use Modules\Memorial\Models\Defunto;
use Modules\Memorial\Models\Necrologio;
use Modules\Memorial\Models\NecrologioCardTemplate;
use Tests\TestCase;

/**
 * Il card designer del necrologio: disegna e salva come file l'anteprima
 * social, perché WhatsApp e Facebook non eseguono JavaScript.
 */
class NecrologioCardDesignerTest extends TestCase
{
    use RefreshDatabase;

    private function agenziaConReferente(): array
    {
        $staff = User::factory()->create();
        $staff->ruolo = RuoloUtente::Staff;
        $staff->save();

        $agenzia = Agenzia::create([
            'ragione_sociale' => 'Onoranze Funebri Bianchi S.r.l.',
            'partita_iva' => '00743110157',
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

    private function pngDataUrl(): string
    {
        // 1x1 pixel PNG trasparente.
        $binario = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');

        return 'data:image/png;base64,'.base64_encode($binario);
    }

    public function test_solo_l_agenzia_proprietaria_apre_il_designer(): void
    {
        [$referente, $agenzia] = $this->agenziaConReferente();
        $n = $this->necrologio($agenzia);

        $altra = Agenzia::create([
            'ragione_sociale' => 'Casa Funeraria Aurora S.n.c.',
            'partita_iva' => '12485671007',
            'indirizzo' => 'Corso Vittorio 88', 'cap' => '10121',
            'citta' => 'Torino', 'provincia' => 'TO', 'telefono' => '011998877',
        ]);
        $altroReferente = User::factory()->create();
        $altroReferente->ruolo = RuoloUtente::Agenzia;
        $altroReferente->agenzia()->associate($altra);
        $altroReferente->save();

        $this->actingAs($referente)->get(route('necrologi.designer', $n))->assertOk();
        $this->actingAs($altroReferente)->get(route('necrologi.designer', $n))->assertNotFound();
    }

    public function test_salvare_la_card_aggiorna_og_image_e_toglie_il_file_vecchio(): void
    {
        Storage::fake('public');
        [$referente, $agenzia] = $this->agenziaConReferente();
        $n = $this->necrologio($agenzia);

        $prima = $this->actingAs($referente)->postJson(
            "/admin/api/necrologi/{$n->id}/salva-card",
            ['image_data' => $this->pngDataUrl()]
        )->assertOk()->json();

        $n->refresh();
        $this->assertNotNull($n->og_image);
        Storage::disk('public')->assertExists($n->og_image);

        $vecchioPath = $n->og_image;

        $this->actingAs($referente)->postJson(
            "/admin/api/necrologi/{$n->id}/salva-card",
            ['image_data' => $this->pngDataUrl()]
        )->assertOk();

        $n->refresh();
        $this->assertNotSame($vecchioPath, $n->og_image, 'un nome nuovo a ogni salvataggio, per non restare nella cache di WhatsApp');
        Storage::disk('public')->assertMissing($vecchioPath);
        Storage::disk('public')->assertExists($n->og_image);
    }

    public function test_un_altra_agenzia_non_puo_salvare_la_card_di_un_necrologio_non_suo(): void
    {
        [, $agenzia] = $this->agenziaConReferente();
        $n = $this->necrologio($agenzia);

        $altra = Agenzia::create([
            'ragione_sociale' => 'Casa Funeraria Aurora S.n.c.',
            'partita_iva' => '12485671007',
            'indirizzo' => 'Corso Vittorio 88', 'cap' => '10121',
            'citta' => 'Torino', 'provincia' => 'TO', 'telefono' => '011998877',
        ]);
        $altroReferente = User::factory()->create();
        $altroReferente->ruolo = RuoloUtente::Agenzia;
        $altroReferente->agenzia()->associate($altra);
        $altroReferente->save();

        $this->actingAs($altroReferente)->postJson(
            "/admin/api/necrologi/{$n->id}/salva-card",
            ['image_data' => $this->pngDataUrl()]
        )->assertNotFound();
    }

    public function test_i_template_sono_scoped_per_agenzia(): void
    {
        Storage::fake('public');
        [$referente, $agenzia] = $this->agenziaConReferente();

        $altra = Agenzia::create([
            'ragione_sociale' => 'Casa Funeraria Aurora S.n.c.',
            'partita_iva' => '12485671007',
            'indirizzo' => 'Corso Vittorio 88', 'cap' => '10121',
            'citta' => 'Torino', 'provincia' => 'TO', 'telefono' => '011998877',
        ]);
        NecrologioCardTemplate::create(['agenzia_id' => $altra->id, 'nome' => 'Non mio', 'path' => 'x.png']);

        $risposta = $this->actingAs($referente)->postJson('/admin/api/necrologio-card-templates', [
            'nome' => 'Sfondo dorato',
            'template' => UploadedFile::fake()->image('sfondo.png', 910, 1093),
        ])->assertOk()->json();

        $this->assertTrue($risposta['success']);

        $elenco = $this->actingAs($referente)->getJson('/admin/api/necrologio-card-templates')->assertOk()->json();

        $this->assertCount(1, $elenco);
        $this->assertSame('Sfondo dorato', $elenco[0]['nome']);
    }

    public function test_eliminare_il_template_di_un_altra_agenzia_da_403(): void
    {
        [$referente, $agenzia] = $this->agenziaConReferente();

        $altra = Agenzia::create([
            'ragione_sociale' => 'Casa Funeraria Aurora S.n.c.',
            'partita_iva' => '12485671007',
            'indirizzo' => 'Corso Vittorio 88', 'cap' => '10121',
            'citta' => 'Torino', 'provincia' => 'TO', 'telefono' => '011998877',
        ]);
        $template = NecrologioCardTemplate::create(['agenzia_id' => $altra->id, 'nome' => 'Non mio', 'path' => 'x.png']);

        $this->actingAs($referente)
            ->deleteJson("/admin/api/necrologio-card-templates/{$template->id}")
            ->assertForbidden();
    }

    public function test_un_privato_non_puo_disegnare_card(): void
    {
        $privato = User::factory()->create();
        $agenzia = Agenzia::create([
            'ragione_sociale' => 'Onoranze Funebri Bianchi S.r.l.',
            'partita_iva' => '00743110157',
            'indirizzo' => 'Via Roma 12', 'cap' => '20121',
            'citta' => 'Milano', 'provincia' => 'MI', 'telefono' => '0212345678',
        ]);
        $n = $this->necrologio($agenzia);

        $this->actingAs($privato)->get(route('necrologi.designer', $n))->assertForbidden();
    }
}
