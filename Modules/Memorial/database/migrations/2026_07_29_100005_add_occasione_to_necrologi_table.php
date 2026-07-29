<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per cosa è questo necrologio: funerale, trigesimo o anniversario (+ il
 * numero, per l'anniversario) — decide la dicitura di condivisione (vedi
 * pubblico.blade.php). Default 'trigesimo': tutti i necrologi esistenti
 * oggi lo sono di fatto (il modello finora non distingueva altro).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('necrologi', function (Blueprint $table) {
            $table->string('occasione', 20)->default('trigesimo')->after('agenzia_id');
            $table->unsignedTinyInteger('numero_anniversario')->nullable()->after('occasione');
        });
    }

    public function down(): void
    {
        Schema::table('necrologi', function (Blueprint $table) {
            $table->dropColumn(['occasione', 'numero_anniversario']);
        });
    }
};
