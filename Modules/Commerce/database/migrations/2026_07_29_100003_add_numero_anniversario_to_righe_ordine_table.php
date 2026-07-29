<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Copia da righe_carrello.numero_anniversario al momento della conferma
 * (stesso principio delle altre colonne di riga_ordine: una fotografia di
 * quello che è stato comprato, non un rimando al carrello che nel frattempo
 * si è svuotato).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('righe_ordine', function (Blueprint $table) {
            $table->unsignedTinyInteger('numero_anniversario')->nullable()->after('quantita');
        });
    }

    public function down(): void
    {
        Schema::table('righe_ordine', function (Blueprint $table) {
            $table->dropColumn('numero_anniversario');
        });
    }
};
