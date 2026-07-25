@extends('layouts.accesso')

@section('title', 'Registrazione onoranze funebri — MemorAI')
@section('larghezza', 'max-w-3xl')
@section('occhiello', 'Onoranze funebri')
@section('titolo', 'Richiedi un account agenzia')
@section('sottotitolo', 'Compila i dati dell\'agenzia: verifichiamo la richiesta e ti apriamo il listino riservato, gli sconti a quantità e le bozze condivisibili con la famiglia. Nel frattempo puoi già entrare nel tuo account e seguire lo stato della pratica.')

@section('modulo')
    <form method="POST" action="{{ route('registrazione.agenzia') }}" class="space-y-10">
        @csrf

        <fieldset class="space-y-6">
            <legend class="font-sans text-[11px] tracking-[0.3em] uppercase text-oro-scuro mb-5">
                Referente
            </legend>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <x-input-label for="name" value="Nome e cognome" />
                    <x-text-input id="name" type="text" name="name" :value="old('name')"
                                  required autofocus autocomplete="name" />
                    <x-input-error :messages="$errors->get('name')" />
                </div>

                <div>
                    <x-input-label for="telefono" value="Telefono" />
                    <x-text-input id="telefono" type="tel" name="telefono" :value="old('telefono')"
                                  required autocomplete="tel" />
                    <x-input-error :messages="$errors->get('telefono')" />
                </div>
            </div>

            <div>
                <x-input-label for="email" value="Indirizzo email" />
                <x-text-input id="email" type="email" name="email" :value="old('email')"
                              required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" />
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <x-input-label for="password" value="Password" />
                    <x-text-input id="password" type="password" name="password"
                                  required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" />
                </div>

                <div>
                    <x-input-label for="password_confirmation" value="Conferma password" />
                    <x-text-input id="password_confirmation" type="password" name="password_confirmation"
                                  required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" />
                </div>
            </div>
        </fieldset>

        <hr class="h-px w-full border-0 bg-caffe/15">

        <fieldset class="space-y-6">
            <legend class="font-sans text-[11px] tracking-[0.3em] uppercase text-oro-scuro mb-5">
                Agenzia
            </legend>

            <div>
                <x-input-label for="ragione_sociale" value="Ragione sociale" />
                <x-text-input id="ragione_sociale" type="text" name="ragione_sociale"
                              :value="old('ragione_sociale')" required autocomplete="organization" />
                <x-input-error :messages="$errors->get('ragione_sociale')" />
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <x-input-label for="partita_iva" value="Partita IVA" />
                    <x-text-input id="partita_iva" type="text" name="partita_iva"
                                  :value="old('partita_iva')" required inputmode="numeric" maxlength="11" />
                    <x-input-error :messages="$errors->get('partita_iva')" />
                </div>

                <div>
                    <x-input-label for="codice_fiscale" value="Codice fiscale (se diverso)" />
                    <x-text-input id="codice_fiscale" type="text" name="codice_fiscale"
                                  :value="old('codice_fiscale')" maxlength="16" />
                    <x-input-error :messages="$errors->get('codice_fiscale')" />
                </div>
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <x-input-label for="codice_sdi" value="Codice destinatario SdI" />
                    <x-text-input id="codice_sdi" type="text" name="codice_sdi"
                                  :value="old('codice_sdi')" maxlength="7" />
                    <x-input-error :messages="$errors->get('codice_sdi')" />
                </div>

                <div>
                    <x-input-label for="pec" value="PEC" />
                    <x-text-input id="pec" type="email" name="pec" :value="old('pec')" />
                    <x-input-error :messages="$errors->get('pec')" />
                </div>
            </div>
        </fieldset>

        <hr class="h-px w-full border-0 bg-caffe/15">

        <fieldset class="space-y-6">
            <legend class="font-sans text-[11px] tracking-[0.3em] uppercase text-oro-scuro mb-2">
                Sede
            </legend>
            <p class="font-sans font-light text-[13px] leading-relaxed text-testo-soft">
                È anche l'indirizzo di consegna: spediamo all'agenzia, che poi consegna alla famiglia.
            </p>

            <div>
                <x-input-label for="indirizzo" value="Indirizzo" />
                <x-text-input id="indirizzo" type="text" name="indirizzo" :value="old('indirizzo')"
                              required autocomplete="street-address" />
                <x-input-error :messages="$errors->get('indirizzo')" />
            </div>

            <div class="grid gap-6 sm:grid-cols-3">
                <div>
                    <x-input-label for="cap" value="CAP" />
                    <x-text-input id="cap" type="text" name="cap" :value="old('cap')"
                                  required inputmode="numeric" maxlength="5" autocomplete="postal-code" />
                    <x-input-error :messages="$errors->get('cap')" />
                </div>

                <div>
                    <x-input-label for="citta" value="Città" />
                    <x-text-input id="citta" type="text" name="citta" :value="old('citta')"
                                  required autocomplete="address-level2" />
                    <x-input-error :messages="$errors->get('citta')" />
                </div>

                <div>
                    <x-input-label for="provincia" value="Provincia" />
                    <x-text-input id="provincia" type="text" name="provincia" :value="old('provincia')"
                                  required maxlength="2" placeholder="MI" />
                    <x-input-error :messages="$errors->get('provincia')" />
                </div>
            </div>
        </fieldset>

        <x-primary-button class="w-full">Invia la richiesta</x-primary-button>
    </form>
@endsection

@section('piede')
    Sei un privato?
    <a href="{{ route('register') }}"
       class="text-oro-scuro hover:text-caffe transition-colors duration-300 underline underline-offset-4 decoration-oro/40">
        Registrati come privato
    </a>
    · Hai già un account?
    <a href="{{ route('login') }}"
       class="text-oro-scuro hover:text-caffe transition-colors duration-300 underline underline-offset-4 decoration-oro/40">
        Accedi
    </a>
@endsection
