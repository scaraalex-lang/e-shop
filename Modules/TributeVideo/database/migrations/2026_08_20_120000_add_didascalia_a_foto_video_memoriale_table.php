<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Didascalia e proprietà per singola foto, per l'editor timeline.
 *
 * Colonne scalari piccole, non un blob JSON: i dati sono per-foto e questa è
 * già la tabella relazionale giusta (FK vera, `ordine` indicizzato). Nessuna
 * di queste colonne entra mai in un ORDER BY (si ordina solo per `ordine`).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('foto_video_memoriale', function (Blueprint $table) {
            $table->string('testo', 180)->nullable()->after('ordine');
            // alto | centro | basso — null se non c'è didascalia.
            $table->string('testo_posizione', 10)->nullable()->after('testo');
            // null = usa il default globale del renderer (4.5s).
            $table->unsignedTinyInteger('durata_secondi')->nullable()->after('testo_posizione');
            // Ken Burns on/off. Default true: comportamento attuale invariato.
            $table->boolean('zoom_attivo')->default(true)->after('durata_secondi');
        });
    }

    public function down(): void
    {
        Schema::table('foto_video_memoriale', function (Blueprint $table) {
            $table->dropColumn(['testo', 'testo_posizione', 'durata_secondi', 'zoom_attivo']);
        });
    }
};
