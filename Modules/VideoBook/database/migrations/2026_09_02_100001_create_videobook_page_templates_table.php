<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Un layout di pagina riusabile: quanti riquadri foto ci sono e dove, non
 * legato a nessun libro in particolare — stesso ruolo di `ricordino_templates`
 * per il Ricordino Designer, qui applicato a pagine multi-foto.
 *
 * `slots` è l'unico posto dove vive la geometria del layout: un array di
 * `{ordine, x, y, w, h}` in coordinate relative (0-1), così lo stesso template
 * si applica a qualunque formato fisico scelto per il libro. `numero_foto` è
 * la stessa informazione derivata da `count(slots)`, ma denormalizzata in una
 * colonna: è il criterio con cui l'utente filtra il selettore ("mi serve un
 * layout da 4 foto"), non ha senso spacchettare il JSON lato query per quello.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videobook_page_templates', function (Blueprint $table) {
            $table->id();

            $table->string('nome');
            $table->unsignedTinyInteger('numero_foto');
            $table->json('slots');

            // null = predefinito MemorAI (curato da noi, versionato nel
            // seeder); un id è l'archivio della singola agenzia, invisibile
            // alle altre — stesso pattern di `ricordino_templates.agenzia_id`.
            // Niente FK: `agenzie` sta nel modulo Commerce.
            $table->unsignedBigInteger('agenzia_id')->nullable()->index();

            $table->boolean('is_predefinito')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('anteprima')->nullable();

            $table->timestamps();

            $table->index(['is_predefinito', 'sort_order']);
            $table->index('numero_foto');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videobook_page_templates');
    }
};
