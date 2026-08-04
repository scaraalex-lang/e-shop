<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Una volta impostata la foto principale della pratica, il Foto Manager si
 * blocca (sola visualizzazione): evita che l'operatore continui a rielaborare
 * o sostituire la foto dopo aver chiuso il primo passo della Scheda Defunto.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordini', function (Blueprint $table) {
            $table->boolean('foto_bloccata')->default(false)->after('richiede_lavorazione');
        });
    }

    public function down(): void
    {
        Schema::table('ordini', function (Blueprint $table) {
            $table->dropColumn('foto_bloccata');
        });
    }
};
