<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Un defunto può avere più manifesti collegati (funerale, partecipazioni,
 * trigesimo...), non uno solo: sostituisce le colonne `necrologi.manifesto*`,
 * che restano sul necrologio ma deprecate (nessun drop, sono dati di
 * produzione già scritti — vedi backfill sotto).
 *
 * `web` è il JPEG qualità web generato automaticamente ad ogni salvataggio
 * (oltre al PDF di stampa): quello che oggi manca fra il PDF e la mini
 * anteprima a bassa risoluzione.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manifesti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('defunto_id')->constrained('defunti')->cascadeOnDelete();
            $table->string('etichetta');
            $table->string('formato');
            $table->json('canvas')->nullable();
            $table->string('pdf')->nullable();
            $table->string('web')->nullable();
            $table->boolean('principale')->default(false);
            $table->timestamps();

            $table->index(['defunto_id', 'principale']);
        });

        $this->backfillDaiNecrologiEsistenti();
    }

    /**
     * Ogni necrologio con un manifesto già composto diventa una riga
     * `manifesti` (principale) per il suo defunto. Idempotente: se la
     * tabella ha già righe non rifà nulla, così ri-eseguire la migration
     * (es. dopo un rollback di test) non duplica i dati.
     */
    private function backfillDaiNecrologiEsistenti(): void
    {
        if (DB::table('manifesti')->exists()) {
            return;
        }

        $ora = now();

        DB::table('necrologi')
            ->whereNotNull('manifesto_canvas')
            ->select('id', 'defunto_id', 'occasione', 'manifesto_canvas', 'manifesto_formato', 'manifesto')
            ->orderBy('id')
            ->each(function ($necrologio) use ($ora) {
                DB::table('manifesti')->insert([
                    'defunto_id' => $necrologio->defunto_id,
                    'etichetta' => 'Manifesto '.($necrologio->occasione ?: 'funerale'),
                    'formato' => $necrologio->manifesto_formato ?? 'a3l',
                    'canvas' => $necrologio->manifesto_canvas,
                    'pdf' => $necrologio->manifesto,
                    'web' => null,
                    'principale' => true,
                    'created_at' => $ora,
                    'updated_at' => $ora,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('manifesti');
    }
};
