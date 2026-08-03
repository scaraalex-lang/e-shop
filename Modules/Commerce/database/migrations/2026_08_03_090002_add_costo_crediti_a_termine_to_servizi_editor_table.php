<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Prezzo della modalità "a termine" (scadenza scelta dall'agenzia, max 6
 * mesi): il `costo_crediti` esistente diventa il prezzo "perpetuo". Colonna
 * generica ma oggi valorizzata solo sulla riga `embed` — le altre righe
 * (ricordini/manifesti/necrologi) non hanno un concetto di durata, restano
 * NULL.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servizi_editor', function (Blueprint $table) {
            $table->unsignedInteger('costo_crediti_a_termine')->nullable()->after('costo_crediti');
        });
    }

    public function down(): void
    {
        Schema::table('servizi_editor', function (Blueprint $table) {
            $table->dropColumn('costo_crediti_a_termine');
        });
    }
};
