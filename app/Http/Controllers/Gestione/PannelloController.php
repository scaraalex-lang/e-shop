<?php

namespace App\Http\Controllers\Gestione;

use App\Http\Controllers\Controller;
use App\Models\HomeSlide;
use Illuminate\View\View;
use Modules\Memorial\Models\Defunto;
use Modules\Memorial\Models\Ricordino;

/**
 * Pannello d'apertura della dashboard operativa e elenco delle pratiche
 * (i defunti registrati dal flusso di prenotazione), con l'aggancio diretto
 * ai due editor.
 */
class PannelloController extends Controller
{
    public function index(): View
    {
        return view('gestione.pannello', [
            'slideAttive'  => HomeSlide::where('is_active', true)->count(),
            'slideTotali'  => HomeSlide::count(),
            'pratiche'     => Defunto::count(),
            'senzaConsenso' => Defunto::where('gdpr_consenso', false)->count(),
            'ricordini'    => Ricordino::count(),
            'ultime'       => Defunto::withCount('ricordini')->latest()->take(5)->get(),
        ]);
    }

    public function pratiche(): View
    {
        return view('gestione.pratiche', [
            'pratiche' => Defunto::withCount('ricordini')->latest()->paginate(20),
        ]);
    }
}
