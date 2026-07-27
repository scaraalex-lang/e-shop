<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Corriere e numero di tracking: si impostano solo da Ordine::spedisci(),
 * mai da mass-assignment (stesso principio di stato_pagamento/pagato_at,
 * niente di questo in $fillable).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordini', function (Blueprint $table) {
            $table->string('corriere')->nullable()->after('spedizione');
            $table->string('tracking_numero')->nullable()->after('corriere');
        });
    }

    public function down(): void
    {
        Schema::table('ordini', function (Blueprint $table) {
            $table->dropColumn(['corriere', 'tracking_numero']);
        });
    }
};
