<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Distingue i template forniti da MemorAI da quelli salvati dall'utente.
 * I predefiniti si presentano in cima all'elenco e non sono eliminabili.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ricordino_templates', function (Blueprint $table) {
            $table->boolean('is_predefinito')->default(false)->after('formato');
            $table->unsignedSmallInteger('sort_order')->default(0)->after('is_predefinito');

            $table->index(['is_predefinito', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::table('ricordino_templates', function (Blueprint $table) {
            $table->dropIndex(['is_predefinito', 'sort_order']);
            $table->dropColumn(['is_predefinito', 'sort_order']);
        });
    }
};
