<?php

namespace Modules\Commerce\Tests\Unit;

use Modules\Commerce\Enums\Occasione;
use Tests\TestCase;

class OccasioneTest extends TestCase
{
    public function test_riconosce_gli_sku_dei_servizi(): void
    {
        $this->assertSame(Occasione::Funerale, Occasione::daSku('SRV-NECRO-FUNERALE'));
        $this->assertSame(Occasione::Trigesimo, Occasione::daSku('SRV-NECRO-TRIGESIMO'));
        $this->assertSame(Occasione::Anniversario, Occasione::daSku('SRV-NECRO-ANNIVERSARIO'));
    }

    public function test_uno_sku_qualunque_non_e_un_occasione(): void
    {
        $this->assertNull(Occasione::daSku('COR-MET-ORO'));
        $this->assertNull(Occasione::daSku(''));
    }
}
