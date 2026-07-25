<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Righe d'ordine: qui il prezzo si cristallizza.
 *
 * A differenza del carrello, che ricalcola sempre, la riga d'ordine porta una
 * copia di nome, codice e importi al momento della conferma. Se domani il
 * listino cambia, o il prodotto viene ritirato, un ordine di ieri deve
 * continuare a raccontare quello che è stato pagato davvero.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('righe_ordine', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ordine_id')->constrained('ordini')->cascadeOnDelete();

            // Il prodotto resta collegato per comodità, ma non è la fonte di
            // verità della riga: quella è la copia qui sotto.
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();

            $table->string('sku');
            $table->string('nome');
            $table->unsignedInteger('quantita');

            $table->unsignedInteger('prezzo_pieno');   // a listino pubblico
            $table->unsignedInteger('prezzo');         // effettivamente dovuto
            $table->decimal('sconto_percentuale', 5, 2)->nullable();

            // Copia del flag: se domani il prodotto smette di essere
            // personalizzabile, questa riga resta da lavorare.
            $table->boolean('richiede_foto')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('righe_ordine');
    }
};
