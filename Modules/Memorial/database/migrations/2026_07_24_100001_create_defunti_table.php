<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Entità defunto: dati commemorativi che precompilano il ricordino e, in
 * Fase 2, si agganciano all'ordine trigesimo. Include il consenso GDPR
 * (in-app) registrato da chi gestisce la pratica.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('defunti', function (Blueprint $table) {
            $table->id();

            // Dati commemorativi (precompilano i blocchi testo del ricordino).
            $table->string('nome');
            $table->string('cognome');
            $table->date('data_nascita')->nullable();
            $table->date('data_morte')->nullable();
            $table->unsignedSmallInteger('anni')->nullable();
            $table->string('frase')->nullable();      // es. "È mancata all'affetto dei suoi cari"
            $table->text('preghiera')->nullable();     // preghiera / dedica sul retro

            // Consenso GDPR (in-app): chi autorizza l'uso di immagine e dati.
            $table->boolean('gdpr_consenso')->default(false);
            $table->string('gdpr_autorizzato_da')->nullable();   // nome di chi autorizza
            $table->string('gdpr_parentela')->nullable();        // relazione col defunto
            $table->timestamp('gdpr_autorizzato_at')->nullable();
            $table->text('gdpr_note')->nullable();

            // Aggancio futuro all'ordine (modulo Commerce, Fase 2). Nessuna FK ora.
            $table->unsignedBigInteger('ordine_id')->nullable()->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('defunti');
    }
};
