<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Il video renderizzato di un libro: un'entità a sé, non uno stato su
 * `videobook_progetti` — stesso principio per cui [[StatoLibro]] non
 * contiene stati di render ("quello vivrà sul singolo export, come `stato`
 * su video_memoriali"). Un libro può essere rigenerato: qui c'è sempre
 * l'ultimo risultato (coda/elaborazione/pronto/errore), non uno storico.
 *
 * FK vera e unica verso `videobook_progetti`: stesso modulo, un solo video
 * per libro (a differenza di `video_memoriali`/`reels`, che vivono legati a
 * un `defunto_id` debole perché nascono da moduli diversi).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videobook_video', function (Blueprint $table) {
            $table->id();

            $table->foreignId('libro_id')
                ->unique()
                ->constrained('videobook_progetti')
                ->cascadeOnDelete();

            // coda | elaborazione | pronto | errore
            $table->string('stato', 20)->default('coda')->index();
            $table->timestamp('render_avviato_il')->nullable();

            // Esito del render: URL/id Cloudinary del video pronto — stesso
            // schema di video_memoriali/reels, stesso proxy di rendering.
            $table->string('cloudinary_url')->nullable();
            $table->string('cloudinary_public_id')->nullable();

            $table->text('messaggio_errore')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videobook_video');
    }
};
