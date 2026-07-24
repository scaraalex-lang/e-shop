<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Galleria santi condivisa: immagini devozionali (santi, Sacro Cuore, ecc.)
 * inseribili nel retro del ricordino. Non legata a un singolo defunto.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('santi', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('path');          // path relativo sul disk 'public'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('santi');
    }
};
