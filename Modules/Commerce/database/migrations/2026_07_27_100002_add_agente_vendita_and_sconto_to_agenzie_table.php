<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Due condizioni commerciali dell'agenzia, entrambe fuori da mass-assignment
 * (si impostano solo dai metodi dedicati su Agenzia, come già `stato`):
 * chi la segue e lo sconto personale che sostituisce gli scaglioni generici.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agenzie', function (Blueprint $table) {
            $table->foreignId('agente_vendita_id')->nullable()->after('id')
                ->constrained('agenti_vendita')->nullOnDelete();
            $table->decimal('sconto_percentuale', 5, 2)->nullable()->after('ordine_minimo_pezzi');
        });
    }

    public function down(): void
    {
        Schema::table('agenzie', function (Blueprint $table) {
            $table->dropConstrainedForeignId('agente_vendita_id');
            $table->dropColumn('sconto_percentuale');
        });
    }
};
