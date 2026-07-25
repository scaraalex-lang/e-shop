<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sconti a scaglioni di quantità, applicati sul singolo prodotto.
 *
 * Regola di business: il prezzo pubblico è uguale per tutti: le agenzie non
 * hanno un listino separato, hanno uno sconto percentuale che scatta oltre una
 * certa quantità e solo se l'account è approvato.
 *
 * La percentuale è `decimal`, non un intero di centesimi: non è denaro, è un
 * coefficiente. Il calcolo però resta in aritmetica intera sui centesimi —
 * vedi Listino.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scaglioni_prezzo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            // Da quanti pezzi in su scatta lo scaglione.
            $table->unsignedInteger('quantita_minima');
            $table->decimal('sconto_percentuale', 5, 2);

            $table->timestamps();

            // Un solo sconto per prodotto a parità di soglia.
            $table->unique(['product_id', 'quantita_minima']);
            $table->index(['product_id', 'quantita_minima'], 'scaglioni_ricerca');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scaglioni_prezzo');
    }
};
