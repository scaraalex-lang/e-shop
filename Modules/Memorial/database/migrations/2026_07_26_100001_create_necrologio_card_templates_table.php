<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Template PNG per il card designer del necrologio: sfondi riusabili che
 * un'agenzia carica una volta e riapplica a ogni card.
 *
 * Scoping semplice per ora: ogni agenzia vede solo i propri, punto. Se in
 * futuro arriveranno template MemorAI curati da noi (decisione ancora aperta
 * col committente), si aggiungerà una colonna proprietario nullable come per
 * `ricordino_templates` — non anticipata qui perché non ancora decisa.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('necrologio_card_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agenzia_id')->index();
            $table->string('nome');
            $table->string('path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('necrologio_card_templates');
    }
};
