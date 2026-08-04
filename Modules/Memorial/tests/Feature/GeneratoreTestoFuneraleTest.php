<?php

namespace Modules\Memorial\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Modules\Commerce\Database\Seeders\ServiziEditorSeeder;
use Modules\Commerce\Enums\MetodoPagamento;
use Modules\Commerce\Enums\RuoloUtente;
use Modules\Commerce\Models\Agenzia;
use Modules\Commerce\Models\Ordine;
use Modules\Commerce\Models\ServizioEditor;
use Modules\Memorial\Models\Defunto;
use Modules\Memorial\Models\Manifesto;
use Modules\Memorial\Servizi\GeneratoreTestoFunerale;
use Tests\TestCase;

/**
 * Il testo "Info funerale" nel formato editoriale a 4 righe: "oggi"/"domani"/
 * la data estesa sono calcolati in PHP (mai lasciati decidere all'AI — sono
 * un fatto, non una scelta di stile), il resto ripiega su un template
 * deterministico se manca la chiave OpenAI o la chiamata fallisce, così il
 * pulsante nel designer funziona comunque.
 */
class GeneratoreTestoFuneraleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        (new ServiziEditorSeeder)->run();
        config(['services.openai.key' => null]);
    }

    private function defuntoConCerimonia(array $sovrascrivi = []): Defunto
    {
        return Defunto::create(array_merge([
            'nome' => 'Luca', 'cognome' => 'Rossi',
            // Abbastanza lontana da non cadere mai su "oggi"/"domani" per
            // caso, qualunque sia la data reale in cui gira il test.
            'cerimonia_at' => Carbon::now()->addWeeks(2)->setTime(15, 0),
            'indirizzo_cerimonia' => 'Via Garibaldi nr. 3',
            'chiesa' => 'Immacolata Concezione',
            'cimitero' => 'Cimitero di Boscoreale, Via Cimitero 5',
            'citta' => 'Boscoreale',
            'provincia' => 'na',
        ], $sovrascrivi));
    }

    // ---- template di riserva (senza AI) --------------------------------------

    public function test_senza_chiave_openai_usa_il_template_deterministico(): void
    {
        $defunto = $this->defuntoConCerimonia();

        $testo = app(GeneratoreTestoFunerale::class)->generaPerDefunto($defunto);
        $righe = explode("\n", $testo);

        $this->assertCount(4, $righe);
        $this->assertStringContainsString('alle ore 15:00', $righe[0]);
        $this->assertSame('Partenza da Via Garibaldi nr. 3, Boscoreale, NA', $righe[1]);
        $this->assertSame('Immacolata Concezione, Boscoreale, NA', $righe[2]);
        $this->assertSame('Cimitero, Boscoreale, NA', $righe[3]);
    }

    public function test_oggi_e_domani_si_calcolano_dalla_data_non_dalla_formattazione_generica(): void
    {
        $oggi = $this->defuntoConCerimonia(['cerimonia_at' => Carbon::now()->setTime(15, 0)]);
        $domani = $this->defuntoConCerimonia(['cerimonia_at' => Carbon::now()->addDay()->setTime(15, 0)]);
        $lontano = $this->defuntoConCerimonia(['cerimonia_at' => Carbon::now()->addWeek()->setTime(15, 0)]);

        $servizio = app(GeneratoreTestoFunerale::class);

        $this->assertStringStartsWith('I funerali si svolgeranno oggi alle ore', $servizio->generaPerDefunto($oggi));
        $this->assertStringStartsWith('I funerali si svolgeranno domani alle ore', $servizio->generaPerDefunto($domani));
        $this->assertStringStartsWith('I funerali si svolgeranno il '.Carbon::now()->addWeek()->day, $servizio->generaPerDefunto($lontano));
    }

    public function test_senza_chiesa_ne_citta_il_template_resta_a_4_righe_senza_virgole_vuote(): void
    {
        $defunto = $this->defuntoConCerimonia(['chiesa' => null, 'citta' => null, 'provincia' => null]);

        $testo = app(GeneratoreTestoFunerale::class)->generaPerDefunto($defunto);
        $righe = explode("\n", $testo);

        $this->assertCount(4, $righe);
        $this->assertSame('Parrocchia', $righe[2]);
        $this->assertSame('Cimitero', $righe[3]);
        $this->assertStringNotContainsString(',,', $testo);
    }

    // ---- con OpenAI configurata -----------------------------------------------

    public function test_con_chiave_configurata_usa_la_risposta_openai(): void
    {
        config(['services.openai.key' => 'sk-test']);
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [['message' => ['content' => "I funerali si svolgeranno domani alle ore 15:00\nPartenza da Via Garibaldi nr. 3, Boscoreale, NA\nImmacolata Concezione, Boscoreale, NA\nCimitero, Boscoreale, NA"]]],
            ]),
        ]);

        $defunto = $this->defuntoConCerimonia(['cerimonia_at' => Carbon::tomorrow()->setTime(15, 0)]);
        $testo = app(GeneratoreTestoFunerale::class)->generaPerDefunto($defunto);

        $this->assertStringContainsString('domani alle ore 15:00', $testo);
        Http::assertSent(function ($request) {
            $corpo = json_decode($request->body(), true);
            $datiInviati = json_decode($corpo['messages'][1]['content'], true);

            // Solo campi già puliti: mai un indirizzo grezzo non passato da Defunto,
            // e la provincia arriva sempre maiuscola indipendentemente da come
            // è stata salvata sul defunto.
            return $datiInviati['citta'] === 'Boscoreale'
                && $datiInviati['provincia'] === 'NA'
                && $datiInviati['quando'] === 'domani';
        });
    }

    public function test_se_openai_fallisce_ripiega_sul_template(): void
    {
        config(['services.openai.key' => 'sk-test']);
        Http::fake(['api.openai.com/*' => Http::response('errore', 500)]);

        $testo = app(GeneratoreTestoFunerale::class)->generaPerDefunto($this->defuntoConCerimonia());

        $this->assertSame('Cimitero, Boscoreale, NA', explode("\n", $testo)[3]);
    }

    // ---- endpoint nel designer -------------------------------------------------

    public function test_il_pulsante_nel_designer_chiama_l_endpoint_e_riceve_il_testo(): void
    {
        $staff = User::factory()->create(['ruolo' => RuoloUtente::Staff]);

        $agenzia = Agenzia::create([
            'ragione_sociale' => 'Onoranze Funebri Bianchi S.r.l.', 'partita_iva' => '00743110157',
            'indirizzo' => 'Via Roma 12', 'cap' => '20121',
            'citta' => 'Milano', 'provincia' => 'MI', 'telefono' => '0212345678',
        ]);
        $agenzia->approva($staff);

        $referente = User::factory()->create(['ruolo' => RuoloUtente::Agenzia]);
        $referente->agenzia()->associate($agenzia);
        $referente->save();

        $defunto = $this->defuntoConCerimonia();
        $ordine = Ordine::create([
            'numero' => Ordine::prossimoNumero(),
            'user_id' => $referente->id, 'agenzia_id' => $agenzia->id,
            'metodo_pagamento' => MetodoPagamento::Fattura->value,
            'totale_pieno' => 0, 'totale_merce' => 0, 'totale' => 0,
            'consegna_nome' => 'Prova', 'consegna_telefono' => '000',
            'consegna_indirizzo' => 'Via Prova 1', 'consegna_cap' => '20121',
            'consegna_citta' => 'Milano', 'consegna_provincia' => 'MI',
            'richiede_lavorazione' => true,
        ]);
        $ordine->forceFill(['defunto_id' => $defunto->id])->save();
        $defunto->forceFill(['ordine_id' => $ordine->id])->save();
        $servizio = ServizioEditor::where('codice', 'manifesti')->first();
        $ordine->servizi()->create(['servizio_editor_id' => $servizio->id, 'costo_crediti' => $servizio->costo_crediti]);
        DB::table('foto_pratica')->insert([
            'ordine_id' => $ordine->id, 'path' => 'photoprint/pratica/principale.jpg',
            'tipo' => 'originale', 'is_principale' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $manifesto = Manifesto::create(['defunto_id' => $defunto->id, 'etichetta' => 'Manifesto funerale', 'formato' => 'a3l', 'principale' => true]);

        $risposta = $this->actingAs($referente)
            ->postJson("/admin/api/manifesti/{$manifesto->id}/testo-funerale");

        $risposta->assertOk();
        $this->assertStringContainsString('Cimitero, Boscoreale, NA', $risposta->json('testo'));
    }

    public function test_il_manifesto_di_un_altro_non_si_tocca(): void
    {
        $referente = User::factory()->create(['ruolo' => RuoloUtente::Agenzia]);
        $altroDefunto = $this->defuntoConCerimonia(['nome' => 'Altro']);
        $manifesto = Manifesto::create(['defunto_id' => $altroDefunto->id, 'etichetta' => 'Manifesto', 'formato' => 'a3l', 'principale' => true]);

        $this->actingAs($referente)
            ->postJson("/admin/api/manifesti/{$manifesto->id}/testo-funerale")
            ->assertNotFound();
    }
}
