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
}
