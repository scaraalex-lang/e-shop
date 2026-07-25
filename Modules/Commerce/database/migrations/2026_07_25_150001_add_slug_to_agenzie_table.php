<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Lo spicchio di indirizzo pubblico dell'agenzia.
 *
 * Serve agli indirizzi dei necrologi: `/ricordi/onoranze-bianchi/...`.
 * Mettere il nome dell'agenzia nel percorso non è un dettaglio tecnico, è la
 * loro vetrina: ogni necrologio condiviso porta in giro il loro nome.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agenzie', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('ragione_sociale');
        });

        // Le agenzie già registrate ne prendono uno derivato dalla ragione
        // sociale; le collisioni si risolvono con la città.
        foreach (DB::table('agenzie')->select('id', 'ragione_sociale', 'citta')->get() as $agenzia) {
            $base = Str::slug($agenzia->ragione_sociale) ?: 'agenzia';
            $slug = $base;

            if (DB::table('agenzie')->where('slug', $slug)->exists()) {
                $slug = Str::slug($base.'-'.$agenzia->citta);
            }

            DB::table('agenzie')->where('id', $agenzia->id)->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('agenzie', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
