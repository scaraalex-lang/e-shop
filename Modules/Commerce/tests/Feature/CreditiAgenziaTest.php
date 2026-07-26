<?php

namespace Modules\Commerce\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Commerce\Enums\StatoOrdine;
use Modules\Commerce\Models\Carrello;
use Modules\Commerce\Models\MovimentoCredito;
use Modules\Commerce\Models\Ordine;
use Modules\Commerce\Tests\Concerns\CreaSoggetti;
use Tests\TestCase;

/**
 * Il pacchetto crediti: un'agenzia lo compra come un prodotto qualsiasi, ma
 * accredita crediti invece di finire in lavorazione. Il consumo (spendere i
 * crediti per aprire un servizio senza un ordine dietro) è un passo
 * successivo, non ancora costruito — qui nasce solo l'accredito.
 */
class CreditiAgenziaTest extends TestCase
{
    use CreaSoggetti, RefreshDatabase;

    private const CONSEGNA = [
        'nome' => 'Giulia Ferrari',
        'telefono' => '3391234567',
        'indirizzo' => 'Via Manzoni 4',
        'cap' => '20121',
        'citta' => 'Milano',
        'provincia' => 'MI',
    ];

    private function pacchettoCrediti(int $crediti = 100, int $prezzo = 10000): Product
    {
        $categoria = Category::firstOrCreate(
            ['slug' => 'servizi-agenzia'],
            ['name' => 'Servizi per agenzie'],
        );

        return Product::create([
            'category_id' => $categoria->id,
            'sku' => 'SRV-TEST-'.uniqid(),
            'slug' => 'test-crediti-'.uniqid(),
            'name' => 'Pacchetto crediti di prova',
            'price' => $prezzo,
            'crediti' => $crediti,
            'is_photo_printable' => false,
            'is_active' => true,
        ]);
    }

    private function conCarrello(User $utente, Product $prodotto, int $quantita): void
    {
        $carrello = Carrello::firstOrCreate(['user_id' => $utente->id]);
        $carrello->righe()->create(['product_id' => $prodotto->id, 'quantita' => $quantita]);
    }

    /**
     * Il minimo d'ordine B2B (20 pezzi di default) non c'entra coi crediti:
     * qui si compra un pacchetto, non un lotto fisico. Azzerato per isolare
     * quello che questi test verificano davvero.
     */
    private function referenteSenzaMinimo(array $attributi = []): User
    {
        return $this->referenteAgenzia(attributi: array_merge(['ordine_minimo_pezzi' => 0], $attributi));
    }

    public function test_l_agenzia_accredita_i_crediti_comprando_il_pacchetto(): void
    {
        $referente = $this->referenteSenzaMinimo();
        $prodotto = $this->pacchettoCrediti(crediti: 100);
        $this->conCarrello($referente, $prodotto, 1);

        $this->actingAs($referente)->post('/ordine/conferma', array_merge(self::CONSEGNA, [
            'metodo_pagamento' => 'fattura',
        ]));

        $ordine = Ordine::firstOrFail();
        $this->assertSame(100, $referente->agenzia->fresh()->creditiSaldo());

        $movimento = MovimentoCredito::firstOrFail();
        $this->assertSame($referente->agenzia_id, $movimento->agenzia_id);
        $this->assertSame($ordine->id, $movimento->ordine_id);
        $this->assertSame(100, $movimento->quantita);
    }

    /**
     * Il minimo d'ordine B2B (20 pezzi di default, config('commerce.ordine_minimo_pezzi'))
     * non deve bloccare chi vuole comprare SOLO crediti: un pacchetto non è
     * un pezzo da spedire, quindi non conta (vedi RigaCarrello::pezzi()).
     */
    public function test_il_minimo_d_ordine_b2b_non_blocca_un_acquisto_di_soli_crediti(): void
    {
        $referente = $this->referenteAgenzia(); // minimo di default (20 pezzi), non azzerato
        $prodotto = $this->pacchettoCrediti(crediti: 100);
        $this->conCarrello($referente, $prodotto, 1);

        $this->actingAs($referente)->post('/ordine/conferma', array_merge(self::CONSEGNA, [
            'metodo_pagamento' => 'fattura',
        ]));

        $this->assertDatabaseCount('ordini', 1);
        $this->assertSame(100, $referente->agenzia->fresh()->creditiSaldo());
    }

    /** Un kit fisico nello stesso carrello continua a contare per il minimo: il credito non lo abbassa. */
    public function test_un_kit_fisico_insieme_ai_crediti_continua_a_richiedere_il_minimo(): void
    {
        $referente = $this->referenteAgenzia(); // minimo di default (20 pezzi)
        $categoria = Category::firstOrCreate(['slug' => 'articoli-trigesimali'], ['name' => 'Articoli trigesimali']);
        $kit = Product::create([
            'category_id' => $categoria->id, 'sku' => 'TRG-TEST-'.uniqid(), 'slug' => 'trg-test-'.uniqid(),
            'name' => 'Kit di prova', 'price' => 4900, 'is_active' => true,
        ]);
        $crediti = $this->pacchettoCrediti();

        $this->conCarrello($referente, $kit, 5); // sotto il minimo di 20
        $this->conCarrello($referente, $crediti, 1);

        $this->actingAs($referente)->post('/ordine/conferma', array_merge(self::CONSEGNA, [
            'metodo_pagamento' => 'fattura',
        ]));

        $this->assertSame(0, Ordine::count(), 'i 5 pezzi del kit restano sotto il minimo, il credito non li completa');
    }

    public function test_comprare_piu_pacchetti_insieme_moltiplica_i_crediti(): void
    {
        $referente = $this->referenteSenzaMinimo();
        $prodotto = $this->pacchettoCrediti(crediti: 100);
        $this->conCarrello($referente, $prodotto, 3);

        $this->actingAs($referente)->post('/ordine/conferma', array_merge(self::CONSEGNA, [
            'metodo_pagamento' => 'fattura',
        ]));

        $this->assertSame(300, $referente->agenzia->fresh()->creditiSaldo());
    }

    public function test_il_saldo_e_la_somma_di_piu_acquisti(): void
    {
        $referente = $this->referenteSenzaMinimo();
        $prodotto = $this->pacchettoCrediti(crediti: 100);

        foreach (range(1, 2) as $volta) {
            $this->conCarrello($referente, $prodotto, 1);
            $this->actingAs($referente)->post('/ordine/conferma', array_merge(self::CONSEGNA, [
                'metodo_pagamento' => 'fattura',
            ]));
        }

        $this->assertSame(200, $referente->agenzia->fresh()->creditiSaldo());
        $this->assertSame(2, MovimentoCredito::count());
    }

    public function test_comprare_il_pacchetto_non_manda_l_ordine_in_lavorazione(): void
    {
        $referente = $this->referenteSenzaMinimo();
        $prodotto = $this->pacchettoCrediti();
        $this->conCarrello($referente, $prodotto, 1);

        $this->actingAs($referente)->post('/ordine/conferma', array_merge(self::CONSEGNA, [
            'metodo_pagamento' => 'fattura',
        ]));

        $ordine = Ordine::firstOrFail();
        $this->assertFalse($ordine->richiede_lavorazione, 'la ricarica crediti non e\' una pratica');
        $this->assertSame(StatoOrdine::InProduzione, $ordine->stato);
    }

    public function test_un_privato_non_accredita_nulla(): void
    {
        $privato = User::factory()->create();
        $prodotto = $this->pacchettoCrediti();
        $this->conCarrello($privato, $prodotto, 1);

        $this->actingAs($privato)->post('/ordine/conferma', array_merge(self::CONSEGNA, [
            'metodo_pagamento' => 'contrassegno',
        ]));

        $this->assertDatabaseCount('ordini', 1);
        $this->assertDatabaseCount('movimenti_credito', 0);
    }

    public function test_un_agenzia_non_vede_i_crediti_di_un_altra(): void
    {
        $miaAgenzia = $this->referenteSenzaMinimo(['ragione_sociale' => 'Onoranze Bianchi', 'partita_iva' => '00743110157']);
        $altraAgenzia = $this->referenteSenzaMinimo(['ragione_sociale' => 'Casa Funeraria Aurora S.n.c.', 'partita_iva' => '12485671007']);

        $prodotto = $this->pacchettoCrediti();
        $this->conCarrello($altraAgenzia, $prodotto, 1);
        $this->actingAs($altraAgenzia)->post('/ordine/conferma', array_merge(self::CONSEGNA, [
            'metodo_pagamento' => 'fattura',
        ]));

        $this->assertSame(0, $miaAgenzia->agenzia->fresh()->creditiSaldo());
        $this->assertSame(100, $altraAgenzia->agenzia->fresh()->creditiSaldo());
    }
}
