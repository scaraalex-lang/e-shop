<?php

namespace Modules\Commerce\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Commerce\Contabilita\EstrattoConto;
use Modules\Commerce\Models\Carrello;
use Modules\Commerce\Models\MovimentoCredito;
use Modules\Commerce\Models\Ordine;
use Modules\Commerce\Tests\Concerns\CreaSoggetti;
use Tests\TestCase;

/**
 * L'estratto conto: un evento per riga (fattura emessa, pagamento ricevuto,
 * crediti usati), non un ordine con lo stato attuale — vedi EstrattoConto.
 * Qui si testa il servizio direttamente, manipolando le date con
 * forceFill: passare dal checkout vero (come ContabilitaAgenziaTest e
 * CreditiCatalogoTest) mette sempre tutto nel mese corrente, non basta a
 * verificare che un ordine "a cavallo" fra due mesi si spezzi in due righe.
 */
class EstrattoContoTest extends TestCase
{
    use CreaSoggetti, RefreshDatabase;

    private function prodotto(): Product
    {
        $categoria = Category::firstOrCreate(['slug' => 'test-estratto-conto'], ['name' => 'Test estratto conto']);

        return Product::create([
            'category_id' => $categoria->id,
            'sku' => 'ESTR-'.uniqid(),
            'slug' => 'estr-'.uniqid(),
            'name' => 'Articolo di prova',
            'price' => 4900,
            'is_active' => true,
        ]);
    }

    private function ordineAFattura(User $referente, int $totale = 18_000): Ordine
    {
        return Ordine::create([
            'numero' => 'MEM-2026-'.random_int(1000, 9999),
            'user_id' => $referente->id,
            'agenzia_id' => $referente->agenzia->id,
            'metodo_pagamento' => 'fattura',
            'totale_pieno' => $totale,
            'totale_merce' => $totale,
            'spedizione' => 0,
            'totale' => $totale,
            'consegna_nome' => 'Prova',
            'consegna_telefono' => '000',
            'consegna_indirizzo' => 'Via Prova 1',
            'consegna_cap' => '20100',
            'consegna_citta' => 'Milano',
            'consegna_provincia' => 'MI',
        ]);
    }

    public function test_un_ordine_fatturato_in_un_mese_e_saldato_nel_successivo_genera_due_righe_separate(): void
    {
        $referente = $this->referenteAgenzia();
        $ordine = $this->ordineAFattura($referente);

        $ordine->forceFill(['fattura_numero' => 'FT-0001', 'fattura_emessa_at' => Carbon::parse('2026-07-15')])->save();
        $ordine->forceFill(['stato_pagamento' => 'pagato', 'pagato_at' => Carbon::parse('2026-08-10'), 'riferimento_pagamento' => 'Bonifico'])->save();

        $estratto = new EstrattoConto;

        $luglio = $estratto->perPeriodo($referente->agenzia, Carbon::parse('2026-07-01')->startOfMonth(), Carbon::parse('2026-07-31')->endOfMonth());
        $agosto = $estratto->perPeriodo($referente->agenzia, Carbon::parse('2026-08-01')->startOfMonth(), Carbon::parse('2026-08-31')->endOfMonth());

        $this->assertCount(1, $luglio);
        $this->assertSame('fattura_emessa', $luglio->first()->tipo);

        $this->assertCount(1, $agosto);
        $this->assertSame('pagamento', $agosto->first()->tipo);
        $this->assertSame('Bonifico', $agosto->first()->riferimento);
    }

    public function test_un_ordine_coperto_interamente_dai_crediti_genera_solo_levento_crediti_non_un_pagamento_vuoto(): void
    {
        $referente = $this->referenteAgenzia();
        $ordine = $this->ordineAFattura($referente, totale: 10_000);
        $ordine->forceFill(['crediti_usati' => 100, 'stato_pagamento' => 'pagato', 'pagato_at' => Carbon::parse('2026-08-05')])->save();

        MovimentoCredito::create([
            'agenzia_id' => $referente->agenzia->id,
            'ordine_id' => $ordine->id,
            'quantita' => -100,
            'causale' => 'Ordine: pagamento in crediti',
        ]);

        $eventi = (new EstrattoConto)->perPeriodo($referente->agenzia, Carbon::parse('2026-08-01')->startOfMonth(), Carbon::parse('2026-08-31')->endOfMonth());

        $this->assertCount(1, $eventi, 'un ordine a 0€ di valoreInDenaro() non deve generare un evento "pagamento" vuoto');
        $this->assertSame('crediti_usati', $eventi->first()->tipo);
        $this->assertSame(100, $eventi->first()->importoCrediti);
    }

    public function test_un_ordine_misto_genera_sia_levento_crediti_sia_levento_pagamento(): void
    {
        $referente = $this->referenteAgenzia();
        $ordine = $this->ordineAFattura($referente, totale: 25_000);
        $ordine->forceFill(['crediti_usati' => 100, 'stato_pagamento' => 'pagato', 'pagato_at' => Carbon::parse('2026-08-05'), 'riferimento_pagamento' => 'SIM-TEST'])->save();

        MovimentoCredito::create([
            'agenzia_id' => $referente->agenzia->id,
            'ordine_id' => $ordine->id,
            'quantita' => -100,
            'causale' => 'Pagamento in crediti',
        ]);

        $eventi = (new EstrattoConto)->perPeriodo($referente->agenzia, Carbon::parse('2026-08-01')->startOfMonth(), Carbon::parse('2026-08-31')->endOfMonth());

        $this->assertCount(2, $eventi);
        $tipi = $eventi->pluck('tipo')->sort()->values()->all();
        $this->assertSame(['crediti_usati', 'pagamento'], $tipi);

        $pagamento = $eventi->firstWhere('tipo', 'pagamento');
        $this->assertSame(15_000, $pagamento->importoDenaro); // 250€ - 100 crediti = 150€
    }

    public function test_il_periodo_di_default_e_il_mese_corrente(): void
    {
        [$inizio, $fine] = EstrattoConto::periodo(request());

        $this->assertTrue($inizio->isSameMonth(now()));
        $this->assertTrue($inizio->isStartOfDay());
        $this->assertTrue($fine->isSameDay(now()->endOfMonth()));
    }

    public function test_il_periodo_si_legge_dal_querystring(): void
    {
        $richiesta = \Illuminate\Http\Request::create('/', 'GET', ['anno' => 2025, 'mese' => 3]);

        [$inizio, $fine] = EstrattoConto::periodo($richiesta);

        $this->assertSame('2025-03-01', $inizio->toDateString());
        $this->assertSame('2025-03-31', $fine->toDateString());
    }

    public function test_lestratto_conto_dellagenzia_si_puo_filtrare_per_mese_dallurl(): void
    {
        $referente = $this->referenteAgenzia();
        $ordine = $this->ordineAFattura($referente);
        $ordine->forceFill(['fattura_numero' => 'FT-LUGLIO', 'fattura_emessa_at' => Carbon::parse('2026-07-20')])->save();

        // Il mese corrente (default): non lo vede.
        $this->actingAs($referente)
            ->get('/account/fatture')
            ->assertOk()
            ->assertDontSee('FT-LUGLIO');

        // Luglio, dall'URL: lo vede.
        $this->actingAs($referente)
            ->get('/account/fatture?anno=2026&mese=7')
            ->assertOk()
            ->assertSee('FT-LUGLIO');
    }
}
