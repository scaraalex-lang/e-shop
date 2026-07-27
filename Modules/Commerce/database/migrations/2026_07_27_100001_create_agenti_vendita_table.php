<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * L'agente di vendita B2B: solo anagrafica gestita dallo staff, nessun
 * login proprio (deciso col committente). Serve per attribuire un'agenzia
 * a chi la segue e, di conseguenza, uno sconto personalizzato.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agenti_vendita', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('email')->nullable();
            $table->string('telefono')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agenti_vendita');
    }
};
