<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * L'URL pubblico del necrologio (per social/WhatsApp) è incluso nel servizio
 * "necrologi". Incorporarlo in un iframe sul sito proprio dell'agenzia è
 * un livello a parte, a pagamento: questo flag lo accende — vedi
 * Necrologio::abilitaEmbed()/embeddabile() e NecrologiController::acquistaEmbed().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('necrologi', function (Blueprint $table) {
            $table->boolean('embed_abilitato')->default(false)->after('pubblicato_fino_al');
        });
    }

    public function down(): void
    {
        Schema::table('necrologi', function (Blueprint $table) {
            $table->dropColumn('embed_abilitato');
        });
    }
};
