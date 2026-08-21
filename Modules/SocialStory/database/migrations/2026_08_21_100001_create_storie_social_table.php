<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Una storia social (formato FB/IG, 1080x1920, un solo fotogramma in questa
 * Fase 1): composta nel designer Fabric, condivisa via link pubblico
 * permanente (`token`) perché l'utente la pubblichi manualmente su
 * Facebook/Instagram — nessuna integrazione Graph API.
 *
 * `defunto_id`/`ordine_id`/`agenzia_id` senza vincolo, come `video_memoriali`:
 * Memorial e Commerce restano moduli separati, letti via query builder puro
 * da chi ne ha bisogno (mai un import Eloquent nella direzione sbagliata).
 *
 * Una sola storia per defunto in questa fase (niente `etichetta` come i
 * manifesti, che ne ammettono più d'uno): il record nasce al primo
 * accesso al designer (`DefuntoStoriaController::show()`, find-or-create) e
 * si aggiorna sul posto ad ogni salvataggio.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storie_social', function (Blueprint $table) {
            $table->id();

            $table->string('token', 64)->unique();

            $table->unsignedBigInteger('defunto_id')->nullable()->index();
            $table->unsignedBigInteger('ordine_id')->nullable()->index();
            $table->unsignedBigInteger('agenzia_id')->nullable()->index();

            $table->json('canvas')->nullable();
            $table->string('anteprima')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storie_social');
    }
};
