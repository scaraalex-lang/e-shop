<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Aggancio dell'utente ai due canali della piattaforma.
 *
 * `ruolo` distingue privato (B2C), agenzia (B2B) e staff (area /gestione).
 * `agenzia_id` è unique: un solo login per agenzia — vedi la migration di
 * `agenzie` per il perché e per come si allenta.
 *
 * Sta in Modules/Commerce e non nella migration core di `users` perché è il
 * modulo Commerce a introdurre il concetto di agenzia: la dipendenza va in
 * quella direzione, non al contrario.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('ruolo', 20)->default('privato')->after('email')->index();
            $table->string('telefono')->nullable()->after('ruolo');

            $table->foreignId('agenzia_id')->nullable()->after('telefono')
                ->constrained('agenzie')->nullOnDelete();

            $table->unique('agenzia_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['agenzia_id']);
            $table->dropConstrainedForeignId('agenzia_id');
            $table->dropIndex(['ruolo']);
            $table->dropColumn(['ruolo', 'telefono']);
        });
    }
};
