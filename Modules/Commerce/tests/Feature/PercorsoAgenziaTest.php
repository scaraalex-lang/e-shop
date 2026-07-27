<?php

namespace Modules\Commerce\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Commerce\Enums\MetodoPagamento;
use Modules\Commerce\Enums\StatoAgenzia;
use Modules\Commerce\Models\Agenzia;
use Modules\Commerce\Models\Carrello;
use Modules\Commerce\Models\Ordine;
use Modules\Commerce\Models\ScaglionePrezzo;
use Modules\Commerce\Tests\Concerns\CreaSoggetti;
use Tests\TestCase;

/**
 * Ripercorre l'intera filiera B2B come la vive un'agenzia vera, non un pezzo
 * alla volta: registrazione -> attesa (prezzo pubblico) -> approvazione ->
 * sconto a scaglioni sbloccato -> minimo d'ordine -> fattura -> ordine
 * tracciabile. I singoli pezzi hanno già i loro test (RegistrazioneAgenziaTest,
 * GestioneAgenzieTest, ListinoTest, CarrelloTest, OrdineTest): questo verifica
 * che la catena regga insieme, con lo stesso account dall'inizio alla fine.
 */
class PercorsoAgenziaTest extends TestCase
{
    use CreaSoggetti, RefreshDatabase;

    private const CONSEGNA = [
        'nome' => 'Marco Bianchi',
        'telefono' => '0212345678',
        'indirizzo' => 'Via Roma 12',
        'cap' => '20121',
        'citta' => 'Milano',
        'provincia' => 'mi',
    ];

    private function prodotto(array $attributi = []): Product
    {
        $categoria = Category::firstOrCreate(
            ['slug' => 'articoli-trigesimali'],
            ['name' => 'Articoli trigesimali'],
        );

        return Product::create(array_merge([
            'category_id' => $categoria->id,
            'sku' => 'TEST-'.uniqid(),
            'slug' => 'test-'.uniqid(),
            'name' => 'Corona di prova',
            'price' => 2600, // 26,00 €
            'is_active' => true,
        ], $attributi));
    }

    public function test_la_filiera_b2b_dalla_registrazione_all_ordine_tracciabile(): void
    {
        // ---- 1. Registrazione: l'agenzia entra subito, ma resta in attesa.
        $this->post('/registrati/agenzia', [
            'name' => 'Marco Bianchi',
            'email' => 'marco@onoranzebianchi.it',
            'telefono' => '0212345678',
            'password' => 'password-lunga-abbastanza',
            'password_confirmation' => 'password-lunga-abbastanza',
            'ragione_sociale' => 'Onoranze Funebri Bianchi S.r.l.',
            'partita_iva' => self::PIVA_VALIDA,
            'indirizzo' => 'Via Roma 12',
            'cap' => '20121',
            'citta' => 'Milano',
            'provincia' => 'mi',
        ])->assertRedirect(route('account'));

        $agenzia = Agenzia::firstOrFail();
        $referente = User::firstOrFail();
        $this->assertSame(StatoAgenzia::InAttesa, $agenzia->stato);
        $this->assertFalse($referente->eAgenziaApprovata());

        // Un prodotto con uno scaglione: da 25 pezzi in su, -10%. Il minimo
        // d'ordine dell'agenzia coincide apposta con la soglia dello sconto,
        // così lo stesso numero di pezzi verifica entrambe le regole.
        $prodotto = $this->prodotto();
        ScaglionePrezzo::create([
            'product_id' => $prodotto->id, 'quantita_minima' => 25, 'sconto_percentuale' => 10,
        ]);
        $agenzia->update(['ordine_minimo_pezzi' => 25]);

        // ---- 2. Prima dell'approvazione: prezzo pubblico, niente fattura.
        $this->actingAs($referente)
            ->post('/carrello', ['product_id' => $prodotto->id, 'quantita' => 25]);

        $this->actingAs($referente)->get('/carrello')
            ->assertOk()
            ->assertDontSee('Sconto agenzia');

        $this->actingAs($referente)
            ->post('/ordine/conferma', array_merge(self::CONSEGNA, ['metodo_pagamento' => 'fattura']))
            ->assertSessionHasErrors('metodo_pagamento');
        // non approvata: la fattura non è nemmeno un'opzione
        $this->assertDatabaseCount('ordini', 0);

        // ---- 3. Lo staff approva l'agenzia.
        $this->actingAs($this->staff())
            ->post("/gestione/agenzie/{$agenzia->id}/approva", ['note_interne' => 'P.IVA verificata'])
            ->assertRedirect(route('gestione.agenzie.show', $agenzia));

        $referente = $referente->fresh();
        $this->assertTrue($referente->eAgenziaApprovata());

        // ---- 4. Lo sconto compare nel carrello, stesso account, stesso carrello.
        $this->actingAs($referente)->get('/carrello')
            ->assertOk()
            ->assertSee('Sconto agenzia')
            ->assertSee('585,00 €'); // 25 x 26,00 = 650,00 -> -10% = 585,00

        // ---- 5. Sotto il minimo (10 pezzi): il checkout rimanda al carrello,
        // sia in lettura (GET) sia provando a confermare direttamente (POST) —
        // il minimo non è solo un avviso a schermo, blocca anche chi salta la
        // pagina e manda la richiesta a mano.
        $carrello = Carrello::where('user_id', $referente->id)->firstOrFail();
        $carrello->righe()->first()->update(['quantita' => 10]);

        $this->actingAs($referente)->get('/ordine/conferma')->assertRedirect(route('carrello'));

        $this->actingAs($referente)
            ->post('/ordine/conferma', array_merge(self::CONSEGNA, ['metodo_pagamento' => 'fattura']))
            ->assertRedirect(route('carrello'));
        // sotto il minimo non si passa, nemmeno via POST diretto
        $this->assertDatabaseCount('ordini', 0);

        // ---- 6. Torna esattamente al minimo (25 pezzi): checkout aperto, fattura disponibile.
        $carrello->righe()->first()->update(['quantita' => 25]);

        $this->actingAs($referente)->get('/ordine/conferma')->assertOk();

        $risposta = $this->actingAs($referente)->post('/ordine/conferma', array_merge(self::CONSEGNA, [
            'metodo_pagamento' => 'fattura',
        ]));

        $ordine = Ordine::firstOrFail();
        $risposta->assertRedirect(route('ordine', $ordine));

        // ---- 7. Sull'ordine restano scritti prezzo scontato, agenzia e spedizione gratuita.
        $riga = $ordine->righe->first();
        $this->assertSame(65_000, $riga->prezzo_pieno);
        $this->assertSame(58_500, $riga->prezzo);
        $this->assertSame('10.00', (string) $riga->sconto_percentuale);
        $this->assertSame($agenzia->id, $ordine->agenzia_id);
        $this->assertSame(MetodoPagamento::Fattura, $ordine->metodo_pagamento);
        $this->assertSame(0, $ordine->spedizione, 'per le agenzie la spedizione è inclusa');
        // il carrello si è svuotato dopo l'ordine
        $this->assertDatabaseCount('righe_carrello', 0);

        // ---- 8. L'ordine si traccia dall'area account, e solo il proprietario lo vede.
        $this->actingAs($referente)
            ->get("/account/ordini/{$ordine->numero}")
            ->assertOk()
            ->assertSee($ordine->numero);

        $this->actingAs($referente)->get('/account/ordini')
            ->assertOk()
            ->assertSee($ordine->numero);

        $this->actingAs($this->referenteAgenzia(attributi: ['ragione_sociale' => 'Un\'altra agenzia', 'partita_iva' => '12485671007']))
            ->get("/account/ordini/{$ordine->numero}")
            ->assertNotFound();
    }
}
