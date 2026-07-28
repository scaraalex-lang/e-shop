<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * I due campi generici "luogo/indirizzo del funerale" non bastavano: una
 * cerimonia ha un punto di partenza (casa del commiato, casa funeraria o
 * casa del defunto), un orario, una chiesa e un cimitero — ciascuno col
 * proprio indirizzo. Sostituiamo i due campi con l'insieme completo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('defunti', function (Blueprint $table) {
            $table->dropColumn(['luogo_funerale', 'indirizzo_funerale']);

            $table->string('luogo_partenza')->nullable()->after('preghiera');
            $table->string('indirizzo_cerimonia')->nullable()->after('luogo_partenza');
            $table->dateTime('cerimonia_at')->nullable()->after('indirizzo_cerimonia');
            $table->string('chiesa')->nullable()->after('cerimonia_at');
            $table->string('indirizzo_chiesa')->nullable()->after('chiesa');
            $table->string('indirizzo_cimitero')->nullable()->after('cimitero');
        });
    }

    public function down(): void
    {
        Schema::table('defunti', function (Blueprint $table) {
            $table->dropColumn([
                'luogo_partenza', 'indirizzo_cerimonia', 'cerimonia_at',
                'chiesa', 'indirizzo_chiesa', 'indirizzo_cimitero',
            ]);

            $table->string('luogo_funerale')->nullable()->after('preghiera');
            $table->string('indirizzo_funerale')->nullable()->after('luogo_funerale');
        });
    }
};
