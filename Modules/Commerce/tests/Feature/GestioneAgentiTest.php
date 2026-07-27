<?php

namespace Modules\Commerce\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Commerce\Models\AgenteVendita;
use Modules\Commerce\Tests\Concerns\CreaSoggetti;
use Tests\TestCase;

class GestioneAgentiTest extends TestCase
{
    use CreaSoggetti, RefreshDatabase;

    public function test_chi_non_e_staff_non_vede_nemmeno_che_gestione_esiste(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/gestione/agenti')
            ->assertNotFound();
    }

    public function test_lo_staff_crea_un_agente(): void
    {
        $risposta = $this->actingAs($this->staff())->post('/gestione/agenti', [
            'nome' => 'Luca Verdi',
            'email' => 'luca@memorai.it',
            'telefono' => '3331234567',
        ]);

        $agente = AgenteVendita::firstOrFail();
        $risposta->assertRedirect(route('gestione.agenti.index'));
        $this->assertSame('Luca Verdi', $agente->nome);
    }

    public function test_il_nome_e_obbligatorio(): void
    {
        $this->actingAs($this->staff())
            ->post('/gestione/agenti', ['nome' => ''])
            ->assertSessionHasErrors('nome');

        $this->assertDatabaseCount('agenti_vendita', 0);
    }

    public function test_lo_staff_assegna_un_agente_a_unagenzia(): void
    {
        $agente = AgenteVendita::create(['nome' => 'Luca Verdi']);
        $referente = $this->referenteAgenzia();

        $this->actingAs($this->staff())
            ->post(route('gestione.agenzie.agente', $referente->agenzia), ['agente_vendita_id' => $agente->id])
            ->assertRedirect(route('gestione.agenzie.show', $referente->agenzia));

        $this->assertSame($agente->id, $referente->agenzia->fresh()->agente_vendita_id);
    }

    public function test_si_puo_togliere_lagente_da_unagenzia(): void
    {
        $agente = AgenteVendita::create(['nome' => 'Luca Verdi']);
        $referente = $this->referenteAgenzia();
        $referente->agenzia->assegnaAgente($agente);

        $this->actingAs($this->staff())
            ->post(route('gestione.agenzie.agente', $referente->agenzia), ['agente_vendita_id' => '']);

        $this->assertNull($referente->agenzia->fresh()->agente_vendita_id);
    }

    public function test_eliminare_un_agente_toglie_lassegnazione_dalle_sue_agenzie(): void
    {
        $agente = AgenteVendita::create(['nome' => 'Luca Verdi']);
        $referente = $this->referenteAgenzia();
        $referente->agenzia->assegnaAgente($agente);

        $this->actingAs($this->staff())
            ->delete(route('gestione.agenti.destroy', $agente))
            ->assertRedirect(route('gestione.agenti.index'));

        $this->assertSame(0, AgenteVendita::count(), 'un agente eliminato non compare più fra quelli attivi');
        $this->assertNull($referente->agenzia->fresh()->agente_vendita_id);
    }
}
