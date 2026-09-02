<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stesso schema di `has_qr_memorial`/`has_social_story`: un flag vetrina che
 * sblocca un servizio digitale a un prodotto fisico specifico. Qui sblocca
 * l'impaginatore VideoBook (Modules/VideoBook) — dispositivo video a libro
 * o fotoalbum stampato, entrambi compongono lo stesso libro nello stesso
 * editor (vedi EditorController::videoBookPagato()).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('has_video_book')->default(false)->after('has_social_story');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('has_video_book');
        });
    }
};
