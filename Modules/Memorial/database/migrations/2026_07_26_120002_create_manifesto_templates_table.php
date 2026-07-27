<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Layout di manifesto riusabili. `agenzia_id` nullable fin da subito (a
 * differenza di `ricordino_templates`, dove è arrivata dopo): null resta
 * disponibile per un domani "curato da noi", un id è l'archivio della
 * singola agenzia — stesso principio di `necrologio_card_templates`.
 *
 * Nessun `is_predefinito` per ora: nella sorgente originale il designer
 * manifesti parte da un canvas vuoto, non da layout curati — se servirà
 * un giorno, si aggiunge allora (vedi la stessa storia per i ricordini).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manifesto_templates', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('formato');
            $table->unsignedBigInteger('agenzia_id')->nullable()->index();
            $table->json('fronte')->nullable();
            $table->string('anteprima')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manifesto_templates');
    }
};
