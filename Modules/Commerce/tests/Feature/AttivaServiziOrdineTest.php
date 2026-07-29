<?php

namespace Modules\Commerce\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Commerce\Database\Seeders\ServiziEditorSeeder;
use Modules\Commerce\Enums\Occasione;
use Modules\Commerce\Enums\StatoOrdine;
use Modules\Commerce\Models\MovimentoCredito;
use Modules\Commerce\Models\Ordine;
use Modules\Commerce\Models\ServizioEditor;
use Modules\Commerce\Tests\Concerns\CreaSoggetti;
use Tests\TestCase;

/**
 * Attivare uno o più servizi editor (ricordini/manifesti/necrologi) su un
 * ordine nuovo, pagati in crediti — vedi AttivaServiziOrdine. Occasione è
 * solo un'etichetta: non determina né prezzo né quali servizi si possono
 * scegliere (sostituisce il vecchio flusso a carrello/SKU).
 */
class AttivaServiziOrdineTest extends TestCase
{
    use CreaSoggetti, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        (new ServiziEditorSeeder)->run();
    }

    private function accreditaCrediti(User $referente, int $quantita): void
    {
        MovimentoCredito::create([
            'agenzia_id' => $referente->agenzia_id,
            'quantita' => $quantita,
            'causale' => 'Accredito di prova',
        ]);
    }

    public function test_attiva_un_servizio_e_scala_i_crediti(): void
    {
        $referente = $this->referenteAgenzia();
        $this->accreditaCrediti($referente, 50);

        $this->actingAs($referente)->post('/account/ordini/servizio', [
            'occasione' => 'trigesimo',
            'servizi' => ['ricordini'],
        ]);

        $ordine = Ordine::firstOrFail();
        $this->assertSame(Occasione::Trigesimo, $ordine->occasione);
        $this->assertTrue($ordine->richiede_lavorazione);
        $this->assertSame(StatoOrdine::InLavorazione, $ordine->stato);
        $this->assertCount(1, $ordine->servizi);
        $this->assertSame('ricordini', $ordine->servizi->first()->servizioEditor->codice);
        $this->assertSame(15, $ordine->servizi->first()->costo_crediti);

        $movimento = MovimentoCredito::where('ordine_id', $ordine->id)->firstOrFail();
        $this->assertSame(-15, $movimento->quantita);
        $this->assertSame(35, $referente->agenzia->fresh()->creditiSaldo());
    }

    public function test_attiva_piu_servizi_insieme_e_ne_somma_il_costo(): void
    {
        $referente = $this->referenteAgenzia();
        $this->accreditaCrediti($referente, 50);

        $this->actingAs($referente)->post('/account/ordini/servizio', [
            'occasione' => 'funerale',
            'servizi' => ['ricordini', 'manifesti'],
        ]);

        $ordine = Ordine::firstOrFail();
        $this->assertCount(2, $ordine->servizi);

        // Un solo movimento per l'intero costo, non uno a servizio.
        $this->assertSame(1, MovimentoCredito::where('ordine_id', $ordine->id)->count());
        $movimento = MovimentoCredito::where('ordine_id', $ordine->id)->firstOrFail();
        $this->assertSame(-35, $movimento->quantita); // 15 (ricordini) + 20 (manifesti)
        $this->assertSame(15, $referente->agenzia->fresh()->creditiSaldo());
    }

    public function test_l_occasione_non_genera_nessuna_riga_ordine_ne_e_legata_al_prezzo(): void
    {
        $referente = $this->referenteAgenzia();
        $this->accreditaCrediti($referente, 50);

        $this->actingAs($referente)->post('/account/ordini/servizio', [
            'occasione' => 'anniversario',
            'numero_anniversario' => 2,
            'servizi' => ['necrologi'],
        ]);

        $ordine = Ordine::firstOrFail();
        $this->assertSame(Occasione::Anniversario, $ordine->occasione);
        $this->assertSame(2, $ordine->numero_anniversario);
        $this->assertCount(0, $ordine->righe); // nessun prodotto Catalog coinvolto
        $this->assertSame(0, $ordine->totale);
    }

    public function test_crediti_insufficienti_non_creano_l_ordine(): void
    {
        $referente = $this->referenteAgenzia();
        $this->accreditaCrediti($referente, 5); // i ricordini ne costano 15

        $this->actingAs($referente)->post('/account/ordini/servizio', [
            'occasione' => 'trigesimo',
            'servizi' => ['ricordini'],
        ]);

        $this->assertDatabaseCount('ordini', 0);
        $this->assertSame(5, $referente->agenzia->fresh()->creditiSaldo());
    }

    public function test_un_servizio_disattivato_non_e_accettato_dalla_validazione(): void
    {
        $referente = $this->referenteAgenzia();
        $this->accreditaCrediti($referente, 50);
        ServizioEditor::where('codice', 'manifesti')->update(['attivo' => false]);

        $this->actingAs($referente)->post('/account/ordini/servizio', [
            'occasione' => 'trigesimo',
            'servizi' => ['manifesti'],
        ])->assertSessionHasErrors('servizi.0');

        $this->assertDatabaseCount('ordini', 0);
    }

    public function test_senza_nessun_servizio_selezionato_e_rifiutato(): void
    {
        $referente = $this->referenteAgenzia();

        $this->actingAs($referente)->post('/account/ordini/servizio', [
            'occasione' => 'trigesimo',
            'servizi' => [],
        ])->assertSessionHasErrors('servizi');
    }

    public function test_un_privato_non_puo_attivare_servizi(): void
    {
        $privato = User::factory()->create();

        $this->actingAs($privato)->post('/account/ordini/servizio', [
            'occasione' => 'trigesimo',
            'servizi' => ['ricordini'],
        ])->assertForbidden();

        $this->assertDatabaseCount('ordini', 0);
    }
}
