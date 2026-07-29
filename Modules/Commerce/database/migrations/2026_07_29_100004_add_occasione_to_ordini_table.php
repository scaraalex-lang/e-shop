<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per cosa è l'ordine: funerale, trigesimo o anniversario (+ il numero, per
 * l'anniversario). Sostituisce come fonte di verità l'euristica per
 * categoria prodotto di `prossimaScadenzaSuggerita()`, che resta solo come
 * fallback per gli ordini vecchi senza questo campo. Nullable: un ordine
 * senza servizio (solo kit fisici) non ha un'occasione.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordini', function (Blueprint $table) {
            $table->string('occasione', 20)->nullable()->after('richiede_lavorazione');
            $table->unsignedTinyInteger('numero_anniversario')->nullable()->after('occasione');
        });
    }

    public function down(): void
    {
        Schema::table('ordini', function (Blueprint $table) {
            $table->dropColumn(['occasione', 'numero_anniversario']);
        });
    }
};
