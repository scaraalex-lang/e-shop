<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Commerce\Tests\Concerns\CreaSoggetti;
use Tests\TestCase;

class AreaAccountStaffTest extends TestCase
{
    use CreaSoggetti, RefreshDatabase;

    public function test_lo_staff_vede_area_staff_non_il_mio_account(): void
    {
        $this->actingAs($this->staff())
            ->get('/account')
            ->assertOk()
            ->assertSee('Area staff')
            ->assertDontSee('Il mio account')
            ->assertSee('Vedi gli ordini')
            ->assertDontSee('I miei ordini')
            ->assertDontSee('Richiedi l\'accesso');
    }

    public function test_un_privato_vede_ancora_il_mio_account(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/account')
            ->assertOk()
            ->assertSee('Il mio account')
            ->assertSee('I miei ordini')
            ->assertSee('Richiedi l\'accesso', false);
    }

    public function test_la_voce_ordini_nel_menu_porta_al_pannello_gestione(): void
    {
        $this->actingAs($this->staff())
            ->get('/account')
            ->assertOk()
            ->assertSee(route('gestione.ordini.index'), false);
    }
}
