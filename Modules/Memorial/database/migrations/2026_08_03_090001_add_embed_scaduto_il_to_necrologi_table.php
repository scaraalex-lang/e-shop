<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * L'embed ora ha una scadenza tecnica propria (vedi Necrologio::abilitaEmbed):
 * la data scelta se acquistato "a termine", oppure data-acquisto+3 anni se
 * "perpetuo" — non è mai più davvero infinito. NULL sulle righe esistenti
 * (embed comprato prima di questa modifica): restano validi per sempre,
 * nessun backfill necessario.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('necrologi', function (Blueprint $table) {
            $table->date('embed_scaduto_il')->nullable()->after('embed_abilitato');
            $table->string('embed_tipo')->nullable()->after('embed_scaduto_il'); // termine | perpetuo
        });
    }

    public function down(): void
    {
        Schema::table('necrologi', function (Blueprint $table) {
            $table->dropColumn(['embed_scaduto_il', 'embed_tipo']);
        });
    }
};
