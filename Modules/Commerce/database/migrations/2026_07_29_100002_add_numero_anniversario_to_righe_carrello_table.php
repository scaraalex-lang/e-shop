<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Solo per la riga del servizio "Anniversario": quale anniversario è (1°,
 * 2°...). Non esiste un meccanismo di attributi-per-riga-carrello, quindi
 * questo è un campo dedicato, non un attributo generico.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('righe_carrello', function (Blueprint $table) {
            $table->unsignedTinyInteger('numero_anniversario')->nullable()->after('quantita');
        });
    }

    public function down(): void
    {
        Schema::table('righe_carrello', function (Blueprint $table) {
            $table->dropColumn('numero_anniversario');
        });
    }
};
