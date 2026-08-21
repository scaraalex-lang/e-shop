<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marca le categorie i cui prodotti compaiono nella sezione "Componi
 * l'ordine di stampa" della pagina ordine (Commerce), una volta approvata
 * la bozza del ricordino: formati di stampa, coroncine, accessori di
 * distribuzione. Decisione dello staff da /gestione/categorie, non del
 * codice: default false, si accende categoria per categoria.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('in_ordine_stampa')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('in_ordine_stampa');
        });
    }
};
