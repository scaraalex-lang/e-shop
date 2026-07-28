<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * I luoghi del funerale: dove si celebra e dove riposa. Stringhe semplici,
 * come trigesimo_luogo/trigesimo_indirizzo su Necrologio — l'autocomplete
 * Google Places compila il testo, non salva coordinate.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('defunti', function (Blueprint $table) {
            $table->string('luogo_funerale')->nullable()->after('preghiera');
            $table->string('indirizzo_funerale')->nullable()->after('luogo_funerale');
            $table->string('cimitero')->nullable()->after('indirizzo_funerale');
        });
    }

    public function down(): void
    {
        Schema::table('defunti', function (Blueprint $table) {
            $table->dropColumn(['luogo_funerale', 'indirizzo_funerale', 'cimitero']);
        });
    }
};
