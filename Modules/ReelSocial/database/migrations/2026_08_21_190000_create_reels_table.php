<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Un reel: la copertina della Storia Social (immagine 1080x1920) seguita dal
 * Video Memoriale già renderizzato, concatenati in un unico mp4 verticale
 * dal proxy Python (`/concat` su tributevideo_proxy.py, VPS) — pensato per
 * essere pubblicato manualmente come Storia/Reel su FB/IG con un solo link,
 * invece dei due link separati di Storia e Video presi singolarmente.
 *
 * `storia_social_id`/`video_memoriale_id` senza vincolo, come `defunto_id`:
 * questo modulo è l'unico a cui è concesso leggere sia SocialStory che
 * TributeVideo (vedi routes/web.php), ma la convenzione del progetto è comunque
 * niente FK reali fra tabelle di moduli diversi — solo colonne indicizzate.
 *
 * Nessuna riga finché non si preme "Crea reel": a differenza di
 * `video_memoriali`/`storie_social` (che nascono al primo accesso al
 * designer), qui il record esiste solo se il reel è stato davvero richiesto
 * — prima di allora la pagina aggregatore mostra solo i link di Storia/Video
 * presi singolarmente, letti direttamente dai loro moduli.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reels', function (Blueprint $table) {
            $table->id();

            $table->string('token', 64)->unique();

            $table->unsignedBigInteger('defunto_id')->nullable()->index();
            $table->unsignedBigInteger('storia_social_id')->nullable()->index();
            $table->unsignedBigInteger('video_memoriale_id')->nullable()->index();

            $table->string('stato')->default('in_coda');
            // Impostato ad ogni dispatch (creazione o un futuro "rigenera"),
            // non solo alla creazione della riga — vedi il bug corretto oggi
            // sul timer di progresso di Video Memoriale: created_at da solo
            // non basta a far ripartire il timer da zero.
            $table->timestamp('render_avviato_il')->nullable();

            $table->string('cloudinary_url')->nullable();
            $table->string('cloudinary_public_id')->nullable();
            $table->string('output_path')->nullable();
            $table->string('messaggio_errore')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reels');
    }
};
