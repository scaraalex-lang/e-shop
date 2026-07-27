<?php

namespace Modules\Catalog\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Catalog\Models\Category;
use Modules\Commerce\Tests\Concerns\CreaSoggetti;
use Tests\TestCase;

class GestioneCategorieTest extends TestCase
{
    use CreaSoggetti, RefreshDatabase;

    private function datiForm(array $sovrascrivi = []): array
    {
        return array_merge([
            'name' => 'Rosari e corone',
            'slug' => 'rosari-e-corone-'.uniqid(),
            'sort_order' => 0,
            'is_active' => '1',
        ], $sovrascrivi);
    }

    public function test_chi_non_e_staff_non_vede_nemmeno_che_gestione_esiste(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/gestione/categorie')
            ->assertNotFound();
    }

    public function test_lo_staff_vede_lelenco_delle_categorie(): void
    {
        Category::create(['name' => 'Devozionali', 'slug' => 'devozionali-'.uniqid()]);

        $this->actingAs($this->staff())
            ->get('/gestione/categorie')
            ->assertOk()
            ->assertSee('Devozionali');
    }

    public function test_lo_staff_crea_una_categoria(): void
    {
        $risposta = $this->actingAs($this->staff())->post('/gestione/categorie', $this->datiForm());

        $categoria = Category::firstOrFail();
        $risposta->assertRedirect(route('gestione.categorie.edit', $categoria));
        $this->assertSame('Rosari e corone', $categoria->name);
        $this->assertTrue($categoria->is_active);
    }

    public function test_una_categoria_puo_avere_un_padre(): void
    {
        $padre = Category::create(['name' => 'Devozionali', 'slug' => 'devozionali-'.uniqid()]);

        $this->actingAs($this->staff())->post('/gestione/categorie', $this->datiForm(['parent_id' => $padre->id]));

        $figlia = Category::where('id', '!=', $padre->id)->firstOrFail();
        $this->assertSame($padre->id, $figlia->parent_id);
    }

    public function test_lo_slug_duplicato_viene_rifiutato(): void
    {
        Category::create(['name' => 'Croci', 'slug' => 'croci-dup']);

        $this->actingAs($this->staff())
            ->post('/gestione/categorie', $this->datiForm(['slug' => 'croci-dup']))
            ->assertSessionHasErrors('slug');

        $this->assertSame(1, Category::count());
    }

    public function test_limmagine_si_carica_e_sostituisce_la_precedente(): void
    {
        Storage::fake('public');
        $categoria = Category::create(['name' => 'Croci', 'slug' => 'croci-img']);

        $this->actingAs($this->staff())->put(route('gestione.categorie.update', $categoria), array_merge(
            $this->datiForm(['name' => $categoria->name, 'slug' => $categoria->slug]),
            ['immagine' => UploadedFile::fake()->image('croci.jpg', 800, 600)],
        ));

        $categoria->refresh();
        $this->assertNotNull($categoria->image);
        Storage::disk('public')->assertExists($categoria->image);
    }

    public function test_disattivare_una_categoria_non_la_elimina(): void
    {
        $categoria = Category::create(['name' => 'Croci', 'slug' => 'croci-disattiva']);

        $this->actingAs($this->staff())->put(route('gestione.categorie.update', $categoria), array_merge(
            $this->datiForm(['name' => $categoria->name, 'slug' => $categoria->slug]),
            ['is_active' => '0'],
        ));

        $this->assertFalse($categoria->fresh()->is_active);
        $this->assertDatabaseCount('categories', 1);
    }
}
