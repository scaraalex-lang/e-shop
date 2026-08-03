<?php

namespace Modules\Commerce\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Commerce\Database\Seeders\ServiziEditorSeeder;
use Modules\Commerce\Models\ServizioEditor;
use Modules\Commerce\Tests\Concerns\CreaSoggetti;
use Tests\TestCase;

class GestioneServiziTest extends TestCase
{
    use CreaSoggetti, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        (new ServiziEditorSeeder)->run();
    }

    public function test_lo_staff_aggiorna_costo_e_stato_di_un_servizio(): void
    {
        $servizio = ServizioEditor::where('codice', 'ricordini')->firstOrFail();

        $this->actingAs($this->staff())->put(route('gestione.servizi.aggiorna', $servizio), [
            'costo_crediti' => 25,
            'attivo' => '1',
        ])->assertRedirect(route('gestione.servizi.index'));

        $servizio->refresh();
        $this->assertSame(25, $servizio->costo_crediti);
        $this->assertTrue($servizio->attivo);
    }

    public function test_lo_staff_imposta_il_costo_a_termine_dell_embed(): void
    {
        $servizio = ServizioEditor::where('codice', 'embed')->firstOrFail();

        $this->actingAs($this->staff())->put(route('gestione.servizi.aggiorna', $servizio), [
            'costo_crediti' => $servizio->costo_crediti,
            'costo_crediti_a_termine' => 12,
            'attivo' => '1',
        ]);

        $this->assertSame(12, $servizio->fresh()->costo_crediti_a_termine);
    }

    public function test_lasciare_vuoto_il_costo_a_termine_toglie_l_opzione(): void
    {
        $servizio = ServizioEditor::where('codice', 'embed')->firstOrFail();
        $servizio->update(['costo_crediti_a_termine' => 15]);

        $this->actingAs($this->staff())->put(route('gestione.servizi.aggiorna', $servizio), [
            'costo_crediti' => $servizio->costo_crediti,
            // niente 'costo_crediti_a_termine': il campo è vuoto nel form.
            'attivo' => '1',
        ]);

        $this->assertNull($servizio->fresh()->costo_crediti_a_termine);
    }

    public function test_disattivare_un_servizio_lo_toglie_dalla_validazione(): void
    {
        $servizio = ServizioEditor::where('codice', 'manifesti')->firstOrFail();

        $this->actingAs($this->staff())->put(route('gestione.servizi.aggiorna', $servizio), [
            'costo_crediti' => $servizio->costo_crediti,
            // niente 'attivo': una checkbox non spuntata non manda il campo.
        ]);

        $this->assertFalse($servizio->fresh()->attivo);
    }

    public function test_un_agenzia_non_puo_entrare_nel_pannello_staff(): void
    {
        $referente = $this->referenteAgenzia();

        $this->actingAs($referente)->get(route('gestione.servizi.index'))->assertNotFound();
    }
}
