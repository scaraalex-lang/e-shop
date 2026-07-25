<?php

namespace Modules\Commerce\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Commerce\Enums\RuoloUtente;
use Modules\Commerce\Models\Agenzia;
use Modules\Commerce\Models\ScaglionePrezzo;
use Modules\Commerce\Prezzi\Listino;
use Modules\Commerce\Tests\Concerns\CreaSoggetti;
use Tests\TestCase;

/**
 * Il motore dei prezzi. Qui uno sbaglio si traduce in soldi sbagliati, quindi
 * i conti sono verificati al centesimo.
 */
class ListinoTest extends TestCase
{
    use CreaSoggetti, RefreshDatabase;

    private Listino $listino;

    protected function setUp(): void
    {
        parent::setUp();
        $this->listino = app(Listino::class);
    }

    private function prodotto(array $attributi = []): Product
    {
        $categoria = Category::create([
            'name' => 'Rosari e corone',
            'slug' => 'rosari-e-corone-'.uniqid(),
        ]);

        return Product::create(array_merge([
            'category_id' => $categoria->id,
            'sku' => 'TEST-'.uniqid(),
            'slug' => 'test-'.uniqid(),
            'name' => 'Rosario di prova',
            'price' => 2600,          // 26,00 €
            'is_active' => true,
        ], $attributi));
    }

    public function test_un_privato_paga_il_prezzo_pubblico_moltiplicato_per_la_quantita(): void
    {
        $prodotto = $this->prodotto();

        $prezzo = $this->listino->perRiga($prodotto, 3, null);

        $this->assertSame(7800, $prezzo->pieno);
        $this->assertSame(7800, $prezzo->scontato);
        $this->assertFalse($prezzo->haSconto());
    }

    public function test_il_kit_comprende_i_pezzi_inclusi_e_paga_solo_gli_extra(): void
    {
        $kit = $this->prodotto([
            'price' => 9900,           // 99,00 € fino a 50 ricordini
            'is_kit' => true,
            'included_units' => 50,
            'extra_unit_price' => 120, // 1,20 € a pezzo oltre i 50
        ]);

        $this->assertSame(9900, $this->listino->perRiga($kit, 50, null)->pieno, 'a 50 pezzi si paga il kit');
        $this->assertSame(9900, $this->listino->perRiga($kit, 30, null)->pieno, 'sotto i 50 il prezzo non scende');
        $this->assertSame(11_100, $this->listino->perRiga($kit, 60, null)->pieno, '99,00 + 10 x 1,20');
    }

    public function test_gli_sconti_a_scaglioni_valgono_solo_per_le_agenzie_approvate(): void
    {
        $prodotto = $this->prodotto();
        ScaglionePrezzo::create([
            'product_id' => $prodotto->id, 'quantita_minima' => 25, 'sconto_percentuale' => 10,
        ]);

        // 25 x 26,00 = 650,00 -> -10% = 585,00
        $agenzia = $this->referenteAgenzia();
        $conSconto = $this->listino->perRiga($prodotto, 25, $agenzia);
        $this->assertSame(65_000, $conSconto->pieno);
        $this->assertSame(58_500, $conSconto->scontato);
        $this->assertSame(6_500, $conSconto->risparmio());

        // stessa quantità, ma senza account o da privato: prezzo pieno
        foreach ([null, User::factory()->create()] as $chiunque) {
            $senzaSconto = $this->listino->perRiga($prodotto, 25, $chiunque);
            $this->assertSame(65_000, $senzaSconto->scontato);
            $this->assertFalse($senzaSconto->haSconto());
        }
    }

    public function test_una_agenzia_non_ancora_approvata_paga_il_pubblico(): void
    {
        $prodotto = $this->prodotto();
        ScaglionePrezzo::create([
            'product_id' => $prodotto->id, 'quantita_minima' => 25, 'sconto_percentuale' => 10,
        ]);

        $referente = $this->referenteAgenzia();
        $referente->agenzia->sospendi(User::factory()->create());

        $this->assertFalse($this->listino->perRiga($prodotto, 25, $referente->fresh())->haSconto());
    }

    public function test_sotto_la_soglia_lo_scaglione_non_scatta(): void
    {
        $prodotto = $this->prodotto();
        ScaglionePrezzo::create([
            'product_id' => $prodotto->id, 'quantita_minima' => 25, 'sconto_percentuale' => 10,
        ]);

        $this->assertFalse($this->listino->perRiga($prodotto, 24, $this->referenteAgenzia())->haSconto());
    }

    public function test_fra_piu_scaglioni_raggiunti_vince_il_piu_conveniente(): void
    {
        $prodotto = $this->prodotto();
        foreach ([25 => 10, 50 => 15, 100 => 20] as $quantita => $sconto) {
            ScaglionePrezzo::create([
                'product_id' => $prodotto->id, 'quantita_minima' => $quantita, 'sconto_percentuale' => $sconto,
            ]);
        }

        $prezzo = $this->listino->perRiga($prodotto, 120, $this->referenteAgenzia());

        $this->assertSame(20.0, (float) $prezzo->scaglione->sconto_percentuale);
        // 120 x 26,00 = 3.120,00 -> -20% = 2.496,00
        $this->assertSame(249_600, $prezzo->scontato);
    }

    public function test_le_percentuali_con_la_virgola_restano_esatte_al_centesimo(): void
    {
        // 12,5% su 33,33 € non deve passare da un float: 3333 -> 2916 (arrotondato per difetto)
        $prodotto = $this->prodotto(['price' => 3333]);
        ScaglionePrezzo::create([
            'product_id' => $prodotto->id, 'quantita_minima' => 1, 'sconto_percentuale' => 12.5,
        ]);

        $prezzo = $this->listino->perRiga($prodotto, 1, $this->referenteAgenzia());

        $this->assertSame(2916, $prezzo->scontato);
        $this->assertIsInt($prezzo->scontato);
    }

    public function test_lo_sconto_si_applica_anche_al_kit_dopo_i_pezzi_extra(): void
    {
        $kit = $this->prodotto([
            'price' => 9900, 'is_kit' => true, 'included_units' => 50, 'extra_unit_price' => 120,
        ]);
        ScaglionePrezzo::create([
            'product_id' => $kit->id, 'quantita_minima' => 100, 'sconto_percentuale' => 8,
        ]);

        // 99,00 + 50 x 1,20 = 159,00 -> -8% = 146,28
        $prezzo = $this->listino->perRiga($kit, 100, $this->referenteAgenzia());

        $this->assertSame(15_900, $prezzo->pieno);
        $this->assertSame(14_628, $prezzo->scontato);
    }
}
