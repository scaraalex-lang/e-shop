<?php

namespace Modules\Commerce\Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Commerce\Enums\MetodoPagamento;
use Modules\Commerce\Models\Ordine;
use Modules\Commerce\Tests\Concerns\CreaSoggetti;
use Tests\TestCase;

/**
 * Il promemoria suggerito dopo un ordine: solo un'euristica sulla categoria
 * degli articoli, niente automatismo (vedi [[Ordine::prossimaScadenzaSuggerita]]).
 */
class OrdineScadenzaSuggeritaTest extends TestCase
{
    use CreaSoggetti, RefreshDatabase;

    private function prodotto(string $categorySlug): Product
    {
        $categoria = Category::firstOrCreate(
            ['slug' => $categorySlug],
            ['name' => ucfirst($categorySlug)],
        );

        return Product::create([
            'category_id' => $categoria->id,
            'sku' => 'TEST-'.uniqid(),
            'slug' => 'test-'.uniqid(),
            'name' => 'Articolo di prova',
            'price' => 2600,
            'is_active' => true,
        ]);
    }

    private function ordine(array $attributi = []): Ordine
    {
        $utente = User::factory()->create();

        return Ordine::create(array_merge([
            'numero' => Ordine::prossimoNumero(),
            'user_id' => $utente->id,
            'metodo_pagamento' => MetodoPagamento::Carta->value,
            'totale_pieno' => 2600, 'totale_merce' => 2600, 'totale' => 2600,
            'consegna_nome' => 'Giulia Ferrari', 'consegna_telefono' => '3391234567',
            'consegna_indirizzo' => 'Via Manzoni 4', 'consegna_cap' => '20121',
            'consegna_citta' => 'Milano', 'consegna_provincia' => 'MI',
        ], $attributi));
    }

    public function test_senza_defunto_non_suggerisce_nulla(): void
    {
        $ordine = $this->ordine();

        $this->assertNull($ordine->prossimaScadenzaSuggerita());
    }

    public function test_un_ordine_di_ricordini_legato_al_defunto_suggerisce_il_trigesimo(): void
    {
        $ordine = $this->ordine();
        $ordine->forceFill(['defunto_id' => 1])->save();
        $ordine->righe()->create([
            'product_id' => $this->prodotto('photoceramiche')->id,
            'sku' => 'X', 'nome' => 'Photoceramica', 'quantita' => 1,
            'prezzo_pieno' => 2600, 'prezzo' => 2600,
        ]);

        $suggerito = $ordine->fresh(['righe.product.category'])->prossimaScadenzaSuggerita();

        $this->assertSame('Trigesimo', $suggerito['etichetta']);
        $this->assertTrue($suggerito['quando']->isSameDay($ordine->created_at->copy()->addDays(30)));
    }

    public function test_un_ordine_col_kit_trigesimo_suggerisce_l_anniversario(): void
    {
        $ordine = $this->ordine();
        $ordine->forceFill(['defunto_id' => 1])->save();
        $ordine->righe()->create([
            'product_id' => $this->prodotto('articoli-trigesimali')->id,
            'sku' => 'X', 'nome' => 'Kit Trigesimo', 'quantita' => 1,
            'prezzo_pieno' => 2600, 'prezzo' => 2600,
        ]);

        $suggerito = $ordine->fresh(['righe.product.category'])->prossimaScadenzaSuggerita();

        $this->assertSame('Anniversario', $suggerito['etichetta']);
        $this->assertTrue($suggerito['quando']->isSameDay($ordine->created_at->copy()->addYear()));
    }
}
