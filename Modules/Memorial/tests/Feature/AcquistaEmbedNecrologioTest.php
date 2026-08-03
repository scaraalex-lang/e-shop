<?php

namespace Modules\Memorial\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Modules\Commerce\Database\Seeders\ServiziEditorSeeder;
use Modules\Commerce\Enums\RuoloUtente;
use Modules\Commerce\Models\Agenzia;
use Modules\Commerce\Models\MovimentoCredito;
use Modules\Memorial\Models\Defunto;
use Modules\Memorial\Models\Necrologio;
use Tests\TestCase;

/**
 * L'acquisto dell'embed ha due modalità scelte al momento del click: "a
 * termine" (scadenza scelta dall'agenzia, non oltre sei mesi, costo ridotto)
 * o "perpetuo" (scadenza tecnica a tre anni, costo pieno) — vedi
 * NecrologiController::acquistaEmbed() e Necrologio::abilitaEmbed().
 */
class AcquistaEmbedNecrologioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        (new ServiziEditorSeeder)->run();
    }

    private function agenziaConReferente(): array
    {
        $staff = User::factory()->create();
        $staff->ruolo = RuoloUtente::Staff;
        $staff->save();

        $agenzia = Agenzia::create([
            'ragione_sociale' => 'Onoranze Funebri Bianchi S.r.l.',
            'partita_iva' => '00743110157',
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

    private function necrologioPubblicato(Agenzia $agenzia): Necrologio
    {
        $defunto = Defunto::create(['nome' => 'Luigia', 'cognome' => 'Rossetti']);

        $n = Necrologio::create([
            'defunto_id' => $defunto->id,
            'agenzia_id' => $agenzia->id,
            'percorso' => Necrologio::componiPercorso($defunto),
            'trigesimo_at' => Carbon::now()->addDays(10)->setTime(15, 30),
        ]);

        $n->autorizzaPubblicazione('Giulia Ferrari', 'figlia');
        $n->pubblica();

        return $n->fresh();
    }

    private function accreditaCrediti(Agenzia $agenzia, int $quantita): void
    {
        MovimentoCredito::create([
            'agenzia_id' => $agenzia->id,
            'quantita' => $quantita,
            'causale' => 'Accredito di prova',
        ]);
    }

    public function test_acquisto_perpetuo_scala_il_costo_pieno_e_scade_a_tre_anni(): void
    {
        [$referente, $agenzia] = $this->agenziaConReferente();
        $this->accreditaCrediti($agenzia, 50);
        $n = $this->necrologioPubblicato($agenzia);

        $this->actingAs($referente)->post(route('necrologi.embed', $n), ['tipo' => 'perpetuo']);

        $n->refresh();
        $this->assertTrue($n->embed_abilitato);
        $this->assertSame('perpetuo', $n->embed_tipo);
        $this->assertTrue($n->embed_scaduto_il->isSameDay(Carbon::now()->addYears(Necrologio::EMBED_PERPETUO_ANNI)));
        $this->assertSame(25, $agenzia->fresh()->creditiSaldo());
        $this->assertSame(-25, MovimentoCredito::where('causale', 'like', 'Embed perpetuo%')->firstOrFail()->quantita);
        $this->assertTrue($n->embeddabile());
    }

    public function test_acquisto_a_termine_entro_sei_mesi_scala_il_costo_ridotto(): void
    {
        [$referente, $agenzia] = $this->agenziaConReferente();
        $this->accreditaCrediti($agenzia, 50);
        $n = $this->necrologioPubblicato($agenzia);
        $scadenza = Carbon::now()->addMonths(3)->format('Y-m-d');

        $this->actingAs($referente)->post(route('necrologi.embed', $n), [
            'tipo' => 'termine',
            'fino_al' => $scadenza,
        ]);

        $n->refresh();
        $this->assertTrue($n->embed_abilitato);
        $this->assertSame('termine', $n->embed_tipo);
        $this->assertTrue($n->embed_scaduto_il->isSameDay(Carbon::parse($scadenza)));
        $this->assertSame(35, $agenzia->fresh()->creditiSaldo());
    }

    public function test_termine_oltre_sei_mesi_diventa_perpetuo(): void
    {
        [$referente, $agenzia] = $this->agenziaConReferente();
        $this->accreditaCrediti($agenzia, 50);
        $n = $this->necrologioPubblicato($agenzia);

        $this->actingAs($referente)->post(route('necrologi.embed', $n), [
            'tipo' => 'termine',
            'fino_al' => Carbon::now()->addMonths(8)->format('Y-m-d'),
        ]);

        $n->refresh();
        $this->assertSame('perpetuo', $n->embed_tipo);
        $this->assertSame(25, $agenzia->fresh()->creditiSaldo());
        $this->assertTrue($n->embed_scaduto_il->isSameDay(Carbon::now()->addYears(Necrologio::EMBED_PERPETUO_ANNI)));
    }

    public function test_crediti_insufficienti_non_attiva_niente(): void
    {
        [$referente, $agenzia] = $this->agenziaConReferente();
        $this->accreditaCrediti($agenzia, 10);
        $n = $this->necrologioPubblicato($agenzia);

        $this->actingAs($referente)->post(route('necrologi.embed', $n), ['tipo' => 'perpetuo']);

        $n->refresh();
        $this->assertFalse($n->embed_abilitato);
        $this->assertSame(10, $agenzia->fresh()->creditiSaldo());
    }

    public function test_un_embed_a_termine_scaduto_si_puo_rinnovare(): void
    {
        [$referente, $agenzia] = $this->agenziaConReferente();
        $this->accreditaCrediti($agenzia, 50);
        $n = $this->necrologioPubblicato($agenzia);

        $n->abilitaEmbed(Carbon::now()->addDay(), 'termine');
        $this->assertTrue($n->fresh()->embeddabile());

        $this->travel(3)->days();
        $this->assertTrue($n->fresh()->embedScaduto());
        $this->assertFalse($n->fresh()->embeddabile());

        $this->actingAs($referente)->post(route('necrologi.embed', $n), ['tipo' => 'perpetuo']);

        $n->refresh();
        $this->assertSame('perpetuo', $n->embed_tipo);
        $this->assertTrue($n->embeddabile());
    }
}
