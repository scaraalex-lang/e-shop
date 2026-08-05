<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A quale defunto appartiene questa riga, quando arriva dalla composizione
 * dell'ordine di stampa nella pagina ordine (Ricordini/coroncine/accessori
 * legati a una pratica già approvata). Nullable: le righe che nascono dalla
 * vetrina o da "Nuovo ordine" non sono legate a nessun defunto.
 *
 * Niente vincolo FK: Memorial è un modulo separato, stesso trattamento di
 * `ordini.defunto_id`. Campo dedicato perché non esiste un meccanismo di
 * attributi-per-riga-carrello — vedi la migration di `numero_anniversario`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('righe_carrello', function (Blueprint $table) {
            $table->unsignedBigInteger('defunto_id')->nullable()->after('numero_anniversario')->index();
        });
    }

    public function down(): void
    {
        Schema::table('righe_carrello', function (Blueprint $table) {
            $table->dropColumn('defunto_id');
        });
    }
};
