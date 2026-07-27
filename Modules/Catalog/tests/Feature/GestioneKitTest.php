<?php

namespace Modules\Catalog\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Commerce\Tests\Concerns\CreaSoggetti;
use Tests\TestCase;

class GestioneKitTest extends TestCase
{
    use CreaSoggetti, RefreshDatabase;

    private function categoria(): Category
    {
        return Category::create(['name' => 'Trigesimali', 'slug' => 'trigesimali-'.uniqid()]);
    }

    private function articolo(array $attributi = []): Product
    {
        return Product::create(array_merge([
            'category_id' => $this->categoria()->id,
            'sku' => 'ART-'.uniqid(),
            'slug' => 'art-'.uniqid(),
            'name' => 'Rosario di prova',
            'price' => 2600,
            'stock' => 50,
            'is_active' => true,
        ], $attributi));
    }

    public function test_chi_non_e_staff_non_vede_nemmeno_che_gestione_esiste(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/gestione/kit')
            ->assertNotFound();
    }

    public function test_lo_staff_crea_un_kit_con_prezzo_impostato_a_mano(): void
    {
        $risposta = $this->actingAs($this->staff())->post('/gestione/kit', [
            'category_id' => $this->categoria()->id,
            'sku' => 'KIT-ARGENTO',
            'slug' => 'kit-argento',
            'name' => 'Kit Argento',
            'price' => '79,00',
            'stock' => 5,
        ]);

        $kit = Product::firstOrFail();
        $risposta->assertRedirect(route('gestione.kit.show', $kit));

        $this->assertTrue($kit->is_componibile);
        $this->assertSame(7900, $kit->price);
    }

    public function test_si_aggiungono_articoli_dal_magazzino_al_kit(): void
    {
        $kit = $this->articolo(['name' => 'Kit Base', 'is_componibile' => true, 'price' => 7900]);
        $rosario = $this->articolo(['name' => 'Rosario']);
        $cofanetto = $this->articolo(['name' => 'Cofanetto']);

        $this->actingAs($this->staff())->post(route('gestione.kit.componenti.store', $kit), [
            'componente_product_id' => $rosario->id,
            'quantita' => 1,
        ]);
        $this->actingAs($this->staff())->post(route('gestione.kit.componenti.store', $kit), [
            'componente_product_id' => $cofanetto->id,
            'quantita' => 2,
        ]);

        $kit->refresh()->load('componenti');
        $this->assertCount(2, $kit->componenti);
        $this->assertSame(2, $kit->componenti->firstWhere('componente_product_id', $cofanetto->id)->quantita);
    }

    public function test_aggiungere_due_volte_lo_stesso_articolo_somma_la_quantita(): void
    {
        $kit = $this->articolo(['is_componibile' => true]);
        $rosario = $this->articolo(['name' => 'Rosario']);

        $this->actingAs($this->staff())->post(route('gestione.kit.componenti.store', $kit), [
            'componente_product_id' => $rosario->id, 'quantita' => 1,
        ]);
        $this->actingAs($this->staff())->post(route('gestione.kit.componenti.store', $kit), [
            'componente_product_id' => $rosario->id, 'quantita' => 2,
        ]);

        $kit->refresh()->load('componenti');
        $this->assertCount(1, $kit->componenti);
        $this->assertSame(3, $kit->componenti->first()->quantita);
    }

    public function test_un_kit_non_puo_contenere_se_stesso(): void
    {
        $kit = $this->articolo(['is_componibile' => true]);

        $this->actingAs($this->staff())->post(route('gestione.kit.componenti.store', $kit), [
            'componente_product_id' => $kit->id, 'quantita' => 1,
        ])->assertStatus(422);

        $this->assertCount(0, $kit->fresh()->componenti);
    }

    public function test_si_toglie_un_articolo_dal_kit(): void
    {
        $kit = $this->articolo(['is_componibile' => true]);
        $rosario = $this->articolo(['name' => 'Rosario']);
        $riga = $kit->componenti()->create(['componente_product_id' => $rosario->id, 'quantita' => 1]);

        $this->actingAs($this->staff())
            ->delete(route('gestione.kit.componenti.destroy', [$kit, $riga]))
            ->assertRedirect(route('gestione.kit.show', $kit));

        $this->assertCount(0, $kit->fresh()->componenti);
    }

    public function test_un_kit_componibile_e_acquistabile_come_un_prodotto_normale(): void
    {
        $kit = $this->articolo(['name' => 'Kit Argento', 'is_componibile' => true, 'price' => 7900]);

        $this->post('/carrello', ['product_id' => $kit->id, 'quantita' => 1])
            ->assertRedirect(route('carrello'));

        $this->assertDatabaseHas('righe_carrello', ['product_id' => $kit->id, 'quantita' => 1]);
    }
}
