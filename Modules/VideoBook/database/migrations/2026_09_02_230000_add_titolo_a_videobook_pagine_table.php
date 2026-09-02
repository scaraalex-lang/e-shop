<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Un'etichetta libera per la pagina, modificabile in sidebar
 * (editor.blade.php): senza, la card mostra il nome del template come
 * placeholder ("Due foto affiancate") — non è granché per orientarsi in un
 * libro lungo. Con un titolo proprio ("Chiesa", "Ricevimento") la lista
 * pagine diventa un indice leggibile, non solo l'elenco dei layout usati.
 *
 * Nullable: senza titolo, il frontend ricade sul nome del template (vedi
 * PaginaApiController::paginaPerFrontend()).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('videobook_pagine', function (Blueprint $table) {
            $table->string('titolo', 60)->nullable()->after('template_id');
        });
    }

    public function down(): void
    {
        Schema::table('videobook_pagine', function (Blueprint $table) {
            $table->dropColumn('titolo');
        });
    }
};
