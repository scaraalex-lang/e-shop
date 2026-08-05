<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Il tracciamento minimo di "fattura emessa" per gli ordini a termini
 * (metodo_pagamento = fattura): niente tabella a parte, una fattura per
 * ordine — vedi Ordine::emettiFattura(). Si impostano solo da lì, mai da
 * mass-assignment (stesso principio di stato_pagamento/pagato_at).
 *
 * Il saldo della fattura riusa registraPagamento(), già generico: per un
 * ordine a fattura il "riferimento" è ciò con cui l'agenzia ha saldato
 * (es. un bonifico), non più solo l'ID di una transazione Stripe.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordini', function (Blueprint $table) {
            $table->string('fattura_numero')->nullable()->after('riferimento_pagamento');
            $table->timestamp('fattura_emessa_at')->nullable()->after('fattura_numero');
        });
    }

    public function down(): void
    {
        Schema::table('ordini', function (Blueprint $table) {
            $table->dropColumn(['fattura_numero', 'fattura_emessa_at']);
        });
    }
};
