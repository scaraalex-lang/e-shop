<?php

namespace Modules\Memorial\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Memorial\Models\Defunto;
use Tests\TestCase;

class DefuntoTest extends TestCase
{
    use RefreshDatabase;

    public function test_coniuga_al_femminile(): void
    {
        $defunto = Defunto::create(['nome' => 'Luigia', 'cognome' => 'Rossetti', 'sesso' => 'F']);

        $this->assertSame('è venuta a mancare', $defunto->eVenutoAMancare());
    }

    public function test_coniuga_al_maschile(): void
    {
        $defunto = Defunto::create(['nome' => 'Mario', 'cognome' => 'Bianchi', 'sesso' => 'M']);

        $this->assertSame('è venuto a mancare', $defunto->eVenutoAMancare());
    }

    /** Senza sesso registrato (defunti creati prima che il campo esistesse), resta al maschile. */
    public function test_senza_sesso_resta_al_maschile(): void
    {
        $defunto = Defunto::create(['nome' => 'Mario', 'cognome' => 'Bianchi']);

        $this->assertSame('è venuto a mancare', $defunto->eVenutoAMancare());
    }

    /**
     * Nessun form fa mai digitare l'età a mano: si calcola da nascita e
     * decesso, altrimenti il blocco "Età" del ricordino/manifesto designer
     * mostra sempre il segnaposto "di anni ___" anche con le date compilate.
     */
    public function test_l_eta_si_calcola_da_nascita_e_decesso(): void
    {
        $defunto = Defunto::create([
            'nome' => 'Luca', 'cognome' => 'Rossi',
            'data_nascita' => '1939-01-20',
            'data_morte' => '2026-07-23',
        ]);

        $this->assertSame(87, $defunto->eta());
        $this->assertSame(87, $defunto->toPraticaData()['anni']);
    }

    public function test_senza_una_delle_due_date_non_calcola_l_eta(): void
    {
        $defunto = Defunto::create(['nome' => 'Luca', 'cognome' => 'Rossi', 'data_nascita' => '1939-01-20']);

        $this->assertNull($defunto->eta());
    }

    /** La colonna `anni` resta una riserva se un giorno serve un'età dichiarata senza date precise. */
    public function test_senza_date_ripiega_sulla_colonna_anni(): void
    {
        $defunto = Defunto::create(['nome' => 'Luca', 'cognome' => 'Rossi', 'anni' => 90]);

        $this->assertSame(90, $defunto->eta());
    }
}
