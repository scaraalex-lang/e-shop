<?php

namespace Tests\Feature;

use App\Models\ContenutoVetrina;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Commerce\Tests\Concerns\CreaSoggetti;
use Tests\TestCase;

class GestioneContenutiTest extends TestCase
{
    use CreaSoggetti, RefreshDatabase;

    public function test_chi_non_e_staff_non_vede_nemmeno_che_gestione_esiste(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/gestione/contenuti')
            ->assertNotFound();
    }

    public function test_la_migration_ha_gia_seminato_i_testi_di_oggi(): void
    {
        $this->assertSame('Artigianato memoriale dal 2026', ContenutoVetrina::valore('hero.occhiello'));
        $this->assertSame('resta', ContenutoVetrina::valore('cta.titolo_enfasi'));
    }

    public function test_lo_staff_aggiorna_un_testo(): void
    {
        $risposta = $this->actingAs($this->staff())->put('/gestione/contenuti', [
            'valori' => [
                'hero.occhiello' => 'Nuovo occhiello di prova',
                'hero.titolo_intro' => 'Custodire la memoria',
                'hero.titolo_enfasi' => 'bellezza',
            ],
        ]);

        $risposta->assertRedirect(route('gestione.contenuti.edit'));
        $this->assertSame('Nuovo occhiello di prova', ContenutoVetrina::valore('hero.occhiello'));
    }

    public function test_la_home_riflette_un_testo_cambiato(): void
    {
        ContenutoVetrina::updateOrCreate(['chiave' => 'cta.titolo_intro'], ['valore' => 'Un ricordo di prova']);

        $this->get('/')->assertOk()->assertSee('Un ricordo di prova');
    }
}
