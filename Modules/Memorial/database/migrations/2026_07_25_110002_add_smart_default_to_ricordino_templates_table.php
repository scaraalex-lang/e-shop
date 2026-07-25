<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Quale impaginazione usa il Designer Smart, per formato.
 *
 * Lo Smart non fa scegliere il layout a chi prenota dal telefono: lo decide la
 * dashboard operativa, una volta, e vale per tutti. Un template per formato ha
 * il flag; gli altri restano disponibili nel designer completo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ricordino_templates', function (Blueprint $table) {
            $table->boolean('is_smart_default')->default(false)->after('is_predefinito');
            $table->index(['formato', 'is_smart_default']);
        });
    }

    public function down(): void
    {
        Schema::table('ricordino_templates', function (Blueprint $table) {
            $table->dropIndex(['formato', 'is_smart_default']);
            $table->dropColumn('is_smart_default');
        });
    }
};
