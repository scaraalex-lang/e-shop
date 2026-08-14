<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Le foto di un video memoriale, in ordine di comparsa (sequenza Ken Burns).
 *
 * FK vera verso video_memoriali: stesso modulo, niente motivo per il legame
 * debole usato verso Commerce/Memorial.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('foto_video_memoriale', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_memoriale_id')->constrained('video_memoriali')->cascadeOnDelete();

            // Path relativo sul disco public: mai un URL assoluto.
            $table->string('path');
            $table->unsignedInteger('ordine')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foto_video_memoriale');
    }
};
