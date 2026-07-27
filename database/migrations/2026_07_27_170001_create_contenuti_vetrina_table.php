<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * I testi di hero, fascia valori, CTA finale, topbar e footer della home
 * erano scritti a mano in home.blade.php e layouts/app.blade.php. Questa
 * migration porta ogni blocco a una riga chiave/valore e inserisce subito il
 * testo di oggi: zero regressione visiva al deploy, lo staff lo cambia da
 * /gestione/contenuti quando vuole.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contenuti_vetrina', function (Blueprint $table) {
            $table->id();
            $table->string('chiave')->unique();
            $table->text('valore')->nullable();
            $table->timestamps();
        });

        $ora = now();

        $valori = [
            'hero.occhiello' => 'Artigianato memoriale dal 2026',
            'hero.sottotitolo' => 'Rosari, corone e ricordini di fattura artigianale, in materiali nobili. Piccoli oggetti da tenere fra le mani e tramandare — mai lutto, sempre cura.',
            'hero.titolo_intro' => 'Custodire la memoria',
            'hero.titolo_enfasi' => 'bellezza',
            'hero.bottone_primario' => 'Scopri le collezioni',
            'hero.bottone_primario_url' => '#',
            'hero.bottone_secondario' => 'Personalizza',
            'hero.bottone_secondario_url' => '#',

            'topbar.testo' => 'Lavorazione artigianale · Spedizione curata in tutta Italia',

            'collezioni.occhiello' => 'Le collezioni',
            'collezioni.titolo' => 'Un catalogo curato',
            'collezioni.testo' => 'Ogni collezione raccoglie oggetti scelti per bellezza e cura dei dettagli.',

            'evidenza.occhiello' => 'Dal catalogo',
            'evidenza.titolo' => 'In evidenza',
            'evidenza.bottone_url' => '#',

            'valori.1.titolo' => 'Fattura artigianale',
            'valori.1.testo' => 'Ogni pezzo è lavorato e rifinito a mano, in materiali nobili scelti con cura.',
            'valori.2.titolo' => 'Personalizzazione',
            'valori.2.testo' => 'Ricordini e stampe su misura, con la fotografia e le parole che preferite.',
            'valori.3.titolo' => 'QR Memoria',
            'valori.3.testo' => 'Una galleria online di foto e ricordi, da arricchire nel tempo, accanto all\'oggetto.',

            'cta.titolo_intro' => 'Un ricordo che',
            'cta.titolo_enfasi' => 'resta',
            'cta.testo' => 'Racconta a chi siamo che cosa desideri: ti guideremo nella scelta e nella personalizzazione, con la stessa cura con cui realizziamo ogni oggetto.',
            'cta.bottone_primario' => 'Richiedi assistenza',
            'cta.bottone_primario_url' => '#',
            'cta.bottone_secondario' => 'Onoranze funebri (B2B)',
            'cta.bottone_secondario_url' => '/registrati/agenzia',

            'footer.testo_brand' => 'Oggetti di memoria e devozione, pensati e realizzati con cura artigianale. Bellezza che dura, da tramandare.',
        ];

        DB::table('contenuti_vetrina')->insert(
            collect($valori)->map(fn ($valore, $chiave) => [
                'chiave' => $chiave,
                'valore' => $valore,
                'created_at' => $ora,
                'updated_at' => $ora,
            ])->values()->all()
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('contenuti_vetrina');
    }
};
