<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Come la foto è inquadrata dentro il suo riquadro: finora sempre lo stesso
 * ritaglio automatico (object-fit: cover, centrato) — ora l'utente può
 * spostare e ingrandire l'immagine dentro la cornice (drag per posizionare,
 * maniglia per zoomare), quindi il ritaglio va salvato per riga invece di
 * essere ricalcolato sempre uguale.
 *
 * `scala`: 1.0 = il minimo che copre il riquadro senza margini vuoti (base
 * "cover"), >1.0 ingrandisce (più ritaglio), <1.0 rimpicciolisce (la foto
 * non riempie più tutta la cornice, resta centrata sul margine che avanza).
 * `pos_x`/`pos_y`: 0..1, dove nel margine di scorrimento residuo (l'immagine
 * ingrandita meno la cornice) sta il punto mostrato — 0.5/0.5 = centrato,
 * uguale al comportamento di prima; ignorati quando la foto è rimpicciolita
 * (non c'è margine da scorrere, solo da centrare).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('videobook_foto', function (Blueprint $table) {
            $table->decimal('scala', 4, 2)->default(1)->after('path');
            $table->decimal('pos_x', 4, 3)->default(0.5)->after('scala');
            $table->decimal('pos_y', 4, 3)->default(0.5)->after('pos_x');
        });
    }

    public function down(): void
    {
        Schema::table('videobook_foto', function (Blueprint $table) {
            $table->dropColumn(['scala', 'pos_x', 'pos_y']);
        });
    }
};
