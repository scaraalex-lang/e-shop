<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Box di testo liberi su una pagina: non legati a un riquadro foto come le
 * didascalie di FotoPagina — un titolo, una citazione, una data, disegnati
 * sopra la pagina (spesso sopra una foto, con uno sfondo semi-trasparente
 * per restare leggibili — "tipo Tipografia").
 *
 * `x`/`y`/`w`/`h` in coordinate relative (0-1) esattamente come gli slot di
 * PaginaTemplate: posizionabile e ridimensionabile liberamente dall'utente
 * (drag/maniglia in editor.blade.php), non vincolato al layout del template.
 *
 * `stile` ha la stessa forma di `videobook_foto.stile` (vedi quella
 * migration) più `sfondo_colore`/`sfondo_opacita` per il box semi-
 * trasparente — un solo modulo di formattazione testo condiviso fra
 * didascalie e box liberi, vedi TestoPagina::stileEffettivo().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videobook_testi', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pagina_id')
                ->constrained('videobook_pagine')
                ->cascadeOnDelete();

            $table->decimal('x', 5, 4);
            $table->decimal('y', 5, 4);
            $table->decimal('w', 5, 4);
            $table->decimal('h', 5, 4);

            $table->string('testo', 500)->nullable();
            $table->json('stile')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videobook_testi');
    }
};
