<?php

namespace Modules\Commerce\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Commerce\Enums\RuoloUtente;
use Modules\Commerce\Http\Requests\RegistrazioneAgenziaRequest;
use Modules\Commerce\Models\Agenzia;

/**
 * Registrazione del canale B2B: crea insieme l'agenzia (stato "in attesa") e
 * l'utente referente che ci farà login.
 *
 * L'agenzia entra subito nell'area account e vede a che punto è la richiesta;
 * listino riservato, sconti a scaglioni e minimo d'ordine restano spenti
 * finché lo staff non approva.
 */
class RegistrazioneAgenziaController extends Controller
{
    public function create(): View
    {
        return view('commerce::auth.registrazione-agenzia');
    }

    public function store(RegistrazioneAgenziaRequest $request): RedirectResponse
    {
        $user = DB::transaction(function () use ($request) {
            $agenzia = Agenzia::create($request->datiAgenzia());

            $user = new User($request->safe()->only(['name', 'email', 'password', 'telefono']));
            // Ruolo e agenzia non passano dalla mass assignment: vedi User.
            $user->ruolo = RuoloUtente::Agenzia;
            $user->agenzia()->associate($agenzia);
            $user->save();

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('account');
    }
}
