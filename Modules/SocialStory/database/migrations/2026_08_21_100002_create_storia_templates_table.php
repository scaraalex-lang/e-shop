<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Layout di storia social riusabili. Stesso schema di `manifesto_templates`
 * (`agenzia_id` nullable fin da subito: null è un layout curato da staff,
 * visibile a tutti; un id è l'archivio della singola agenzia) più
 * `is_predefinito`/`sort_order` di `ricordino_templates`, per poter seedare
 * template curati che compaiono per primi nell'elenco fin dal giorno 1.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storia_templates', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('formato')->default('1080x1920');
            $table->unsignedBigInteger('agenzia_id')->nullable()->index();
            $table->boolean('is_predefinito')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('layout')->nullable();
            $table->string('anteprima')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storia_templates');
    }
};
