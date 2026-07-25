<?php

namespace App\Http\Controllers\Gestione;

use App\Http\Controllers\Controller;
use App\Models\HomeSlide;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Gestione delle slide del carosello di home: cosa vede il visitatore appena
 * arriva e dove lo porta ogni pulsante.
 */
class SlideController extends Controller
{
    public function index(): View
    {
        return view('gestione.slide.index', [
            'slide' => HomeSlide::orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function create(): View
    {
        return view('gestione.slide.form', [
            'slide' => new HomeSlide([
                'is_active'  => true,
                'sort_order' => (int) HomeSlide::max('sort_order') + 10,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $slide = HomeSlide::create($this->validati($request));

        return redirect()->route('gestione.slide.index')
            ->with('ok', "Slide «{$slide->titolo}» creata.");
    }

    public function edit(HomeSlide $slide): View
    {
        return view('gestione.slide.form', compact('slide'));
    }

    public function update(Request $request, HomeSlide $slide): RedirectResponse
    {
        $slide->update($this->validati($request, $slide));

        return redirect()->route('gestione.slide.index')
            ->with('ok', "Slide «{$slide->titolo}» aggiornata.");
    }

    public function destroy(HomeSlide $slide): RedirectResponse
    {
        $titolo = $slide->titolo;
        $slide->delete();

        return redirect()->route('gestione.slide.index')
            ->with('ok', "Slide «{$titolo}» eliminata.");
    }

    /** Pubblica/nasconde senza aprire il form. */
    public function attiva(HomeSlide $slide): RedirectResponse
    {
        $slide->update(['is_active' => ! $slide->is_active]);

        return back()->with('ok', $slide->is_active
            ? "Slide «{$slide->titolo}» pubblicata."
            : "Slide «{$slide->titolo}» nascosta.");
    }

    /** Sposta la slide di una posizione ($direzione = su|giu). */
    public function sposta(HomeSlide $slide, string $direzione): RedirectResponse
    {
        $vicina = HomeSlide::query()
            ->when($direzione === 'su',
                fn ($q) => $q->where('sort_order', '<', $slide->sort_order)->orderByDesc('sort_order'),
                fn ($q) => $q->where('sort_order', '>', $slide->sort_order)->orderBy('sort_order'))
            ->first();

        if ($vicina) {
            // scambio secco delle due posizioni
            [$a, $b] = [$slide->sort_order, $vicina->sort_order];
            $slide->update(['sort_order' => $b]);
            $vicina->update(['sort_order' => $a]);
        }

        return back();
    }

    /**
     * Campi validati. L'immagine si può caricare (finisce in storage/slide) o
     * indicare come path/URL già esistente.
     */
    private function validati(Request $request, ?HomeSlide $slide = null): array
    {
        $dati = $request->validate([
            'occhiello'      => ['nullable', 'string', 'max:120'],
            'titolo'         => ['required', 'string', 'max:160'],
            'titolo_corsivo' => ['nullable', 'string', 'max:60'],
            'testo'          => ['nullable', 'string', 'max:600'],
            'immagine'       => ['nullable', 'string', 'max:255'],
            'immagine_alt'   => ['nullable', 'string', 'max:160'],
            'cta_label'      => ['nullable', 'string', 'max:60'],
            'cta_href'       => ['nullable', 'string', 'max:255'],
            'cta2_label'     => ['nullable', 'string', 'max:60'],
            'cta2_href'      => ['nullable', 'string', 'max:255'],
            'sort_order'     => ['required', 'integer', 'min:0', 'max:9999'],
            'file_immagine'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if ($file = $request->file('file_immagine')) {
            $dati['immagine'] = $file->store('slide', 'public');

            // il file sostituito non serve più, ma solo se stava dentro slide/
            $vecchia = $slide?->immagine;
            if ($vecchia && str_starts_with($vecchia, 'slide/')) {
                Storage::disk('public')->delete($vecchia);
            }
        }

        unset($dati['file_immagine']);

        $dati['is_active'] = $request->boolean('is_active');

        return $dati;
    }
}
