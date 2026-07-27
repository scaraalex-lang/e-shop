<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Le voci del menu principale e delle tre colonne del footer erano array PHP
 * scritti a mano in resources/views/layouts/app.blade.php. Questa migration
 * le porta a tabella E inserisce subito le righe di oggi (stessa etichetta,
 * url puntata a una categoria reale dove ce n'è una corrispondenza chiara,
 * altrimenti "#" come già era): zero regressione visiva al deploy, lo staff
 * poi le cambia da /gestione/menu quando vuole.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voci_menu', function (Blueprint $table) {
            $table->id();
            $table->string('zona');
            $table->string('etichetta');
            $table->string('url');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['zona', 'is_active']);
        });

        $ora = now();
        $righe = [];

        foreach ([
            ['Trigesimali', '/categoria/articoli-trigesimali'],
            ['Devozionali', '/categoria/devozionali'],
            ['Photoceramiche', '/categoria/photoceramiche'],
            ['Personalizza', '#'],
            ['Stampa foto', '#'],
            ['QR Memoria', '#'],
        ] as $i => [$etichetta, $url]) {
            $righe[] = ['zona' => 'principale', 'etichetta' => $etichetta, 'url' => $url, 'sort_order' => $i * 10];
        }

        foreach ([
            ['Articoli trigesimali', '/categoria/articoli-trigesimali'],
            ['Rosari e corone', '/categoria/rosari'],
            ['Croci', '/categoria/croci'],
            ['Photoceramiche', '/categoria/photoceramiche'],
        ] as $i => [$etichetta, $url]) {
            $righe[] = ['zona' => 'footer_collezioni', 'etichetta' => $etichetta, 'url' => $url, 'sort_order' => $i * 10];
        }

        foreach ([
            ['Personalizza il tuo ricordino', '#'],
            ['Stampa foto', '#'],
            ['QR Memoria', '#'],
            ['Onoranze funebri (B2B)', '/registrati/agenzia'],
        ] as $i => [$etichetta, $url]) {
            $righe[] = ['zona' => 'footer_servizi', 'etichetta' => $etichetta, 'url' => $url, 'sort_order' => $i * 10];
        }

        foreach ([
            ['Contatti', '#'],
            ['Spedizioni e resi', '#'],
            ['Domande frequenti', '#'],
            ['Assistenza personalizzazione', '#'],
        ] as $i => [$etichetta, $url]) {
            $righe[] = ['zona' => 'footer_assistenza', 'etichetta' => $etichetta, 'url' => $url, 'sort_order' => $i * 10];
        }

        foreach ([
            ['Privacy', '#'],
            ['Cookie', '#'],
            ['Termini', '#'],
        ] as $i => [$etichetta, $url]) {
            $righe[] = ['zona' => 'legale', 'etichetta' => $etichetta, 'url' => $url, 'sort_order' => $i * 10];
        }

        foreach ($righe as &$riga) {
            $riga['is_active'] = true;
            $riga['created_at'] = $ora;
            $riga['updated_at'] = $ora;
        }

        DB::table('voci_menu')->insert($righe);
    }

    public function down(): void
    {
        Schema::dropIfExists('voci_menu');
    }
};
