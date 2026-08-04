<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Città e sigla provincia della cerimonia (unica per tutto il percorso:
 * partenza, chiesa e cimitero sono quasi sempre nello stesso comune) — prima
 * non esistevano come dati propri, solo dentro le stringhe libere degli
 * indirizzi raccolti da Google Places. Servono separati per comporre il
 * testo del manifesto nel formato editoriale ("Via Roma 1, Boscoreale, NA"),
 * che Google restituisce già scomposto in address_components.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('defunti', function (Blueprint $table) {
            $table->string('citta')->nullable()->after('cimitero');
            $table->string('provincia', 2)->nullable()->after('citta');
        });
    }

    public function down(): void
    {
        Schema::table('defunti', function (Blueprint $table) {
            $table->dropColumn(['citta', 'provincia']);
        });
    }
};
