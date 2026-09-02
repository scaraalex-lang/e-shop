<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Il libro/video di un ordine: un progetto per ordine, come `video_memoriali`.
 *
 * `ordine_id`/`defunto_id` senza vincolo — stesso pattern debole di
 * `foto_pratica.ordine_id` e `video_memoriali.defunto_id`: VideoBook resta un
 * modulo a sé, il legame nasce quando la lavorazione dell'ordine lo agancia.
 *
 * `formato` è la dimensione fisica scelta per la stampa (es. "20x20"), fissa
 * per tutte le pagine del libro — non sta sul template di pagina perché è
 * una proprietà del libro, non del layout dei riquadri.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videobook_progetti', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('ordine_id')->nullable()->index();
            $table->unsignedBigInteger('defunto_id')->nullable()->index();

            $table->string('titolo')->nullable();
            $table->string('formato');

            // bozza | completato
            $table->string('stato', 20)->default('bozza')->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videobook_progetti');
    }
};
