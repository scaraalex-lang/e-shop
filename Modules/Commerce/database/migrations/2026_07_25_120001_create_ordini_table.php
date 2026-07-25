<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * L'ordine: il contenitore a cui si agganciano defunto, foto e ricordino.
 *
 * I dati di consegna sono copiati qui e non letti dall'account: un indirizzo
 * cambiato dopo non deve riscrivere dove è stato spedito un pacco di sei mesi
 * fa. Vale lo stesso principio dei prezzi sulle righe.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordini', function (Blueprint $table) {
            $table->id();

            // Numero leggibile, quello che il cliente cita al telefono.
            $table->string('numero', 20)->unique();

            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            // Copia dell'agenzia al momento dell'ordine: serve allo storico
            // anche se il referente un domani cambia account.
            $table->foreignId('agenzia_id')->nullable()->constrained('agenzie')->nullOnDelete();

            $table->string('stato', 30)->default('nuovo')->index();

            // Pagamento
            $table->string('metodo_pagamento', 20);
            $table->string('stato_pagamento', 20)->default('in_attesa')->index();
            $table->timestamp('pagato_at')->nullable();
            // Riferimento della transazione: oggi finto, domani quello di Stripe.
            $table->string('riferimento_pagamento')->nullable();

            // Importi, tutti in centesimi
            $table->unsignedInteger('totale_pieno');   // a listino pubblico
            $table->unsignedInteger('totale_merce');   // dopo gli sconti
            $table->unsignedInteger('spedizione')->default(0);
            $table->unsignedInteger('totale');         // merce + spedizione

            // Consegna (per il B2B è la sede dell'agenzia: si spedisce a loro,
            // sono loro a consegnare alla famiglia)
            $table->string('consegna_nome');
            $table->string('consegna_telefono');
            $table->string('consegna_indirizzo');
            $table->string('consegna_cap', 5);
            $table->string('consegna_citta');
            $table->string('consegna_provincia', 2);
            $table->text('note')->nullable();

            /*
             * Vero se l'ordine contiene articoli da personalizzare con la
             * fotografia: è il flag che apre la lavorazione (Foto Manager e
             * Designer) a chi ha comprato, e che aggiunge la tappa
             * "in lavorazione" al tracciamento.
             */
            $table->boolean('richiede_lavorazione')->default(false);

            // Il defunto della pratica, quando la lavorazione è iniziata.
            // Niente vincolo: `defunti` sta nel modulo Memorial e il legame
            // nasce dopo l'ordine, non insieme.
            $table->unsignedBigInteger('defunto_id')->nullable()->index();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordini');
    }
};
