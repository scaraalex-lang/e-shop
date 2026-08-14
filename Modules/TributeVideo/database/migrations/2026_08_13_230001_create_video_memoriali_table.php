<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Un video memoriale: foto + audio opzionale + testo, elaborati dal proxy
 * Python in un mp4 con Ken Burns e crossfade, poi caricato su Cloudinary.
 *
 * `defunto_id`/`ordine_id` senza vincolo, come `foto_pratica.ordine_id`:
 * Memorial e Commerce restano moduli separati, il legame nasce quando la
 * pipeline viene agganciata al checkout reale — nell'MVP di test entrambi
 * restano null, il video nasce da testo libero inserito da staff.
 *
 * Il token è l'unica credenziale del link pubblico (stesso pattern di
 * `revisioni_ricordino.token`): a differenza di quella tabella, qui non c'è
 * uno stato ordine da controllare per la validità — il link deve restare
 * raggiungibile anche a ordine chiuso, essendo pensato per un QR fisico
 * permanente. L'unico criterio è `stato === pronto`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_memoriali', function (Blueprint $table) {
            $table->id();

            $table->string('token', 64)->unique();

            $table->unsignedBigInteger('defunto_id')->nullable()->index();
            $table->unsignedBigInteger('ordine_id')->nullable()->index();

            // in_coda | in_elaborazione | pronto | errore
            $table->string('stato', 20)->default('in_coda')->index();

            // Dati testo per le due card (apertura/chiusura), liberi finché
            // non è agganciato a un Defunto reale.
            $table->string('nome_completo');
            $table->string('date', 60)->nullable();
            $table->text('citazione')->nullable();

            $table->string('audio_path')->nullable();

            // Esito del render: URL/id Cloudinary del video pronto, il path
            // locale resta come fallback/audit (mai servito direttamente).
            $table->string('cloudinary_url')->nullable();
            $table->string('cloudinary_public_id')->nullable();
            $table->string('output_path')->nullable();

            $table->text('messaggio_errore')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_memoriali');
    }
};
