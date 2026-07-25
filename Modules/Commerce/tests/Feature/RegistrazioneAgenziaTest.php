<?php

namespace Modules\Commerce\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Commerce\Enums\RuoloUtente;
use Modules\Commerce\Enums\StatoAgenzia;
use Modules\Commerce\Models\Agenzia;
use Tests\TestCase;

class RegistrazioneAgenziaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Partita IVA con cifra di controllo valida, usata nei test.
     */
    private const PIVA = '00743110157';

    private function datiValidi(array $sovrascrivi = []): array
    {
        return array_merge([
            'name' => 'Marco Bianchi',
            'email' => 'marco@onoranzebianchi.it',
            'telefono' => '0212345678',
            'password' => 'password-lunga-abbastanza',
            'password_confirmation' => 'password-lunga-abbastanza',
            'ragione_sociale' => 'Onoranze Funebri Bianchi S.r.l.',
            'partita_iva' => self::PIVA,
            'indirizzo' => 'Via Roma 12',
            'cap' => '20121',
            'citta' => 'Milano',
            'provincia' => 'mi',
        ], $sovrascrivi);
    }

    public function test_la_pagina_di_registrazione_agenzia_si_apre(): void
    {
        $this->get('/registrati/agenzia')->assertOk();
    }

    public function test_una_agenzia_puo_registrarsi_e_resta_in_attesa(): void
    {
        $response = $this->post('/registrati/agenzia', $this->datiValidi());

        $response->assertRedirect(route('account'));
        $this->assertAuthenticated();

        $agenzia = Agenzia::firstOrFail();
        $this->assertSame(StatoAgenzia::InAttesa, $agenzia->stato);
        $this->assertSame('MI', $agenzia->provincia, 'la provincia va normalizzata in maiuscolo');

        $user = User::firstOrFail();
        $this->assertSame(RuoloUtente::Agenzia, $user->ruolo);
        $this->assertSame($agenzia->id, $user->agenzia_id);
        $this->assertFalse($user->eAgenziaApprovata());
    }

    public function test_la_partita_iva_non_valida_viene_rifiutata(): void
    {
        $this->post('/registrati/agenzia', $this->datiValidi(['partita_iva' => '12345678901']))
            ->assertSessionHasErrors('partita_iva');

        $this->assertDatabaseCount('agenzie', 0);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_la_partita_iva_non_puo_essere_duplicata(): void
    {
        $this->post('/registrati/agenzia', $this->datiValidi());
        $this->post('/esci');

        $this->post('/registrati/agenzia', $this->datiValidi(['email' => 'altra@esempio.it']))
            ->assertSessionHasErrors('partita_iva');

        $this->assertDatabaseCount('agenzie', 1);
    }

    public function test_la_registrazione_non_puo_assegnarsi_il_ruolo_staff(): void
    {
        $this->post('/registrati/agenzia', $this->datiValidi(['ruolo' => 'staff']));

        $this->assertSame(RuoloUtente::Agenzia, User::firstOrFail()->ruolo);
    }

    public function test_se_i_dati_agenzia_non_sono_validi_non_resta_nulla_a_meta(): void
    {
        // L'utente e l'agenzia nascono nella stessa transazione: se salta la
        // validazione non deve restare un utente orfano.
        $this->post('/registrati/agenzia', $this->datiValidi(['cap' => 'non-un-cap']))
            ->assertSessionHasErrors('cap');

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('agenzie', 0);
        $this->assertGuest();
    }
}
