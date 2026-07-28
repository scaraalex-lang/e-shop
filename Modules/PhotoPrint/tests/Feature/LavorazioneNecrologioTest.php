<?php

namespace Modules\PhotoPrint\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Commerce\Models\Carrello;
use Modules\Commerce\Models\Ordine;
use Modules\Commerce\Tests\Concerns\CreaSoggetti;
use Modules\Memorial\Models\Necrologio;
use Tests\TestCase;

/**
 * Necrologio e Manifesto si aprono dalla lavorazione dell'ordine, legati al
 * defunto di quella pratica: niente più menu con tutti i defunti del sistema.
 */
class LavorazioneNecrologioTest extends TestCase
{
    use CreaSoggetti, RefreshDatabase;

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

    private function pronoPerNecrologio(Ordine $ordine, int $agenziaId): void
    {
        $ordine->forceFill(['agenzia_id' => $agenziaId])->save();

        $this->actingAs($ordine->user)
            ->get("/account/ordini/{$ordine->numero}/lavorazione")
            ->assertOk();

        $this->actingAs($ordine->user)->post("/account/ordini/{$ordine->numero}/lavorazione/defunto", [
            'nome' => 'Luigia', 'cognome' => 'Rossetti',
            'gdpr_parentela' => 'Figlia', 'gdpr_consenso' => '1',
        ]);

        DB::table('foto_pratica')->insert([
            'ordine_id' => $ordine->id,
            'path' => 'photoprint/pratica/principale.jpg',
            'tipo' => 'originale',
            'is_principale' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_aprire_il_necrologio_dalla_lavorazione_lo_crea_se_non_esiste(): void
    {
        $referente = $this->referenteAgenzia();
        $ordine = $this->ordineInLavorazione();
        $this->pronoPerNecrologio($ordine, $referente->agenzia_id);

        $this->assertDatabaseCount('necrologi', 0);

        $this->actingAs($ordine->user)
            ->post(route('lavorazione.necrologio', $ordine))
            ->assertRedirect();

        $necrologio = Necrologio::firstOrFail();
        $this->assertSame($ordine->fresh()->defunto_id, $necrologio->defunto_id);
        $this->assertSame($referente->agenzia_id, $necrologio->agenzia_id);
    }

    public function test_aprire_il_necrologio_due_volte_riusa_lo_stesso(): void
    {
        $referente = $this->referenteAgenzia();
        $ordine = $this->ordineInLavorazione();
        $this->pronoPerNecrologio($ordine, $referente->agenzia_id);

        $this->actingAs($ordine->user)->post(route('lavorazione.necrologio', $ordine));
        $this->actingAs($ordine->user)->post(route('lavorazione.necrologio', $ordine));

        $this->assertDatabaseCount('necrologi', 1);
    }

    public function test_un_ordine_privato_senza_agenzia_non_apre_il_necrologio(): void
    {
        $ordine = $this->ordineInLavorazione();

        $this->actingAs($ordine->user)
            ->get("/account/ordini/{$ordine->numero}/lavorazione")
            ->assertOk();
        $this->actingAs($ordine->user)->post("/account/ordini/{$ordine->numero}/lavorazione/defunto", [
            'nome' => 'Luigia', 'cognome' => 'Rossetti',
            'gdpr_parentela' => 'Figlia', 'gdpr_consenso' => '1',
        ]);

        $this->actingAs($ordine->user)
            ->post(route('lavorazione.necrologio', $ordine))
            ->assertNotFound();

        $this->assertDatabaseCount('necrologi', 0);
    }

    public function test_senza_foto_principale_non_si_apre_il_necrologio(): void
    {
        $referente = $this->referenteAgenzia();
        $ordine = $this->ordineInLavorazione();
        $ordine->forceFill(['agenzia_id' => $referente->agenzia_id])->save();

        $this->actingAs($ordine->user)
            ->get("/account/ordini/{$ordine->numero}/lavorazione")
            ->assertOk();
        $this->actingAs($ordine->user)->post("/account/ordini/{$ordine->numero}/lavorazione/defunto", [
            'nome' => 'Luigia', 'cognome' => 'Rossetti',
            'gdpr_parentela' => 'Figlia', 'gdpr_consenso' => '1',
        ]);
        // NON carico la foto principale

        $this->actingAs($ordine->user)
            ->post(route('lavorazione.necrologio', $ordine))
            ->assertForbidden();
    }

    public function test_lo_staff_apre_il_designer_di_un_necrologio_nato_da_un_ordine_non_suo(): void
    {
        $referente = $this->referenteAgenzia();
        $ordine = $this->ordineInLavorazione();
        $this->pronoPerNecrologio($ordine, $referente->agenzia_id);

        $staff = $this->staff();

        $this->actingAs($staff)
            ->post(route('lavorazione.necrologio', $ordine))
            ->assertRedirect();

        $necrologio = Necrologio::firstOrFail();

        $this->actingAs($staff)
            ->get(route('necrologi.designer', $necrologio))
            ->assertOk();
    }

    public function test_aprire_il_manifesto_dalla_lavorazione_lo_crea_se_non_esiste(): void
    {
        $referente = $this->referenteAgenzia();
        $ordine = $this->ordineInLavorazione();
        $this->pronoPerNecrologio($ordine, $referente->agenzia_id);

        $this->actingAs($ordine->user)
            ->post(route('lavorazione.manifesto', $ordine))
            ->assertRedirect();

        $this->assertDatabaseCount('necrologi', 1);
    }
}
