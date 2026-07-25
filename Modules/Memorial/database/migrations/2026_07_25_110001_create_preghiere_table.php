<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Archivio dei testi di preghiera.
 *
 * Serve al Designer Smart: da telefono nessuno scrive a mano una preghiera,
 * la sceglie da una galleria. I testi sono di dominio liturgico comune e si
 * gestiscono dalla dashboard operativa.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preghiere', function (Blueprint $table) {
            $table->id();

            $table->string('titolo');
            $table->text('testo');
            // raggruppamento per la galleria: "Preghiere", "Salmi", "Frasi brevi"…
            $table->string('categoria')->nullable();

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preghiere');
    }
};
