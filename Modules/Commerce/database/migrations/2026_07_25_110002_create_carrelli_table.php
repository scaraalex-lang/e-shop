<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Carrello persistente.
 *
 * Chi non ha ancora un account riempie il carrello lo stesso: la riga è legata
 * a un token in sessione (`token`), non a un utente. Al momento dell'accesso il
 * carrello dell'ospite viene fuso in quello dell'account e il token sparisce —
 * vedi UnisciCarrelloOspite.
 *
 * Sta a database e non solo in sessione perché il carrello di un'agenzia può
 * valere decine di pezzi montati in più riprese: perderlo con la sessione
 * sarebbe un danno vero.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carrelli', function (Blueprint $table) {
            $table->id();

            // Uno dei due è sempre valorizzato: l'utente se ha fatto accesso,
            // altrimenti il token dell'ospite.
            $table->foreignId('user_id')->nullable()->unique()->constrained()->cascadeOnDelete();
            $table->string('token', 64)->nullable()->unique();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carrelli');
    }
};
