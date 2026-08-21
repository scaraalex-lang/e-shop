<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Quanti crediti dell'agenzia hanno coperto parte (o tutto) di questo
 * ordine — cambio fisso 1 credito = 100 centesimi, lo stesso di
 * SRV-CREDITI-100 (vedi ServiziAgenziaSeeder). Si imposta solo da
 * CreaOrdine, mai da mass-assignment diretto dell'utente: è calcolato e
 * capato lì (saldo disponibile, sotto lock) prima di salvare l'ordine.
 *
 * `totale` non cambia significato: resta il valore pieno dell'ordine.
 * Quanto va ancora incassato in denaro è `totale - crediti_usati*100`,
 * vedi Ordine::valoreInDenaro().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordini', function (Blueprint $table) {
            $table->unsignedInteger('crediti_usati')->default(0)->after('totale');
        });
    }

    public function down(): void
    {
        Schema::table('ordini', function (Blueprint $table) {
            $table->dropColumn('crediti_usati');
        });
    }
};
