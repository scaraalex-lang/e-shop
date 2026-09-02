<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Il PDF pronto stampa delle pagine popolate, per il prodotto "Fotoalbum
 * VideoBook" — generato lato client (canvas + jsPDF, come Ricordino
 * Designer) e caricato qui, non renderizzato dal proxy Python: a differenza
 * del video non serve ffmpeg, è overlay di immagini su un foglio, il
 * browser lo fa da solo in pochi secondi.
 *
 * Vive sul libro (`videobook_progetti`), non su una tabella a parte come il
 * video: qui non c'è uno stato di coda/elaborazione da tracciare, il PDF o
 * c'è o non c'è ancora.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('videobook_progetti', function (Blueprint $table) {
            $table->string('pdf_path')->nullable()->after('stato');
        });
    }

    public function down(): void
    {
        Schema::table('videobook_progetti', function (Blueprint $table) {
            $table->dropColumn('pdf_path');
        });
    }
};
