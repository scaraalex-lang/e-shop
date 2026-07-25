<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Il necrologio: la card che l'agenzia condivide su WhatsApp e Facebook,
 * con l'orario del trigesimo e il manifesto allegato.
 *
 * DUE CONSENSI, NON UNO. Su `defunti` c'è già il consenso all'uso di
 * immagine e dati per realizzare gli articoli: quella foto finisce su un
 * ricordino che resta in famiglia. Pubblicare online è un'altra cosa —
 * nome, ritratto e date esposti a chiunque abbia l'indirizzo — e va
 * autorizzata a parte, da un familiare che si nomina. Riusare il primo
 * consenso per fare il secondo sarebbe sbagliato.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('necrologi', function (Blueprint $table) {
            $table->id();

            $table->foreignId('defunto_id')->constrained('defunti')->cascadeOnDelete();

            // L'agenzia che pubblica. Nessuna FK: `agenzie` sta in Commerce e
            // Memorial non deve dipenderne, come per `defunti.ordine_id`.
            $table->unsignedBigInteger('agenzia_id')->index();

            /*
             * L'indirizzo pubblico: /ricordi/{agenzia}/{percorso}
             * Il percorso è "luigia-rossetti-a7f3": si legge e si condivide,
             * ma il codice in coda impedisce di indovinare gli altri
             * cambiando il nome — e rende un vicolo cieco l'indirizzo di una
             * pagina ritirata.
             */
            $table->string('percorso');

            // Il trigesimo: è l'informazione per cui la card esiste.
            $table->dateTime('trigesimo_at')->nullable();
            $table->string('trigesimo_luogo')->nullable();
            $table->string('trigesimo_indirizzo')->nullable();

            $table->text('testo')->nullable();       // poche righe di annuncio

            // L'immagine dell'anteprima social: WhatsApp e Facebook non
            // eseguono JavaScript, prendono un file da un indirizzo.
            $table->string('og_image')->nullable();
            // Il manifesto, caricato dall'agenzia.
            $table->string('manifesto')->nullable();

            // ---- consenso alla PUBBLICAZIONE (distinto da quello sul defunto)
            $table->boolean('pubblicazione_consenso')->default(false);
            $table->string('pubblicazione_autorizzata_da')->nullable();
            $table->string('pubblicazione_parentela')->nullable();
            $table->timestamp('pubblicazione_autorizzata_at')->nullable();

            // ---- interruttore e scadenza
            $table->boolean('pubblicato')->default(false);
            // Spegnimento automatico: un necrologio serve nei giorni intorno
            // al funerale e al trigesimo, non per sempre.
            $table->date('pubblicato_fino_al')->nullable();

            $table->timestamps();

            $table->unique(['agenzia_id', 'percorso']);
            $table->index(['pubblicato', 'pubblicato_fino_al']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('necrologi');
    }
};
