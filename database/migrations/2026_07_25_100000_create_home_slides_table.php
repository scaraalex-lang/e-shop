<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Slide del carosello di home page.
 *
 * Contenuto della vetrina, non del catalogo: sta a livello applicazione perché
 * non appartiene a nessun modulo. Ogni slide è una porta d'ingresso a una
 * sezione o a un flusso (prenota ricordini, prenota photoceramica), e si
 * gestisce dalla dashboard operativa senza toccare il codice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_slides', function (Blueprint $table) {
            $table->id();

            $table->string('occhiello')->nullable();   // sopratitolo piccolo maiuscolo
            $table->string('titolo');
            $table->string('titolo_corsivo')->nullable(); // coda del titolo in corsivo oro
            $table->text('testo')->nullable();

            // path relativo dentro storage/app/public (es. "categories/rosari.jpg")
            $table->string('immagine')->nullable();
            $table->string('immagine_alt')->nullable();

            // azione principale e secondaria (href assoluto o path interno)
            $table->string('cta_label')->nullable();
            $table->string('cta_href')->nullable();
            $table->string('cta2_label')->nullable();
            $table->string('cta2_href')->nullable();

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_slides');
    }
};
