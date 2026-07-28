<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Niente split per il cimitero: su Google Places molti cimiteri hanno solo
 * la città come "formatted_address", senza via/civico — lo split lasciava
 * l'indirizzo con un dato inutile. Un solo campo con tutto il testo dentro.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('defunti', function (Blueprint $table) {
            $table->dropColumn('indirizzo_cimitero');
        });
    }

    public function down(): void
    {
        Schema::table('defunti', function (Blueprint $table) {
            $table->string('indirizzo_cimitero')->nullable()->after('cimitero');
        });
    }
};
