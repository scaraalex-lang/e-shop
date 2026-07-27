<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    private function prodotto(array $attributi = []): Product
    {
        $categoria = Category::firstOrCreate(['slug' => 'rosari-home-test'], ['name' => 'Rosari']);

        return Product::create(array_merge([
            'category_id' => $categoria->id,
            'sku' => 'HOME-'.uniqid(),
            'slug' => 'home-'.uniqid(),
            'name' => 'Prodotto di prova',
            'price' => 2600,
            'is_active' => true,
        ], $attributi));
    }

    public function test_la_home_mostra_solo_i_prodotti_in_evidenza(): void
    {
        $evidenza = $this->prodotto(['name' => 'Rosario in evidenza', 'is_featured' => true]);
        $this->prodotto(['name' => 'Rosario non in evidenza', 'is_featured' => false]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Rosario in evidenza')
            ->assertDontSee('Rosario non in evidenza');
    }

    public function test_un_prodotto_disattivato_non_compare_anche_se_in_evidenza(): void
    {
        $this->prodotto(['name' => 'Rosario nascosto', 'is_featured' => true, 'is_active' => false]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Rosario nascosto');
    }
}
