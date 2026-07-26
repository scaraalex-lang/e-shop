<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La storia dei crediti di un'agenzia: positivo quando si accredita
 * (l'acquisto di un pacchetto), negativo quando si spende. Il consumo non è
 * ancora costruito — oggi nasce solo il movimento positivo dell'acquisto —
 * ma il segno è già pensato per quello, così non serve un'altra tabella
 * quando arriverà.
 *
 * Nessun saldo tenuto su una colonna a parte: si somma la storia
 * (Agenzia::creditiSaldo()), niente numero da tenere sincronizzato a mano.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimenti_credito', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agenzia_id')->constrained('agenzie')->restrictOnDelete();
            $table->foreignId('ordine_id')->nullable()->constrained('ordini')->nullOnDelete();
            $table->integer('quantita');
            $table->string('causale');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimenti_credito');
    }
};
