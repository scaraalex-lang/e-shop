<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * "In evidenza" in home era una whitelist di SKU scritta nella route
 * (routes/web.php), il modo più fragile di curare la vetrina: cambiare cosa
 * appare in home richiedeva una modifica al codice sorgente. Questa
 * migration marca is_featured=true sugli stessi 3 SKU di oggi — stessa home,
 * zero regressione — poi lo staff sceglie dal form prodotto.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('is_active');
        });

        DB::table('products')
            ->whereIn('sku', ['COR-PRL-CHA', 'COR-VTR-ROS', 'ROS-BRC-CRI-BLU'])
            ->update(['is_featured' => true]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('is_featured');
        });
    }
};
