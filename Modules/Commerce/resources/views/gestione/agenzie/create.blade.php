@extends('layouts.gestione')

@section('title', 'Nuova agenzia — Gestione MemorAI')
@section('titolo', 'Nuova agenzia')

@section('gestione')
    <a href="{{ route('gestione.agenzie.index') }}"
       class="font-sans text-[11px] tracking-[0.22em] uppercase text-testo-soft hover:text-oro-scuro transition-colors duration-300">
        ← Tutte le agenzie
    </a>

    <p class="mt-6 font-sans font-light text-[14px] text-testo-soft max-w-xl">
        A differenza della registrazione self-service, questa agenzia nasce già <strong class="text-testo">approvata</strong>:
        sconti, fattura e minimo d'ordine sono attivi da subito.
    </p>

    <form method="POST" action="{{ route('gestione.agenzie.store') }}" class="mt-8 max-w-3xl space-y-10">
        @csrf

        <fieldset class="space-y-6">
            <legend class="font-sans text-[11px] tracking-[0.3em] uppercase text-oro-scuro mb-5">Referente</legend>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <x-input-label for="name" value="Nome e cognome" />
                    <x-text-input id="name" name="name" :value="old('name')" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="telefono" value="Telefono" />
                    <x-text-input id="telefono" name="telefono" :value="old('telefono')" required />
                    <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
                </div>
            </div>

            <div>
                <x-input-label for="email" value="Indirizzo email (per l'accesso)" />
                <x-text-input id="email" type="email" name="email" :value="old('email')" required />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <x-input-label for="password" value="Password" />
                    <x-text-input id="password" type="password" name="password" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="password_confirmation" value="Conferma password" />
                    <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
                </div>
            </div>
        </fieldset>

        <hr class="h-px w-full border-0 bg-caffe/15">

        <fieldset class="space-y-6">
            <legend class="font-sans text-[11px] tracking-[0.3em] uppercase text-oro-scuro mb-5">Agenzia</legend>

            <div>
                <x-input-label for="ragione_sociale" value="Ragione sociale" />
                <x-text-input id="ragione_sociale" name="ragione_sociale" :value="old('ragione_sociale')" required />
                <x-input-error :messages="$errors->get('ragione_sociale')" class="mt-2" />
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <x-input-label for="partita_iva" value="Partita IVA" />
                    <x-text-input id="partita_iva" name="partita_iva" :value="old('partita_iva')" required inputmode="numeric" maxlength="11" />
                    <x-input-error :messages="$errors->get('partita_iva')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="codice_fiscale" value="Codice fiscale (se diverso)" />
                    <x-text-input id="codice_fiscale" name="codice_fiscale" :value="old('codice_fiscale')" maxlength="16" />
                </div>
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <x-input-label for="codice_sdi" value="Codice destinatario SdI" />
                    <x-text-input id="codice_sdi" name="codice_sdi" :value="old('codice_sdi')" maxlength="7" />
                </div>
                <div>
                    <x-input-label for="pec" value="PEC" />
                    <x-text-input id="pec" type="email" name="pec" :value="old('pec')" />
                </div>
            </div>
        </fieldset>

        <hr class="h-px w-full border-0 bg-caffe/15">

        <fieldset class="space-y-6">
            <legend class="font-sans text-[11px] tracking-[0.3em] uppercase text-oro-scuro mb-2">Sede</legend>

            <div>
                <x-input-label for="indirizzo" value="Indirizzo" />
                <x-text-input id="indirizzo" name="indirizzo" :value="old('indirizzo')" required />
                <x-input-error :messages="$errors->get('indirizzo')" class="mt-2" />
            </div>

            <div class="grid gap-6 sm:grid-cols-3">
                <div>
                    <x-input-label for="cap" value="CAP" />
                    <x-text-input id="cap" name="cap" :value="old('cap')" required inputmode="numeric" maxlength="5" />
                    <x-input-error :messages="$errors->get('cap')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="citta" value="Città" />
                    <x-text-input id="citta" name="citta" :value="old('citta')" required />
                    <x-input-error :messages="$errors->get('citta')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="provincia" value="Provincia" />
                    <x-text-input id="provincia" name="provincia" :value="old('provincia')" required maxlength="2" placeholder="MI" />
                    <x-input-error :messages="$errors->get('provincia')" class="mt-2" />
                </div>
            </div>
        </fieldset>

        <x-primary-button>Crea l'agenzia, già approvata</x-primary-button>
    </form>
@endsection
