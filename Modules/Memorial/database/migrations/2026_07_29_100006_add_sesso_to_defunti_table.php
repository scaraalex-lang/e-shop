<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Serve alla coniugazione di genere della dicitura del necrologio per il
 * funerale ("è venuto"/"è venuta a mancare", vedi Defunto::eVenutoAMancare).
 * Nullable: sui defunti già esistenti non c'è, e non è un campo obbligatorio
 * a raccogliere retroattivamente.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('defunti', function (Blueprint $table) {
            $table->char('sesso', 1)->nullable()->after('cognome');
        });
    }

    public function down(): void
    {
        Schema::table('defunti', function (Blueprint $table) {
            $table->dropColumn('sesso');
        });
    }
};
