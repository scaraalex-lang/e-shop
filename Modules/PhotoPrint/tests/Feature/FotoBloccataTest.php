<?php

namespace Modules\PhotoPrint\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Commerce\Models\Carrello;
use Modules\Commerce\Models\Ordine;
use Modules\Memorial\Models\Defunto;
use Modules\PhotoPrint\Models\FotoPratica;
use Tests\TestCase;

/**
 * Una volta confermata la foto principale, il Foto Manager si blocca (sola
 * visualizzazione): evita che l'operatore la rielabori o ne carichi altre
 * dopo aver chiuso il primo passo della Scheda Defunto.
 */
class FotoBloccataTest extends TestCase
{
    use RefreshDatabase;

    private function ordineInLavorazione(): Ordine
    {
        $categoria = Category::firstOrCreate(
            ['slug' => 'articoli-trigesimali'],
            ['name' => 'Articoli trigesimali'],
        );

        $prodotto = Product::create([
            'category_id' => $categoria->id,
            'sku' => 'TRG-TEST-'.uniqid(),
            'slug' => 'trg-test-'.uniqid(),
            'name' => 'Cofanetto di prova',
            'price' => 4900,
            'is_photo_printable' => true,
            'is_active' => true,
        ]);

        $utente = User::factory()->create();
        $carrello = Carrello::create(['user_id' => $utente->id]);
        $carrello->righe()->create(['product_id' => $prodotto->id, 'quantita' => 1]);

        $this->actingAs($utente)->post('/ordine/conferma', [
            'nome' => 'Giulia Ferrari', 'telefono' => '3391234567',
            'indirizzo' => 'Via Manzoni 4', 'cap' => '20121',
            'citta' => 'Milano', 'provincia' => 'MI',
            'metodo_pagamento' => 'contrassegno',
        ]);

        return Ordine::latest('id')->firstOrFail();
    }

    private function apriLavorazione(Ordine $ordine): void
    {
        $this->actingAs($ordine->user)
            ->get("/account/ordini/{$ordine->numero}/lavorazione")
            ->assertOk();
    }

    public function test_salvare_come_principale_blocca_l_ordine(): void
    {
        Storage::fake('public');
        $ordine = $this->ordineInLavorazione();
        $this->apriLavorazione($ordine);

        $this->assertFalse($ordine->fresh()->fotoBloccata());

        $this->actingAs($ordine->user)->postJson('/admin/api/foto-pratica/salva', [
            'image' => 'data:image/jpeg;base64,'.base64_encode('finta'),
            'tipo' => 'ritagliata',
            'is_principale' => 1,
        ])->assertOk();

        $this->assertTrue($ordine->fresh()->fotoBloccata());
    }

    public function test_dopo_il_blocco_non_si_carica_piu_niente(): void
    {
        Storage::fake('public');
        $ordine = $this->ordineInLavorazione();
        $this->apriLavorazione($ordine);
        $ordine->forceFill(['foto_bloccata' => true])->save();

        $this->actingAs($ordine->user)
            ->postJson('/admin/api/foto-pratica/upload', [
                'photo' => UploadedFile::fake()->image('nonna.jpg'),
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('foto_pratica', 0);
    }

    public function test_dopo_il_blocco_non_si_salva_piu_dal_canvas(): void
    {
        Storage::fake('public');
        $ordine = $this->ordineInLavorazione();
        $this->apriLavorazione($ordine);
        $ordine->forceFill(['foto_bloccata' => true])->save();

        $this->actingAs($ordine->user)->postJson('/admin/api/foto-pratica/salva', [
            'image' => 'data:image/jpeg;base64,'.base64_encode('finta'),
            'tipo' => 'ritagliata',
        ])->assertForbidden();
    }

    public function test_dopo_il_blocco_non_si_elimina_piu_una_foto(): void
    {
        Storage::fake('public');
        $ordine = $this->ordineInLavorazione();
        $this->apriLavorazione($ordine);

        $this->actingAs($ordine->user)->post('/admin/api/foto-pratica/upload', [
            'photo' => UploadedFile::fake()->image('nonna.jpg'),
        ]);
        $foto = FotoPratica::firstOrFail();

        $ordine->forceFill(['foto_bloccata' => true])->save();

        $this->actingAs($ordine->user)
            ->deleteJson("/admin/api/foto-pratica/{$foto->id}")
            ->assertForbidden();

        $this->assertDatabaseCount('foto_pratica', 1);
    }

    public function test_la_pagina_mostra_il_banner_di_blocco(): void
    {
        Storage::fake('public');
        $ordine = $this->ordineInLavorazione();
        $this->apriLavorazione($ordine);
        $defunto = Defunto::create(['nome' => 'Luigia', 'cognome' => 'Rossetti', 'ordine_id' => $ordine->id]);
        $ordine->forceFill(['foto_bloccata' => true, 'defunto_id' => $defunto->id])->save();

        $this->actingAs($ordine->user)
            ->get('/studio/foto')
            ->assertOk()
            ->assertSee('Foto confermata');
    }
}
