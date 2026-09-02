<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rimossa la didascalia per foto: niente più spazio riservato sotto la foto
 * nel layout di stampa (i template riempiono l'intera pagina/il loro
 * riquadro, vedi PaginaTemplateSeeder) né testo sovraimpresso nel video
 * (GeneraVideoBook non manda più `testo` al proxy di render).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('videobook_foto', function (Blueprint $table) {
            $table->dropColumn('didascalia');
        });
    }

    public function down(): void
    {
        Schema::table('videobook_foto', function (Blueprint $table) {
            $table->string('didascalia', 180)->nullable();
        });
    }
};
