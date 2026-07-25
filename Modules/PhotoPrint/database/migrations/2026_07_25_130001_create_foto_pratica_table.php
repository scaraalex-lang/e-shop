<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Le fotografie di una lavorazione, legate all'ordine.
 *
 * Fino a qui le foto elaborate finivano su disco senza che nessuno se ne
 * ricordasse: bastava chiudere il browser per perderle. Questa tabella è il
 * registro — l'originale caricato dal cliente e le versioni elaborate,
 * ciascuna appesa all'ordine che le ha richieste.
 *
 * `ordine_id` senza vincolo, come `defunti.ordine_id`: gli ordini stanno nel
 * modulo Commerce e il legame nasce dopo, a lavorazione avviata.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('foto_pratica', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('ordine_id')->index();

            // Path relativo sul disco public: mai un URL assoluto, che con
            // APP_URL=http://localhost verrebbe sbagliato.
            $table->string('path');

            // originale | elaborata_ai | ritagliata
            $table->string('tipo', 30)->default('originale');
            $table->boolean('is_principale')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foto_pratica');
    }
};
