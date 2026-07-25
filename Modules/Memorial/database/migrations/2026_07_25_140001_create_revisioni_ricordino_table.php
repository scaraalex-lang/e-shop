<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Le revisioni di un ricordino: ogni invio alla famiglia e la sua risposta.
 *
 * Non c'è una tabella separata per lo "storico": lo storico È questo elenco.
 * Ogni volta che si manda la bozza nasce una riga nuova, con la sua anteprima
 * congelata — così, se la famiglia chiede una correzione, resta scritto cosa
 * aveva davanti agli occhi quando l'ha chiesta.
 *
 * Il token vive nel link mandato alla famiglia: niente account, niente
 * password. Vale finché l'ordine è aperto (controllo a runtime, non una
 * scadenza a data fissa: un trigesimo può slittare).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revisioni_ricordino', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ricordino_id')->constrained('ricordini')->cascadeOnDelete();

            // 64 caratteri casuali: il link è l'unica credenziale, non deve
            // essere indovinabile.
            $table->string('token', 64)->unique();

            // A chi è stata mandata, e da chi.
            $table->string('inviata_a');
            $table->foreignId('inviata_da')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('inviata_at');

            // Copia di quello che la famiglia ha visto: il ricordino intanto
            // può essere cambiato, la revisione no.
            $table->string('anteprima')->nullable();

            $table->timestamp('vista_at')->nullable();

            // null = in attesa · approvata · modifiche
            $table->string('esito', 20)->nullable()->index();
            $table->text('nota')->nullable();          // cosa correggere
            $table->timestamp('risposta_at')->nullable();

            $table->timestamps();

            $table->index(['ricordino_id', 'inviata_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revisioni_ricordino');
    }
};
