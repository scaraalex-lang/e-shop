<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cosa c'è in ogni riquadro di una pagina: una foto + la sua didascalia.
 *
 * `slot` combacia con l'`ordine` dentro `videobook_page_templates.slots`: è
 * così che si sa quale (x, y, w, h) del layout usare per questa foto, sia in
 * stampa (jsPDF) sia nel video (una scena per foto, come da decisione presa:
 * lo zoom Ken Burns è per singola foto dentro la pagina, non sulla pagina
 * intera).
 *
 * `durata_secondi` riprende lo stesso campo di `foto_video_memoriale`: stessa
 * idea (quanto dura l'inquadratura di questa foto nel render), null = usa il
 * default globale del renderer.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videobook_foto', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pagina_id')
                ->constrained('videobook_pagine')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('slot');

            // Path relativo sul disco public: mai un URL assoluto.
            $table->string('path');
            $table->string('didascalia', 180)->nullable();
            $table->unsignedTinyInteger('durata_secondi')->nullable();

            $table->timestamps();

            $table->unique(['pagina_id', 'slot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videobook_foto');
    }
};
