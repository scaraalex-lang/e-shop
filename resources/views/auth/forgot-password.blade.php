@extends('layouts.accesso')

@section('title', 'Password dimenticata — MemorAI')
@section('occhiello', 'Recupero accesso')
@section('titolo', 'Password dimenticata')
@section('sottotitolo', 'Indicaci il tuo indirizzo email: ti inviamo un link per sceglierne una nuova.')

@section('modulo')
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
        @csrf

        <div>
            <x-input-label for="email" value="Indirizzo email" />
            <x-text-input id="email" type="email" name="email" :value="old('email')"
                          required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <x-primary-button class="w-full">Invia il link</x-primary-button>
    </form>
@endsection

@section('piede')
    Te la sei ricordata?
    <a href="{{ route('login') }}"
       class="text-oro-scuro hover:text-caffe transition-colors duration-300 underline underline-offset-4 decoration-oro/40">
        Torna all'accesso
    </a>
@endsection
