<?php

namespace App\Http\Controllers;

use App\Models\ContenutoVetrina;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * I testi di hero, fascia valori, CTA e footer della home: un'unica scheda,
 * non un CRUD — le chiavi sono fisse (vedi la migration di
 * contenuti_vetrina), lo staff le compila, non ne crea di nuove.
 */
class GestioneContenutiController extends Controller
{
    /** Le uniche chiavi che questa pagina sa modificare. */
    private const CHIAVI = [
        'hero.occhiello', 'hero.sottotitolo', 'hero.titolo_intro', 'hero.titolo_enfasi',
        'hero.bottone_primario', 'hero.bottone_primario_url',
        'hero.bottone_secondario', 'hero.bottone_secondario_url',
        'topbar.testo',
        'collezioni.occhiello', 'collezioni.titolo', 'collezioni.testo',
        'evidenza.occhiello', 'evidenza.titolo', 'evidenza.bottone_url',
        'valori.1.titolo', 'valori.1.testo',
        'valori.2.titolo', 'valori.2.testo',
        'valori.3.titolo', 'valori.3.testo',
        'cta.titolo_intro', 'cta.titolo_enfasi', 'cta.testo',
        'cta.bottone_primario', 'cta.bottone_primario_url',
        'cta.bottone_secondario', 'cta.bottone_secondario_url',
        'footer.testo_brand',
    ];

    public function edit(): View
    {
        return view('gestione.contenuti.edit', [
            'valori' => ContenutoVetrina::query()->pluck('valore', 'chiave'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        // "valori.*" con l'asterisco: convalida ogni valore dell'array
        // indipendentemente dalla sua chiave, che qui contiene dei punti
        // (es. "hero.occhiello") — "valori.hero.occhiello" varrebbe invece
        // come percorso annidato, non come chiave letterale.
        $dati = $request->validate([
            'valori' => ['array'],
            'valori.*' => ['nullable', 'string', 'max:2000'],
        ]);

        foreach (self::CHIAVI as $chiave) {
            ContenutoVetrina::updateOrCreate(['chiave' => $chiave], ['valore' => $dati['valori'][$chiave] ?? null]);
        }

        return redirect()
            ->route('gestione.contenuti.edit')
            ->with('stato', 'Contenuti della home aggiornati.');
    }
}
