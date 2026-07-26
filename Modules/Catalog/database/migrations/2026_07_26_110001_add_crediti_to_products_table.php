<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Quanti crediti dà l'acquisto di questo prodotto (pacchetti crediti per le
 * agenzie: accesso ai servizi editor senza comprare un kit fisico). `null`
 * per tutti i prodotti normali, che non hanno niente a che fare coi crediti.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('crediti')->nullable()->after('extra_unit_price');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('crediti');
        });
    }
};
