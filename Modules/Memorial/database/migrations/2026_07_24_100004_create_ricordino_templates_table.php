<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Template di ricordino: un LAYOUT riusabile, non legato a un defunto.
 *
 * I blocchi personali (nome, date, frase, preghiera) sono salvati con il testo
 * segnaposto e la foto del defunto è esclusa: applicando il template a un altro
 * defunto i suoi dati vengono riempiti dal designer, e i dati di una persona
 * non finiscono nel ricordino di un'altra.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ricordino_templates', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('formato')->default('7x10');   // 7x10 | 6x9
            $table->json('fronte')->nullable();            // stato canvas fronte
            $table->json('retro')->nullable();             // stato canvas retro
            $table->string('anteprima')->nullable();       // path JPG sul disk 'public'
            $table->timestamps();

            $table->index('formato');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ricordino_templates');
    }
};
