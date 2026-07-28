<?php

namespace Modules\Commerce\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Commerce\Enums\RuoloUtente;
use Modules\Commerce\Models\Agenzia;
use Modules\Commerce\Models\Carrello;
use Modules\Commerce\Models\RigaCarrello;
use Modules\Commerce\Models\ScaglionePrezzo;
use Modules\Commerce\Tests\Concerns\CreaSoggetti;
use Tests\TestCase;

class CarrelloTest extends TestCase
{
    use CreaSoggetti, RefreshDatabase;

    private function prodotto(array $attributi = []): Product
    {
        $categoria = Category::firstOrCreate(
            ['slug' => 'rosari-e-corone'],
            ['name' => 'Rosari e corone'],
        );

        return Product::create(array_merge([
            'category_id' => $categoria->id,
            'sku' => 'TEST-'.uniqid(),
            'slug' => 'test-'.uniqid(),
            'name' => 'Rosario di prova',
            'price' => 2600,
            'is_active' => true,
        ], $attributi));
    }

    public function test_un_ospite_puo_riempire_il_carrello(): void
    {
        $prodotto = $this->prodotto();

        $this->post('/carrello', ['product_id' => $prodotto->id, 'quantita' => 3])
            ->assertRedirect(route('carrello'));

        $carrello = Carrello::firstOrFail();
        $this->assertNull($carrello->user_id, 'il carrello dell\'ospite non ha un utente');
        $this->assertNotNull($carrello->token);
        $this->assertSame(3, $carrello->righe()->first()->quantita);
    }

    public function test_aggiungere_due_volte_lo_stesso_prodotto_somma_la_riga(): void
    {
        $prodotto = $this->prodotto();

        $this->post('/carrello', ['product_id' => $prodotto->id, 'quantita' => 3]);
        $this->post('/carrello', ['product_id' => $prodotto->id, 'quantita' => 2]);

        $this->assertDatabaseCount('righe_carrello', 1);
        $this->assertSame(5, RigaCarrello::firstOrFail()->quantita);
    }

    public function test_visitare_la_vetrina_non_crea_un_carrello(): void
    {
        // Il contatore in barra legge il carrello senza crearlo: altrimenti
        // ogni visitatore lascerebbe una riga a database.
        $this->get('/carrello')->assertOk();

        $this->assertDatabaseCount('carrelli', 0);
    }

    public function test_azzerare_la_quantita_toglie_la_riga(): void
    {
        $prodotto = $this->prodotto();
        $this->post('/carrello', ['product_id' => $prodotto->id, 'quantita' => 3]);
        $riga = RigaCarrello::firstOrFail();

        $this->patch("/carrello/riga/{$riga->id}", ['quantita' => 0])
            ->assertRedirect(route('carrello'));

        $this->assertDatabaseCount('righe_carrello', 0);
    }

    public function test_non_si_tocca_la_riga_di_un_altro(): void
    {
        // Gli id sono progressivi: senza controllo bastava cambiare un numero
        // nell'indirizzo per svuotare il carrello altrui.
        $altrui = Carrello::create(['token' => 'token-di-un-altro']);
        $riga = $altrui->righe()->create(['product_id' => $this->prodotto()->id, 'quantita' => 4]);

        $this->patch("/carrello/riga/{$riga->id}", ['quantita' => 99])->assertNotFound();
        $this->delete("/carrello/riga/{$riga->id}")->assertNotFound();

        $this->assertSame(4, $riga->fresh()->quantita);
    }

    public function test_al_primo_accesso_il_carrello_dell_ospite_entra_in_quello_dell_account(): void
    {
        $prodotto = $this->prodotto();
        $altro = $this->prodotto();

        // da ospite
        $this->post('/carrello', ['product_id' => $prodotto->id, 'quantita' => 10]);

        // l'account aveva gia' qualcosa dentro
        $utente = User::factory()->create(['password' => bcrypt('password-di-prova')]);
        $suo = Carrello::create(['user_id' => $utente->id]);
        $suo->righe()->create(['product_id' => $prodotto->id, 'quantita' => 5]);
        $suo->righe()->create(['product_id' => $altro->id, 'quantita' => 2]);

        $this->post('/accedi', ['email' => $utente->email, 'password' => 'password-di-prova'])
            ->assertRedirect(route('account'));

        // resta solo quello dell'account: quello dell'ospite viene cancellato
        $this->assertDatabaseCount('carrelli', 1);
        $suo->refresh()->load('righe');
        $this->assertSame(15, $suo->righe->firstWhere('product_id', $prodotto->id)->quantita, '10 + 5');
        $this->assertSame(2, $suo->righe->firstWhere('product_id', $altro->id)->quantita);
    }

    public function test_il_carrello_mostra_lo_sconto_agenzia_e_il_totale(): void
    {
        $prodotto = $this->prodotto();
        ScaglionePrezzo::create([
            'product_id' => $prodotto->id, 'quantita_minima' => 25, 'sconto_percentuale' => 10,
        ]);

        $referente = $this->referenteAgenzia(attributi: ['ordine_minimo_pezzi' => 30]);
        $carrello = Carrello::create(['user_id' => $referente->id]);
        $carrello->righe()->create(['product_id' => $prodotto->id, 'quantita' => 25]);

        $this->actingAs($referente)->get('/carrello')
            ->assertOk()
            ->assertSee('585,00 €')        // 650,00 meno il 10%
            ->assertSee('Sconto agenzia');
    }

    public function test_il_carrello_avvisa_quando_manca_al_minimo_d_ordine(): void
    {
        $referente = $this->referenteAgenzia(attributi: ['ordine_minimo_pezzi' => 30]);   // minimo 30 pezzi
        $carrello = Carrello::create(['user_id' => $referente->id]);
        $carrello->righe()->create(['product_id' => $this->prodotto()->id, 'quantita' => 12]);

        $this->actingAs($referente)->get('/carrello')
            ->assertOk()
            ->assertSee('30 pezzi')
            ->assertSee('ne mancano 18');
    }

    public function test_il_privato_non_vede_nessun_minimo_d_ordine(): void
    {
        $utente = User::factory()->create();
        $carrello = Carrello::create(['user_id' => $utente->id]);
        $carrello->righe()->create(['product_id' => $this->prodotto()->id, 'quantita' => 1]);

        $this->actingAs($utente)->get('/carrello')
            ->assertOk()
            ->assertDontSee('minimo d\'ordine');
    }

    public function test_un_carrello_di_soli_crediti_ignora_il_minimo_d_ordine(): void
    {
        $categoria = Category::firstOrCreate(
            ['slug' => 'servizi-agenzia'],
            ['name' => 'Servizi per agenzie'],
        );
        $pacchettoCrediti = Product::create([
            'category_id' => $categoria->id,
            'sku' => 'SRV-TEST-'.uniqid(),
            'slug' => 'test-crediti-'.uniqid(),
            'name' => 'Pacchetto crediti di prova',
            'price' => 10000,
            'crediti' => 100,
            'is_photo_printable' => false,
            'is_active' => true,
        ]);

        $referente = $this->referenteAgenzia(attributi: ['ordine_minimo_pezzi' => 30]);
        $carrello = Carrello::create(['user_id' => $referente->id]);
        $carrello->righe()->create(['product_id' => $pacchettoCrediti->id, 'quantita' => 1]);

        $this->actingAs($referente)->get('/carrello')
            ->assertOk()
            ->assertSeeText('il minimo d\'ordine non si applica')
            ->assertDontSee('Mancano')
            ->assertSeeText('Concludi l\'ordine');
    }

    public function test_non_si_aggiunge_un_prodotto_ritirato(): void
    {
        $prodotto = $this->prodotto(['is_active' => false]);

        $this->post('/carrello', ['product_id' => $prodotto->id, 'quantita' => 1])
            ->assertNotFound();

        $this->assertDatabaseCount('righe_carrello', 0);
    }
}
