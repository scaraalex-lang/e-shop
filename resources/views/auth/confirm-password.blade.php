@extends('layouts.accesso')

@section('title', 'Conferma password — MemorAI')
@section('occhiello', 'Verifica')
@section('titolo', 'Conferma la password')
@section('sottotitolo', 'Stai per entrare in una sezione protetta: conferma la password per proseguire.')

@section('modulo')
    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-6">
        @csrf

        <div>
            <x-input-label for="password" value="Password" />
            <x-text-input id="password" type="password" name="password"
                          required autofocus autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <x-primary-button class="w-full">Conferma</x-primary-button>
    </form>
@endsection
