<?php

namespace Modules\PhotoPrint\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Commerce\Models\Carrello;
use Modules\Commerce\Models\Ordine;
use Tests\TestCase;

/**
 * La sequenza della lavorazione (dati del defunto -> foto -> ricordino) era
 * imposta solo con CSS: aprendo l'URL a mano si arrivava agli editor anche
 * senza i dati necessari. Questi test verificano il guard server-side vero.
 */
class SequenzaLavorazioneTest extends TestCase
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

    private function salvaDefunto(Ordine $ordine): void
    {
        $this->actingAs($ordine->user)->post("/account/ordini/{$ordine->numero}/lavorazione/defunto", [
            'nome' => 'Luigia', 'cognome' => 'Rossetti',
            'gdpr_parentela' => 'Figlia', 'gdpr_consenso' => '1',
        ])->assertRedirect();
    }

    public function test_lo_studio_foto_reindirizza_alla_lavorazione_senza_defunto(): void
    {
        $ordine = $this->ordineInLavorazione();
        $this->apriLavorazione($ordine);

        $this->actingAs($ordine->user)
            ->get('/studio/foto')
            ->assertRedirect(route('lavorazione', $ordine));
    }

    public function test_lo_studio_foto_si_apre_dopo_i_dati_del_defunto(): void
    {
        $ordine = $this->ordineInLavorazione();
        $this->apriLavorazione($ordine);
        $this->salvaDefunto($ordine);

        $this->actingAs($ordine->user)
            ->get('/studio/foto')
            ->assertOk();
    }

    public function test_il_ricordino_designer_reindirizza_alla_lavorazione_senza_foto_principale(): void
    {
        $ordine = $this->ordineInLavorazione();
        $this->apriLavorazione($ordine);
        $this->salvaDefunto($ordine);

        $this->actingAs($ordine->user)
            ->get('/studio/ricordino')
            ->assertRedirect(route('lavorazione', $ordine));
    }

    public function test_salvare_i_dati_del_defunto_accetta_i_luoghi_della_cerimonia(): void
    {
        $ordine = $this->ordineInLavorazione();
        $this->apriLavorazione($ordine);

        $this->actingAs($ordine->user)->post("/account/ordini/{$ordine->numero}/lavorazione/defunto", [
            'nome' => 'Luigia', 'cognome' => 'Rossetti',
            'gdpr_parentela' => 'Figlia', 'gdpr_consenso' => '1',
            'luogo_partenza' => 'Casa funeraria',
            'indirizzo_cerimonia' => 'Via Torino 5, Milano',
            'cerimonia_at' => '2026-08-10 10:30',
            'chiesa' => 'Chiesa di San Giuseppe',
            'indirizzo_chiesa' => 'Via Roma 1, Milano',
            'cimitero' => 'Cimitero Monumentale',
            'indirizzo_cimitero' => 'Piazzale Cimitero Monumentale, Milano',
        ])->assertRedirect();

        $defunto = \Modules\Memorial\Models\Defunto::firstOrFail();
        $this->assertSame('Casa funeraria', $defunto->luogo_partenza);
        $this->assertSame('Via Torino 5, Milano', $defunto->indirizzo_cerimonia);
        $this->assertSame('10/08/2026 10:30', $defunto->cerimonia_at->format('d/m/Y H:i'));
        $this->assertSame('Chiesa di San Giuseppe', $defunto->chiesa);
        $this->assertSame('Via Roma 1, Milano', $defunto->indirizzo_chiesa);
        $this->assertSame('Cimitero Monumentale', $defunto->cimitero);
        $this->assertSame('Piazzale Cimitero Monumentale, Milano', $defunto->indirizzo_cimitero);
    }

    public function test_il_luogo_di_partenza_deve_essere_una_delle_opzioni_previste(): void
    {
        $ordine = $this->ordineInLavorazione();
        $this->apriLavorazione($ordine);

        $this->actingAs($ordine->user)->post("/account/ordini/{$ordine->numero}/lavorazione/defunto", [
            'nome' => 'Luigia', 'cognome' => 'Rossetti',
            'gdpr_parentela' => 'Figlia', 'gdpr_consenso' => '1',
            'luogo_partenza' => 'Un posto qualsiasi',
        ])->assertSessionHasErrors('luogo_partenza');
    }

    public function test_il_ricordino_designer_si_apre_con_la_foto_principale(): void
    {
        $ordine = $this->ordineInLavorazione();
        $this->apriLavorazione($ordine);
        $this->salvaDefunto($ordine);

        DB::table('foto_pratica')->insert([
            'ordine_id' => $ordine->id,
            'path' => 'photoprint/pratica/principale.jpg',
            'tipo' => 'originale',
            'is_principale' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($ordine->user)
            ->get('/studio/ricordino')
            ->assertOk()
            ->assertViewHas('fotoElaborata', '/storage/photoprint/pratica/principale.jpg');
    }
}
