<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Quali servizi sono stati attivati su un ordine, col costo congelato al
 * momento dell'attivazione — stesso principio di `righe_ordine.prezzo`
 * rispetto al listino: se domani il costo del servizio cambia da
 * /gestione/servizi, questo ordine deve continuare a raccontare quanto è
 * costato davvero.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordine_servizi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ordine_id')->constrained('ordini')->cascadeOnDelete();
            $table->foreignId('servizio_editor_id')->constrained('servizi_editor');
            $table->unsignedInteger('costo_crediti');
            $table->timestamps();

            // Un doppio submit del form non deve raddoppiare il costo.
            $table->unique(['ordine_id', 'servizio_editor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordine_servizi');
    }
};
