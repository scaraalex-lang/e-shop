<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * La foto dell'hero in home era un altro SKU fisso nella route
 * (routes/web.php: 'COR-MET-ORO'), stessa fragilità già risolta una volta
 * per is_featured. Un solo prodotto alla volta può essere hero — lo staff
 * lo sceglie dal form prodotto, il controller si occupa di spegnere gli
 * altri quando ne attiva uno nuovo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_hero')->default(false)->after('is_featured');
        });

        DB::table('products')
            ->where('sku', 'COR-MET-ORO')
            ->update(['is_hero' => true]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('is_hero');
        });
    }
};
