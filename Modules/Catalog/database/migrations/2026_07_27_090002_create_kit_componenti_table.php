<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La composizione di un kit componibile: quali articoli contiene e quanti.
 * È informativa (mostra allo staff e al cliente cosa c'è dentro), non
 * scarica il magazzino dei componenti — solo lo stock del kit stesso, se e
 * quando verrà scalato (oggi lo stock non decrementa da nessuna parte).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kit_componenti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kit_product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('componente_product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('quantita')->default(1);
            $table->timestamps();

            $table->unique(['kit_product_id', 'componente_product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kit_componenti');
    }
};
