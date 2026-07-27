<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Il kit componibile è un Product come un altro (`is_kit` resta il kit "a
 * soglia" del trigesimo, un concetto diverso): questo flag distingue solo
 * chi ha una composizione da mostrare (vedi kit_componenti).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_componibile')->default(false)->after('is_kit');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('is_componibile');
        });
    }
};
