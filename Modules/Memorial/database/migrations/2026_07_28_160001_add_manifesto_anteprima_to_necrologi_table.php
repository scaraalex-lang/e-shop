<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Miniatura JPEG del manifesto, generata dal canvas al salvataggio (stesso
 * principio di `manifesto_templates.anteprima`): la pagina pubblica mostra
 * questa invece di incassare il PDF/embed originale.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('necrologi', function (Blueprint $table) {
            $table->string('manifesto_anteprima')->nullable()->after('manifesto');
        });
    }

    public function down(): void
    {
        Schema::table('necrologi', function (Blueprint $table) {
            $table->dropColumn('manifesto_anteprima');
        });
    }
};
