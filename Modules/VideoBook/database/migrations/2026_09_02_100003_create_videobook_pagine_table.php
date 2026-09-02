<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Le pagine di un progetto VideoBook, in ordine.
 *
 * FK vera verso `videobook_progetti`: stesso modulo, niente motivo per il
 * legame debole usato verso Commerce/Memorial (stesso ragionamento di
 * `foto_video_memoriale.video_memoriale_id`).
 *
 * `template_id` è nullOnDelete e non required: se un template viene tolto
 * dal catalogo, la pagina già composta non deve sparire — resta con i suoi
 * riquadri, solo il selettore non lo riproporrà più per pagine nuove.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videobook_pagine', function (Blueprint $table) {
            $table->id();

            $table->foreignId('videobook_progetto_id')
                ->constrained('videobook_progetti')
                ->cascadeOnDelete();

            $table->foreignId('template_id')
                ->nullable()
                ->constrained('videobook_page_templates')
                ->nullOnDelete();

            $table->unsignedInteger('ordine')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videobook_pagine');
    }
};
