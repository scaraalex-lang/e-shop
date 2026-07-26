<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Chi possiede il template: `null` resta il predefinito MemorAI (curato da
 * noi, versionato nel seeder) o un layout creato dallo staff per tutti; un
 * id è l'archivio della singola agenzia, invisibile alle altre — è
 * informazione commerciale loro, come per `necrologi.agenzia_id`.
 *
 * Nessuna FK: `agenzie` sta nel modulo Commerce e Memorial non deve
 * dipenderne (stessa scelta già fatta per `necrologi.agenzia_id` e
 * `necrologio_card_templates.agenzia_id`).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ricordino_templates', function (Blueprint $table) {
            $table->unsignedBigInteger('agenzia_id')->nullable()->index()->after('formato');
        });
    }

    public function down(): void
    {
        Schema::table('ricordino_templates', function (Blueprint $table) {
            $table->dropColumn('agenzia_id');
        });
    }
};
