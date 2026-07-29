<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `crediti` era unsigned: bastava per "quanti crediti dà l'acquisto" (sempre
 * positivo). I servizi per il necrologio (funerale/trigesimo/anniversario)
 * costano crediti invece di guadagnarli: serve poterlo esprimere negativo,
 * cosa che `CreaOrdine::da()` già gestisce da sola (usa il segno di
 * `crediti` così com'è per generare il MovimentoCredito).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('crediti')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('crediti')->nullable()->change();
        });
    }
};
