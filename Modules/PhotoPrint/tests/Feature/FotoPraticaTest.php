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
use Modules\PhotoPrint\Models\FotoPratica;
use Tests\TestCase;

/**
 * La galleria della pratica.
 *
 * Questi tre endpoint erano chiamati dal Foto Manager ma non erano mai stati
 * portati: chi apriva una lavorazione con la galleria vuota trovava soltanto
 * "upload fallito". Nessun test li sfiorava, ed è per questo che sono rimasti
 * scoperti fino alla prima prova con un cliente vero.
 */
class FotoPraticaTest extends TestCase
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

    /** Apre la lavorazione: è il gesto che dice agli editor su cosa lavorare. */
    private function apriLavorazione(Ordine $ordine): void
    {
        $this->actingAs($ordine->user)
            ->get("/account/ordini/{$ordine->numero}/lavorazione")
            ->assertOk();
    }

    public function test_il_cliente_carica_una_foto_sulla_propria_pratica(): void
    {
        Storage::fake('public');
        $ordine = $this->ordineInLavorazione();
        $this->apriLavorazione($ordine);

        $risposta = $this->actingAs($ordine->user)->post('/admin/api/foto-pratica/upload', [
            'photo' => UploadedFile::fake()->image('nonna.jpg', 1200, 1600),
        ]);

        $risposta->assertOk()->assertJson(['success' => true]);
        $risposta->assertJsonStructure(['photo' => ['id', 'url', 'tipo', 'is_principale']]);

        $foto = FotoPratica::firstOrFail();
        $this->assertSame($ordine->id, $foto->ordine_id);
        $this->assertTrue($foto->is_principale, 'la prima foto della pratica diventa la principale');
        Storage::disk('public')->assertExists($foto->path);
    }

    public function test_un_file_che_non_e_una_immagine_viene_rifiutato(): void
    {
        Storage::fake('public');
        $ordine = $this->ordineInLavorazione();
        $this->apriLavorazione($ordine);

        $this->actingAs($ordine->user)
            ->postJson('/admin/api/foto-pratica/upload', [
                'photo' => UploadedFile::fake()->create('preventivo.pdf', 100, 'application/pdf'),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('photo');

        $this->assertDatabaseCount('foto_pratica', 0);
    }

    public function test_il_canvas_si_salva_sulla_pratica_come_principale(): void
    {
        Storage::fake('public');
        $ordine = $this->ordineInLavorazione();
        $this->apriLavorazione($ordine);

        // 1x1 px: al test interessa il percorso, non l'immagine
        $dataUrl = 'data:image/jpeg;base64,'.base64_encode('finta-immagine');

        $this->actingAs($ordine->user)->postJson('/admin/api/foto-pratica/salva', [
            'image' => $dataUrl,
            'tipo' => 'ritagliata',
            'is_principale' => 1,
        ])->assertOk()->assertJson(['success' => true]);

        $foto = FotoPratica::firstOrFail();
        $this->assertSame('ritagliata', $foto->tipo);
        $this->assertTrue($foto->is_principale);
    }

    public function test_salvare_una_nuova_principale_toglie_il_segno_alla_precedente(): void
    {
        Storage::fake('public');
        $ordine = $this->ordineInLavorazione();
        $this->apriLavorazione($ordine);

        $dataUrl = 'data:image/jpeg;base64,'.base64_encode('finta');

        foreach ([1, 2] as $volta) {
            $this->actingAs($ordine->user)->postJson('/admin/api/foto-pratica/salva', [
                'image' => $dataUrl, 'tipo' => 'ritagliata', 'is_principale' => 1,
            ])->assertOk();
        }

        $this->assertSame(1, FotoPratica::where('is_principale', true)->count());
        $this->assertTrue(FotoPratica::latest('id')->first()->is_principale);
    }

    public function test_una_foto_si_elimina_con_il_suo_file(): void
    {
        Storage::fake('public');
        $ordine = $this->ordineInLavorazione();
        $this->apriLavorazione($ordine);

        $this->actingAs($ordine->user)->post('/admin/api/foto-pratica/upload', [
            'photo' => UploadedFile::fake()->image('nonna.jpg'),
        ]);
        $foto = FotoPratica::firstOrFail();

        $this->actingAs($ordine->user)
            ->deleteJson("/admin/api/foto-pratica/{$foto->id}")
            ->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseCount('foto_pratica', 0);
        Storage::disk('public')->assertMissing($foto->path);
    }

    public function test_non_si_tocca_la_foto_della_pratica_di_un_altro(): void
    {
        Storage::fake('public');
        $mia = $this->ordineInLavorazione();
        $this->apriLavorazione($mia);
        $this->actingAs($mia->user)->post('/admin/api/foto-pratica/upload', [
            'photo' => UploadedFile::fake()->image('nonna.jpg'),
        ]);
        $foto = FotoPratica::firstOrFail();

        // un altro cliente, con la sua lavorazione aperta
        $sua = $this->ordineInLavorazione();
        $this->apriLavorazione($sua);

        $this->actingAs($sua->user)
            ->deleteJson("/admin/api/foto-pratica/{$foto->id}")
            ->assertNotFound();

        $this->assertDatabaseCount('foto_pratica', 1);
    }

    public function test_senza_lavorazione_aperta_non_si_carica_niente(): void
    {
        Storage::fake('public');
        $ordine = $this->ordineInLavorazione();
        // NON apro la lavorazione: nessuna pratica corrente

        $this->actingAs($ordine->user)
            ->postJson('/admin/api/foto-pratica/upload', [
                'photo' => UploadedFile::fake()->image('nonna.jpg'),
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('foto_pratica', 0);
    }
}
