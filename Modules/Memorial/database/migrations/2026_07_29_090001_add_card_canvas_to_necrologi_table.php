<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lo stato editabile della card, distinto dal PNG finale (`og_image`): senza
 * questo il Card Designer riparte sempre da un canvas vuoto (foto della
 * pratica + nome/date di default) invece di ritrovare quanto già disegnato,
 * lo stesso problema già risolto per il manifesto (`manifesto_canvas`) e i
 * ricordini (`ricordini.fronte`/`retro`).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('necrologi', function (Blueprint $table) {
            $table->json('card_canvas')->nullable()->after('og_image');
        });
    }

    public function down(): void
    {
        Schema::table('necrologi', function (Blueprint $table) {
            $table->dropColumn('card_canvas');
        });
    }
};
