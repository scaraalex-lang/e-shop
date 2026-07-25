<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agenzia di onoranze funebri: il cliente B2B.
 *
 * Tabella separata da `users` di proposito. L'utente è l'identità che fa
 * login; l'agenzia è il soggetto commerciale (P.IVA, sede, stato di
 * approvazione, condizioni) a cui appartengono ordini, template del Designer
 * e bozze. Oggi il rapporto è uno-a-uno — un solo login per agenzia, garantito
 * dall'indice unique su `users.agenzia_id` — ma se un giorno servirà dare
 * l'accesso anche ai colleghi basta togliere quell'unique: il modello regge già.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agenzie', function (Blueprint $table) {
            $table->id();

            // Anagrafica commerciale
            $table->string('ragione_sociale');
            $table->string('partita_iva', 11)->unique();
            $table->string('codice_fiscale', 16)->nullable();

            // Fatturazione: fase 1 solo raccolta dati, SdI in fase 2
            $table->string('codice_sdi', 7)->nullable();
            $table->string('pec')->nullable();

            // Sede: è anche l'indirizzo di consegna B2B (l'agenzia
            // poi consegna alla famiglia).
            $table->string('indirizzo');
            $table->string('cap', 5);
            $table->string('citta');
            $table->string('provincia', 2);
            $table->string('nazione', 2)->default('IT');
            $table->string('telefono');

            // Approvazione manuale: nessuno sconto finché non è approvata.
            $table->string('stato', 20)->default('in_attesa')->index();
            $table->timestamp('stato_aggiornato_at')->nullable();
            $table->foreignId('stato_aggiornato_da')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->text('motivo_rifiuto')->nullable();   // mostrato all'agenzia
            $table->text('note_interne')->nullable();     // solo staff

            // Condizioni commerciali. Il minimo d'ordine B2B è in NUMERO PEZZI
            // (regola di business), non in euro; null = si applica il default
            // di configurazione. Gli sconti a scaglioni stanno sul prodotto,
            // non qui.
            $table->unsignedInteger('ordine_minimo_pezzi')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agenzie');
    }
};
