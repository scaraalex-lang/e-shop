<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stesso schema di `has_qr_memorial`: un flag vetrina che sblocca un servizio
 * digitale a un prodotto fisico specifico. Qui sblocca la Storia Social B2C
 * (Modules/SocialStory) — a differenza del Video Memoriale B2B (a crediti
 * agenzia), il B2C non ha un saldo crediti proprio, quindi si aggancia a un
 * acquisto pagato per davvero, come già fa `has_qr_memorial` per il Video
 * Memoriale B2C (vedi DefuntoVideoController::qrPagato()).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('has_social_story')->default(false)->after('has_qr_memorial');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('has_social_story');
        });
    }
};
