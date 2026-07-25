<?php

namespace Modules\PhotoPrint\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Commerce\Enums\RuoloUtente;
use Modules\Commerce\Models\Agenzia;
use ReflectionProperty;
use Tests\TestCase;

/**
 * Gli editor non sono più pubblici: entrano staff e agenzie approvate.
 */
class AccessoStudioTest extends TestCase
{
    use RefreshDatabase;

    private const PAGINE = ['/studio/foto', '/studio/ricordino'];

    private function staff(): User
    {
        $user = User::factory()->create();
        $user->ruolo = RuoloUtente::Staff;
        $user->save();

        return $user;
    }

    private function referenteAgenzia(bool $approvata): User
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

        $user = User::factory()->create();
        $user->ruolo = RuoloUtente::Agenzia;
        $user->agenzia()->associate($agenzia);
        $user->save();

        if ($approvata) {
            $agenzia->approva($this->staff());
        }

        return $user->fresh();
    }

    public function test_senza_login_gli_editor_mandano_alla_pagina_di_accesso(): void
    {
        foreach (self::PAGINE as $pagina) {
            $this->get($pagina)->assertRedirect(route('login'));
        }
    }

    public function test_senza_login_gli_endpoint_rispondono_401_e_non_una_pagina(): void
    {
        // Le chiamate degli editor si annunciano come XHR: devono ricevere un
        // 401 da gestire in JS, non il redirect HTML della pagina di accesso.
        $this->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson('/admin/api/santi')
            ->assertUnauthorized();
    }

    public function test_un_privato_non_entra_negli_editor(): void
    {
        $privato = User::factory()->create();

        foreach (self::PAGINE as $pagina) {
            $this->actingAs($privato)->get($pagina)->assertForbidden();
        }

        $this->actingAs($privato)->getJson('/admin/api/santi')->assertForbidden();
    }

    public function test_una_agenzia_in_attesa_non_entra_negli_editor(): void
    {
        $referente = $this->referenteAgenzia(approvata: false);

        $this->actingAs($referente)->get('/studio/ricordino')->assertForbidden();
        $this->actingAs($referente)->getJson('/admin/api/santi')->assertForbidden();
    }

    public function test_una_agenzia_approvata_entra_negli_editor(): void
    {
        $referente = $this->referenteAgenzia(approvata: true);

        $this->actingAs($referente)->getJson('/admin/api/santi')->assertOk();
    }

    public function test_lo_staff_entra_negli_editor(): void
    {
        $staff = $this->staff();

        $this->actingAs($staff)->getJson('/admin/api/santi')->assertOk();
    }

    public function test_gli_endpoint_non_sono_piu_esclusi_dal_csrf(): void
    {
        // In ambiente di test il CSRF è disattivato dal framework, quindi non
        // si può provocare un 419: si verifica direttamente che l'esclusione
        // della Fase 1 sia stata tolta.
        $escluse = (new ReflectionProperty(PreventRequestForgery::class, 'neverVerify'))->getValue();

        $this->assertNotContains('admin/api/*', $escluse);
    }
}
