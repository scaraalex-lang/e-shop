<?php

namespace Modules\Commerce\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Commerce\Enums\RuoloUtente;
use Modules\Commerce\Enums\StatoAgenzia;
use Modules\Commerce\Models\Agenzia;
use Tests\TestCase;

class GestioneAgenzieTest extends TestCase
{
    use RefreshDatabase;

    private function staff(): User
    {
        $user = User::factory()->create();
        $user->ruolo = RuoloUtente::Staff;
        $user->save();

        return $user;
    }

    private function agenziaConReferente(): Agenzia
    {
        $agenzia = Agenzia::create([
            'ragione_sociale' => 'Onoranze Funebri Bianchi S.r.l.',
            'partita_iva' => '00743110157',
            'indirizzo' => 'Via Roma 12',
            'cap' => '20121',
            'citta' => 'Milano',
            'provincia' => 'MI',
            'telefono' => '0212345678',
        ]);

        $referente = User::factory()->create();
        $referente->ruolo = RuoloUtente::Agenzia;
        $referente->agenzia()->associate($agenzia);
        $referente->save();

        return $agenzia->refresh();
    }

    public function test_chi_non_e_staff_non_vede_nemmeno_che_gestione_esiste(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/gestione/agenzie')
            ->assertNotFound();
    }

    public function test_il_referente_di_una_agenzia_non_entra_in_gestione(): void
    {
        $agenzia = $this->agenziaConReferente();

        $this->actingAs($agenzia->user)
            ->get('/gestione/agenzie')
            ->assertNotFound();
    }

    public function test_lo_staff_vede_lelenco_delle_agenzie(): void
    {
        $agenzia = $this->agenziaConReferente();

        $this->actingAs($this->staff())
            ->get('/gestione/agenzie')
            ->assertOk()
            ->assertSee($agenzia->ragione_sociale);
    }

    public function test_lo_staff_approva_una_agenzia(): void
    {
        $staff = $this->staff();
        $agenzia = $this->agenziaConReferente();

        $this->actingAs($staff)
            ->post("/gestione/agenzie/{$agenzia->id}/approva", ['note_interne' => 'P.IVA verificata'])
            ->assertRedirect(route('gestione.agenzie.show', $agenzia));

        $agenzia->refresh();
        $this->assertSame(StatoAgenzia::Approvata, $agenzia->stato);
        $this->assertSame($staff->id, $agenzia->stato_aggiornato_da);
        $this->assertNotNull($agenzia->stato_aggiornato_at);
        $this->assertTrue($agenzia->user->fresh()->eAgenziaApprovata());
    }

    public function test_il_rifiuto_richiede_un_motivo_e_lo_mostra_allagenzia(): void
    {
        $staff = $this->staff();
        $agenzia = $this->agenziaConReferente();

        $this->actingAs($staff)
            ->post("/gestione/agenzie/{$agenzia->id}/rifiuta", ['motivo_rifiuto' => ''])
            ->assertSessionHasErrors('motivo_rifiuto');

        $this->assertSame(StatoAgenzia::InAttesa, $agenzia->fresh()->stato);

        $this->actingAs($staff)
            ->post("/gestione/agenzie/{$agenzia->id}/rifiuta", ['motivo_rifiuto' => 'P.IVA non riscontrata']);

        $agenzia->refresh();
        $this->assertSame(StatoAgenzia::Rifiutata, $agenzia->stato);

        $this->actingAs($agenzia->user)
            ->get('/account')
            ->assertOk()
            ->assertSee('P.IVA non riscontrata');
    }

    public function test_sospendere_toglie_le_condizioni_b2b(): void
    {
        $staff = $this->staff();
        $agenzia = $this->agenziaConReferente();
        $agenzia->approva($staff);

        $this->actingAs($staff)->post("/gestione/agenzie/{$agenzia->id}/sospendi");

        $this->assertSame(StatoAgenzia::Sospesa, $agenzia->fresh()->stato);
        $this->assertFalse($agenzia->user->fresh()->eAgenziaApprovata());
    }

    public function test_le_note_interne_non_arrivano_allagenzia(): void
    {
        $staff = $this->staff();
        $agenzia = $this->agenziaConReferente();

        $this->actingAs($staff)
            ->post("/gestione/agenzie/{$agenzia->id}/approva", ['note_interne' => 'cliente segnalato da Rossi']);

        $this->actingAs($agenzia->user)
            ->get('/account')
            ->assertOk()
            ->assertDontSee('cliente segnalato da Rossi');
    }

    public function test_chi_non_e_staff_non_puo_creare_unagenzia(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/gestione/agenzie/nuova')
            ->assertNotFound();
    }

    private function datiNuovaAgenzia(array $sovrascrivi = []): array
    {
        return array_merge([
            'name' => 'Marco Bianchi',
            'email' => 'marco@onoranzebianchi.it',
            'telefono' => '0212345678',
            'password' => 'password-lunga-abbastanza',
            'password_confirmation' => 'password-lunga-abbastanza',
            'ragione_sociale' => 'Onoranze Funebri Bianchi S.r.l.',
            'partita_iva' => '00743110157',
            'indirizzo' => 'Via Roma 12',
            'cap' => '20121',
            'citta' => 'Milano',
            'provincia' => 'mi',
        ], $sovrascrivi);
    }

    public function test_lo_staff_crea_unagenzia_gia_approvata(): void
    {
        $staff = $this->staff();

        $risposta = $this->actingAs($staff)->post('/gestione/agenzie', $this->datiNuovaAgenzia());

        $agenzia = Agenzia::firstOrFail();
        $risposta->assertRedirect(route('gestione.agenzie.show', $agenzia));

        $this->assertSame(StatoAgenzia::Approvata, $agenzia->stato);
        $this->assertSame($staff->id, $agenzia->stato_aggiornato_da);
        $this->assertTrue($agenzia->user->eAgenziaApprovata());
    }

    public function test_il_referente_creato_dallo_staff_puo_accedere_subito(): void
    {
        $this->actingAs($this->staff())->post('/gestione/agenzie', $this->datiNuovaAgenzia([
            'email' => 'referente@prova.it',
            'password' => 'una-password-lunga',
            'password_confirmation' => 'una-password-lunga',
        ]));

        $this->post('/esci');

        $this->post('/accedi', ['email' => 'referente@prova.it', 'password' => 'una-password-lunga'])
            ->assertRedirect(route('account'));
    }

    public function test_il_comando_promuove_a_staff(): void
    {
        $user = User::factory()->create(['email' => 'staff@memorai.test']);

        $this->artisan('commerce:staff staff@memorai.test')->assertSuccessful();

        $this->assertSame(RuoloUtente::Staff, $user->fresh()->ruolo);
    }

    public function test_il_comando_rifiuta_il_referente_di_una_agenzia(): void
    {
        $agenzia = $this->agenziaConReferente();

        $this->artisan("commerce:staff {$agenzia->user->email}")->assertFailed();

        $this->assertSame(RuoloUtente::Agenzia, $agenzia->user->fresh()->ruolo);
    }
}
