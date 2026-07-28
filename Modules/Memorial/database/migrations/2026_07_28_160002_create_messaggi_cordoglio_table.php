<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Messaggi di cordoglio pubblici, lasciati senza account sulla pagina del
 * manifesto (il funerale, non il trigesimo): solo nome e testo, niente
 * contatto — non serve altro per lasciare un pensiero.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messaggi_cordoglio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('necrologio_id')->constrained('necrologi')->cascadeOnDelete();
            $table->string('nome');
            $table->text('messaggio');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messaggi_cordoglio');
    }
};
