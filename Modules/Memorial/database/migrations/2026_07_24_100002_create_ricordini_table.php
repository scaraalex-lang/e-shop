<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ricordino (santino) legato a un defunto: stato del canvas fronte/retro,
 * formato e stato di lavorazione. È il legame defunto ↔ ricordino.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ricordini', function (Blueprint $table) {
            $table->id();
            $table->foreignId('defunto_id')
                  ->constrained('defunti')->cascadeOnDelete();

            $table->string('formato')->default('7x10');   // 7x10 | 6x9
            $table->json('fronte')->nullable();            // stato canvas fronte
            $table->json('retro')->nullable();             // stato canvas retro
            $table->string('stato')->default('bozza');     // bozza | in_approvazione | approvato

            $table->string('anteprima_fronte')->nullable(); // path PNG anteprima
            $table->string('anteprima_retro')->nullable();

            $table->timestamps();

            $table->index(['defunto_id', 'stato']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ricordini');
    }
};
