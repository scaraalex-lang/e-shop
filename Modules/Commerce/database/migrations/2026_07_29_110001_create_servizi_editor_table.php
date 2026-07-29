<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Il catalogo dei servizi editor attivabili a crediti: ricordini, manifesti,
 * necrologi. Righe fisse (niente create/delete da UI, solo costo e
 * attivo/non attivo modificabili da /gestione/servizi) — deliberatamente
 * SEPARATO da `products`: un servizio non è un prodotto Catalog, non ha
 * prezzo in euro, non passa dal carrello.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servizi_editor', function (Blueprint $table) {
            $table->id();
            $table->string('codice')->unique(); // ricordini | manifesti | necrologi
            $table->string('etichetta');
            $table->unsignedInteger('costo_crediti');
            $table->boolean('attivo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servizi_editor');
    }
};
