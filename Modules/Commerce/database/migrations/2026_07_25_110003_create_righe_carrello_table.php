<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Righe del carrello.
 *
 * Qui NON si salva nessun prezzo: il carrello è una lista di intenzioni, e il
 * costo si ricalcola a ogni vista con il listino del momento e le condizioni
 * dell'account. Il prezzo si cristallizza sull'ordine, non prima: se il listino
 * cambia mentre il carrello è fermo da tre giorni, chi compra deve vedere il
 * prezzo vero prima di confermare.
 *
 * Attenzione alla `quantita` dei kit: è il NUMERO DI PEZZI (ricordini), non il
 * numero di kit. Il prezzo base ne comprende `included_units` e ogni pezzo in
 * più si paga — vedi Product::priceForQuantity.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('righe_carrello', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrello_id')->constrained('carrelli')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            $table->unsignedInteger('quantita');

            $table->timestamps();

            // Lo stesso prodotto compare una volta sola: si somma la quantità.
            $table->unique(['carrello_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('righe_carrello');
    }
};
