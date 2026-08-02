<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Un ordine ha un solo defunto: LavorazioneController::salvaDefunto() riusa
 * sempre il defunto già legato all'ordine invece di crearne uno nuovo, ma
 * finora nulla lo impediva a livello di database. Lo unique è nullable: più
 * defunti senza ordine (demo, bozze) restano possibili.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('defunti', function (Blueprint $table) {
            $table->unique('ordine_id');
        });
    }

    public function down(): void
    {
        Schema::table('defunti', function (Blueprint $table) {
            $table->dropUnique(['ordine_id']);
        });
    }
};
