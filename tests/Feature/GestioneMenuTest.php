<?php

namespace Tests\Feature;

use App\Enums\ZonaMenu;
use App\Models\VoceMenu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Commerce\Tests\Concerns\CreaSoggetti;
use Tests\TestCase;

class GestioneMenuTest extends TestCase
{
    use CreaSoggetti, RefreshDatabase;

    public function test_chi_non_e_staff_non_vede_nemmeno_che_gestione_esiste(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/gestione/menu')
            ->assertNotFound();
    }

    public function test_la_migration_ha_gia_seminato_le_voci_di_oggi(): void
    {
        $this->assertTrue(VoceMenu::inZona(ZonaMenu::Principale)->where('etichetta', 'Trigesimali')->exists());
        $this->assertTrue(VoceMenu::inZona(ZonaMenu::Legale)->where('etichetta', 'Privacy')->exists());
    }

    public function test_lo_staff_crea_una_voce(): void
    {
        $risposta = $this->actingAs($this->staff())->post('/gestione/menu', [
            'zona' => ZonaMenu::FooterServizi->value,
            'etichetta' => 'Contattaci su WhatsApp',
            'url' => 'https://wa.me/391234567',
            'sort_order' => 5,
            'is_active' => '1',
        ]);

        $voce = VoceMenu::where('etichetta', 'Contattaci su WhatsApp')->firstOrFail();
        $risposta->assertRedirect(route('gestione.menu.index'));
        $this->assertSame(ZonaMenu::FooterServizi, $voce->zona);
    }

    public function test_lo_staff_elimina_una_voce(): void
    {
        $voce = VoceMenu::create([
            'zona' => ZonaMenu::Principale, 'etichetta' => 'Prova', 'url' => '#', 'sort_order' => 0,
        ]);

        $this->actingAs($this->staff())
            ->delete(route('gestione.menu.destroy', $voce))
            ->assertRedirect(route('gestione.menu.index'));

        $this->assertDatabaseMissing('voci_menu', ['id' => $voce->id]);
    }

    public function test_il_layout_mostra_solo_le_voci_attive_del_menu_principale(): void
    {
        // /carrello estende lo stesso layout della home senza passare dalla
        // query "in evidenza" (SKU fissi con FIELD(), non portabile su
        // SQLite — la sistema il prossimo pezzo, is_featured).
        VoceMenu::query()->delete();
        VoceMenu::create(['zona' => ZonaMenu::Principale, 'etichetta' => 'Voce attiva', 'url' => '/x', 'sort_order' => 0, 'is_active' => true]);
        VoceMenu::create(['zona' => ZonaMenu::Principale, 'etichetta' => 'Voce spenta', 'url' => '/y', 'sort_order' => 1, 'is_active' => false]);

        $this->get('/carrello')
            ->assertOk()
            ->assertSee('Voce attiva')
            ->assertDontSee('Voce spenta');
    }

    public function test_il_footer_mostra_le_voci_per_colonna(): void
    {
        VoceMenu::query()->where('zona', ZonaMenu::FooterCollezioni)->delete();
        VoceMenu::create(['zona' => ZonaMenu::FooterCollezioni, 'etichetta' => 'Collezione di prova', 'url' => '/z', 'sort_order' => 0, 'is_active' => true]);

        $this->get('/carrello')
            ->assertOk()
            ->assertSee('Collezione di prova');
    }
}
