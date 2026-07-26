<?php

namespace Modules\Memorial\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Commerce\Enums\RuoloUtente;
use Modules\Commerce\Models\Agenzia;
use Modules\Memorial\Models\Defunto;
use Modules\Memorial\Models\Necrologio;
use Tests\TestCase;

/**
 * Il ponte fra il Ricordino Designer e il necrologio: la preghiera scritta
 * nel ricordino può diventare l'annuncio del necrologio dello stesso
 * defunto. Il designer chiama questi endpoint da solo, a ogni bozza salvata
 * (Modules/PhotoPrint/resources/views/ricordino-designer.blade.php).
 */
class NecrologioPreghieraTest extends TestCase
{
    use RefreshDatabase;

    private function agenziaConReferente(
        string $ragioneSociale = 'Onoranze Funebri Bianchi S.r.l.',
        string $partitaIva = '00743110157',
    ): array {
        $staff = User::factory()->create();
        $staff->ruolo = RuoloUtente::Staff;
        $staff->save();

        $agenzia = Agenzia::create([
            'ragione_sociale' => $ragioneSociale,
            'partita_iva' => $partitaIva,
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

    public function test_dice_che_non_esiste_ancora_un_necrologio_per_il_defunto(): void
    {
        [$referente] = $this->agenziaConReferente();
        $defunto = Defunto::create(['nome' => 'Maria', 'cognome' => 'Verdi']);

        $risposta = $this->actingAs($referente)
            ->getJson("/admin/api/necrologio-pratica/{$defunto->id}")
            ->assertOk()
            ->json();

        $this->assertFalse($risposta['exists']);
    }

    public function test_dice_che_esiste_quando_questa_agenzia_ne_ha_gia_uno(): void
    {
        [$referente, $agenzia] = $this->agenziaConReferente();
        $defunto = Defunto::create(['nome' => 'Maria', 'cognome' => 'Verdi']);
        $n = Necrologio::create([
            'defunto_id' => $defunto->id, 'agenzia_id' => $agenzia->id,
            'percorso' => Necrologio::componiPercorso($defunto),
        ]);

        $risposta = $this->actingAs($referente)
            ->getJson("/admin/api/necrologio-pratica/{$defunto->id}")
            ->assertOk()
            ->json();

        $this->assertTrue($risposta['exists']);
        $this->assertSame($n->id, $risposta['id']);
    }

    public function test_il_necrologio_di_un_altra_agenzia_non_conta_come_esistente(): void
    {
        [$referente] = $this->agenziaConReferente('Onoranze Funebri Bianchi S.r.l.', '00743110157');
        [, $altra] = $this->agenziaConReferente('Casa Funeraria Aurora S.n.c.', '12485671007');

        $defunto = Defunto::create(['nome' => 'Maria', 'cognome' => 'Verdi']);
        Necrologio::create([
            'defunto_id' => $defunto->id, 'agenzia_id' => $altra->id,
            'percorso' => Necrologio::componiPercorso($defunto),
        ]);

        $risposta = $this->actingAs($referente)
            ->getJson("/admin/api/necrologio-pratica/{$defunto->id}")
            ->assertOk()
            ->json();

        $this->assertFalse($risposta['exists']);
    }

    public function test_salva_la_preghiera_come_annuncio_quando_e_vuoto(): void
    {
        [$referente, $agenzia] = $this->agenziaConReferente();
        $defunto = Defunto::create(['nome' => 'Maria', 'cognome' => 'Verdi']);
        $n = Necrologio::create([
            'defunto_id' => $defunto->id, 'agenzia_id' => $agenzia->id,
            'percorso' => Necrologio::componiPercorso($defunto),
        ]);

        $risposta = $this->actingAs($referente)->postJson('/admin/api/salva-preghiera', [
            'pratica_id' => $defunto->id,
            'prayer' => 'Riposa in pace, dolce mamma.',
        ])->assertOk()->json();

        $this->assertTrue($risposta['success']);
        $this->assertSame('Riposa in pace, dolce mamma.', $n->fresh()->testo);
    }

    public function test_non_sovrascrive_un_annuncio_gia_scritto(): void
    {
        [$referente, $agenzia] = $this->agenziaConReferente();
        $defunto = Defunto::create(['nome' => 'Maria', 'cognome' => 'Verdi']);
        $n = Necrologio::create([
            'defunto_id' => $defunto->id, 'agenzia_id' => $agenzia->id,
            'percorso' => Necrologio::componiPercorso($defunto),
            'testo' => 'Annuncio scritto a mano dall\'agenzia.',
        ]);

        $risposta = $this->actingAs($referente)->postJson('/admin/api/salva-preghiera', [
            'pratica_id' => $defunto->id,
            'prayer' => 'Testo scritto nel ricordino.',
        ])->assertOk()->json();

        $this->assertFalse($risposta['success']);
        $this->assertSame('Annuncio scritto a mano dall\'agenzia.', $n->fresh()->testo);
    }

    public function test_chi_non_ha_agenzia_non_rompe_il_designer(): void
    {
        $privato = User::factory()->create();
        $defunto = Defunto::create(['nome' => 'Maria', 'cognome' => 'Verdi']);

        $risposta = $this->actingAs($privato)->postJson('/admin/api/salva-preghiera', [
            'pratica_id' => $defunto->id,
            'prayer' => 'Una preghiera qualsiasi.',
        ])->assertOk()->json();

        $this->assertFalse($risposta['success']);
    }

    public function test_senza_necrologio_per_quel_defunto_non_fa_nulla(): void
    {
        [$referente] = $this->agenziaConReferente();
        $defunto = Defunto::create(['nome' => 'Maria', 'cognome' => 'Verdi']);

        $risposta = $this->actingAs($referente)->postJson('/admin/api/salva-preghiera', [
            'pratica_id' => $defunto->id,
            'prayer' => 'Una preghiera qualsiasi.',
        ])->assertOk()->json();

        $this->assertFalse($risposta['success']);
    }

    public function test_il_link_dal_designer_precompila_defunto_e_testo(): void
    {
        [$referente] = $this->agenziaConReferente();
        $defunto = Defunto::create(['nome' => 'Maria', 'cognome' => 'Verdi']);

        $this->actingAs($referente)
            ->get(route('necrologi.nuovo', ['defunto_id' => $defunto->id, 'testo' => 'Una preghiera per Maria']))
            ->assertOk()
            ->assertSee('Una preghiera per Maria')
            ->assertSee('selected', false);
    }
}
