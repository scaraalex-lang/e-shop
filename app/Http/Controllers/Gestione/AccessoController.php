<?php

namespace App\Http\Controllers\Gestione;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AccessoController extends Controller
{
    public function mostra(): View|RedirectResponse
    {
        abort_unless(config('gestione.password'), 403,
            'Dashboard non configurata: imposta GESTIONE_PASSWORD nel file .env.');

        if (request()->session()->get('gestione_accesso')) {
            return redirect()->route('gestione.pannello');
        }

        return view('gestione.entra');
    }

    public function entra(Request $request): RedirectResponse
    {
        $request->validate(['password' => ['required', 'string']]);

        $attesa = (string) config('gestione.password');

        if ($attesa === '' || ! hash_equals($attesa, (string) $request->input('password'))) {
            throw ValidationException::withMessages([
                'password' => 'Password non valida.',
            ]);
        }

        $request->session()->regenerate();
        $request->session()->put('gestione_accesso', true);

        return redirect()->route('gestione.pannello');
    }

    public function esci(Request $request): RedirectResponse
    {
        $request->session()->forget('gestione_accesso');
        $request->session()->regenerate();

        return redirect()->route('gestione.entra');
    }
}
