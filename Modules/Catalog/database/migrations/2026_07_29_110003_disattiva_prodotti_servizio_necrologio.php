<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * I tre SKU SRV-NECRO-FUNERALE/TRIGESIMO/ANNIVERSARIO (occasione trattata
 * come servizio a sé, un solo servizio per ordine) sono superati dal nuovo
 * catalogo servizi (`servizi_editor`: ricordini/manifesti/necrologi,
 * selezione multipla, occasione solo etichetta). Disattivati, non
 * eliminati: potrebbero già comparire in ordini reali.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')
            ->whereIn('sku', ['SRV-NECRO-FUNERALE', 'SRV-NECRO-TRIGESIMO', 'SRV-NECRO-ANNIVERSARIO'])
            ->update(['is_active' => false]);
    }

    public function down(): void
    {
        DB::table('products')
            ->whereIn('sku', ['SRV-NECRO-FUNERALE', 'SRV-NECRO-TRIGESIMO', 'SRV-NECRO-ANNIVERSARIO'])
            ->update(['is_active' => true]);
    }
};
