<?php

namespace Modules\Catalog\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Commerce\Tests\Concerns\CreaSoggetti;
use Tests\TestCase;

class GestioneProdottiTest extends TestCase
{
    use CreaSoggetti, RefreshDatabase;

    private function categoria(): Category
    {
        return Category::create(['name' => 'Rosari e corone', 'slug' => 'rosari-e-corone-'.uniqid()]);
    }

    /** Un prodotto creato direttamente sul modello (prezzo già in centesimi), per i test che partono da uno esistente. */
    private function prodotto(array $attributi = []): Product
    {
        return Product::create(array_merge([
            'category_id' => $this->categoria()->id,
            'sku' => 'TEST-'.uniqid(),
            'slug' => 'test-'.uniqid(),
            'name' => 'Rosario di prova',
            'price' => 2600,
            'stock' => 10,
            'is_active' => true,
        ], $attributi));
    }

    /** Il payload che il form manda via HTTP: il prezzo arriva in euro, non in centesimi. */
    private function datiForm(Product|array $categoriaOSovrascrivi = [], array $sovrascrivi = []): array
    {
        if ($categoriaOSovrascrivi instanceof Product) {
            $p = $categoriaOSovrascrivi;
            $base = ['category_id' => $p->category_id, 'sku' => $p->sku, 'slug' => $p->slug, 'name' => $p->name];
        } else {
            $base = [
                'category_id' => $this->categoria()->id,
                'sku' => 'TEST-'.uniqid(),
                'slug' => 'test-'.uniqid(),
                'name' => 'Rosario di prova',
            ];
            $sovrascrivi = $categoriaOSovrascrivi;
        }

        return array_merge($base, [
            'price' => '26,00',
            'stock' => 10,
            'is_active' => '1',
        ], $sovrascrivi);
    }

    public function test_chi_non_e_staff_non_vede_nemmeno_che_gestione_esiste(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/gestione/prodotti')
            ->assertNotFound();
    }

    public function test_lo_staff_vede_lelenco_dei_prodotti(): void
    {
        $this->prodotto(['name' => 'Corona metallo oro']);

        $this->actingAs($this->staff())
            ->get('/gestione/prodotti')
            ->assertOk()
            ->assertSee('Corona metallo oro');
    }

    public function test_lo_staff_crea_un_prodotto_con_prezzo_in_euro_convertito_in_centesimi(): void
    {
        $dati = $this->datiForm(['price' => '26,50', 'compare_at_price' => '32,00']);

        $risposta = $this->actingAs($this->staff())->post('/gestione/prodotti', $dati);

        $prodotto = Product::firstOrFail();
        $risposta->assertRedirect(route('gestione.prodotti.edit', $prodotto));

        $this->assertSame(2650, $prodotto->price);
        $this->assertSame(3200, $prodotto->compare_at_price);
        $this->assertSame(10, $prodotto->stock);
        $this->assertTrue($prodotto->is_active);
    }

    public function test_lo_sku_duplicato_viene_rifiutato(): void
    {
        $this->prodotto(['sku' => 'TRG-DUP']);

        $this->actingAs($this->staff())
            ->post('/gestione/prodotti', $this->datiForm(['sku' => 'TRG-DUP']))
            ->assertSessionHasErrors('sku');

        $this->assertSame(1, Product::count());
    }

    public function test_le_due_foto_si_caricano_su_due_righe_distinte(): void
    {
        Storage::fake('public');
        $prodotto = $this->prodotto();

        $this->actingAs($this->staff())->put(route('gestione.prodotti.update', $prodotto), array_merge(
            $this->datiForm($prodotto),
            [
                'foto_catalogo' => UploadedFile::fake()->image('catalogo.jpg', 800, 800),
                'foto_zoom' => UploadedFile::fake()->image('zoom.jpg', 1600, 1600),
            ],
        ));

        $prodotto->refresh()->load('images');
        $this->assertCount(2, $prodotto->images);

        $catalogo = $prodotto->images->firstWhere('is_primary', true);
        $zoom = $prodotto->images->firstWhere('is_primary', false);
        $this->assertNotNull($catalogo);
        $this->assertNotNull($zoom);
        Storage::disk('public')->assertExists($catalogo->path);
        Storage::disk('public')->assertExists($zoom->path);
    }

    public function test_un_file_che_non_e_unimmagine_viene_rifiutato(): void
    {
        Storage::fake('public');
        $prodotto = $this->prodotto();

        $this->actingAs($this->staff())->put(route('gestione.prodotti.update', $prodotto), array_merge(
            $this->datiForm($prodotto),
            ['foto_catalogo' => UploadedFile::fake()->create('preventivo.pdf', 100, 'application/pdf')],
        ))->assertSessionHasErrors('foto_catalogo');

        $this->assertCount(0, $prodotto->fresh()->images);
    }

    public function test_disattivare_un_prodotto_lo_toglie_dalla_vetrina_ma_non_lo_elimina(): void
    {
        $prodotto = $this->prodotto();

        $this->actingAs($this->staff())->put(route('gestione.prodotti.update', $prodotto), array_merge(
            $this->datiForm($prodotto),
            ['is_active' => '0'],
        ));

        $this->assertFalse($prodotto->fresh()->is_active);
        $this->assertDatabaseCount('products', 1);
    }
}
