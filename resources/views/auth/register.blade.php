@extends('layouts.accesso')

@section('title', 'Registrati — MemorAI')
@section('occhiello', 'Nuovo account')
@section('titolo', 'Crea il tuo account')
@section('sottotitolo', 'Bastano pochi dati per ordinare, salvare le bozze dei ricordini e ritrovarle quando vuoi.')

@section('modulo')
    <form method="POST" action="{{ route('register') }}" class="space-y-6">
        @csrf

        <div>
            <x-input-label for="name" value="Nome e cognome" />
            <x-text-input id="name" type="text" name="name" :value="old('name')"
                          required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" value="Indirizzo email" />
            <x-text-input id="email" type="email" name="email" :value="old('email')"
                          required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

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

        <x-primary-button class="w-full">Crea account</x-primary-button>
    </form>
@endsection

@section('piede')
    Sei un'onoranza funebre?
    <a href="{{ route('registrazione.agenzia') }}"
       class="text-oro-scuro hover:text-caffe transition-colors duration-300 underline underline-offset-4 decoration-oro/40">
        Richiedi un account agenzia
    </a>
    <br class="sm:hidden">
    · Hai già un account?
    <a href="{{ route('login') }}"
       class="text-oro-scuro hover:text-caffe transition-colors duration-300 underline underline-offset-4 decoration-oro/40">
        Accedi
    </a>
@endsection
