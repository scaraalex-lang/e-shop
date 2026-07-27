<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lo stato editabile del manifesto, distinto dal file finale (`manifesto`,
 * che resta il PDF/immagine scaricabile — oggi caricato a mano, ora anche
 * prodotto dal designer). Senza questo, riaprire il designer non
 * ritroverebbe mai il lavoro fatto: si ricomincerebbe sempre da un foglio
 * bianco, come per `ricordini.fronte`/`retro`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('necrologi', function (Blueprint $table) {
            $table->json('manifesto_canvas')->nullable()->after('manifesto');
            $table->string('manifesto_formato')->nullable()->after('manifesto_canvas');
        });
    }

    public function down(): void
    {
        Schema::table('necrologi', function (Blueprint $table) {
            $table->dropColumn(['manifesto_canvas', 'manifesto_formato']);
        });
    }
};
