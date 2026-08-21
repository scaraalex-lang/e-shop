<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Timestamp dell'ultimo dispatch del render, separato da `created_at`.
 *
 * Scoperto verificando a schermo la modifica di un video già pronto: il
 * timer di progresso.blade.php calcolava i minuti trascorsi da
 * `created_at`, che con l'update in-place (stesso record, non un nuovo
 * video) resta la data della PRIMA generazione — il timer partiva già a
 * "93:24" invece che da zero. Serve un campo che si aggiorna a ogni
 * dispatch (creazione o modifica), non solo alla creazione della riga.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('video_memoriali', function (Blueprint $table) {
            $table->timestamp('render_avviato_il')->nullable()->after('stato');
        });
    }

    public function down(): void
    {
        Schema::table('video_memoriali', function (Blueprint $table) {
            $table->dropColumn('render_avviato_il');
        });
    }
};
