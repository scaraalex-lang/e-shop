<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Il profilo ICC del laboratorio di stampa fotografica: caricato una volta
 * dallo staff (dashboard admin, vedi ImpostazioniController), da usare per
 * allineare il colore delle foto esportate a quello che il laboratorio
 * stamperà davvero — vedi Modules\VideoBook\Models\ProfiloColore.
 *
 * Ogni caricamento è una riga nuova, non un aggiornamento sul posto: la
 * storia di quale profilo era attivo quando resta, e ProfiloColore::attivo()
 * legge sempre l'ultimo. Il file vive sul disco `local` (non `public`): non
 * ha bisogno di essere raggiungibile da un URL pubblico, solo dal server
 * quando (in una fase successiva) la pipeline di export lo userà davvero.
 *
 * Solo il caricamento oggi: la conversione dei colori in fase di stampa non
 * è ancora collegata (richiede spostare parte della generazione del PDF lato
 * server, oggi interamente client — vedi PdfController), questa tabella è la
 * base su cui costruirla.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videobook_profili_colore', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->string('nome_originale');
            $table->foreignId('caricato_da')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videobook_profili_colore');
    }
};
