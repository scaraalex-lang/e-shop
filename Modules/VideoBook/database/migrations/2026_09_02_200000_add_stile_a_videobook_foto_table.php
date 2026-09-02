<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Formattazione della foto e della sua didascalia: font/dimensione/
 * allineamento/grassetto/sottolineato/corsivo/colore del testo, più bordino,
 * regolazione (luminosità/contrasto/saturazione) e viraggio della foto —
 * tutto quello che il pannello "Strumenti" (editor.blade.php) può cambiare
 * oltre a ritaglio e posizione (che restano su scala/pos_x/pos_y).
 *
 * Un'unica colonna JSON invece di una colonna per campo: stesso approccio
 * ibrido di `products.attributes` (vedi CLAUDE.md) — sono tutte proprietà
 * cosmetiche, mai un criterio di filtro/query, e l'insieme è destinato a
 * crescere via via che il pannello Strumenti aggiunge funzioni (vedi
 * FotoPagina::stileEffettivo() per i default quando una chiave manca).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('videobook_foto', function (Blueprint $table) {
            $table->json('stile')->nullable()->after('durata_secondi');
        });
    }

    public function down(): void
    {
        Schema::table('videobook_foto', function (Blueprint $table) {
            $table->dropColumn('stile');
        });
    }
};
