@extends('layouts.accesso')

@section('title', 'Accedi — MemorAI')
@section('occhiello', 'Area riservata')
@section('titolo', 'Accedi')
@section('sottotitolo', 'Entra per seguire i tuoi ordini, riprendere le bozze dei ricordini e ritrovare i tuoi dati.')

@section('modulo')
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <div>
            <x-input-label for="email" value="Indirizzo email" />
            <x-text-input id="email" type="email" name="email" :value="old('email')"
                          required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="password" value="Password" />
            <x-text-input id="password" type="password" name="password"
                          required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer">
                <input id="remember_me" type="checkbox" name="remember"
                       class="h-4 w-4 border border-caffe/30 accent-oro focus:ring-oro/40">
                <span class="font-sans font-light text-[13px] text-testo-soft">Ricordami</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}"
                   class="font-sans text-[13px] text-testo-soft hover:text-oro-scuro transition-colors duration-300 underline underline-offset-4 decoration-oro/40">
                    Password dimenticata?
                </a>
            @endif
        </div>

        <x-primary-button class="w-full">Accedi</x-primary-button>
    </form>
@endsection

@section('piede')
    Non hai ancora un account?
    <a href="{{ route('register') }}"
       class="text-oro-scuro hover:text-caffe transition-colors duration-300 underline underline-offset-4 decoration-oro/40">
        Registrati
    </a>
@endsection
