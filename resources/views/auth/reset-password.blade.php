@extends('layouts.accesso')

@section('title', 'Nuova password — MemorAI')
@section('occhiello', 'Recupero accesso')
@section('titolo', 'Scegli una nuova password')

@section('modulo')
    <form method="POST" action="{{ route('password.store') }}" class="space-y-6">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <x-input-label for="email" value="Indirizzo email" />
            <x-text-input id="email" type="email" name="email" :value="old('email', $request->email)"
                          required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="password" value="Nuova password" />
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

        <x-primary-button class="w-full">Salva la nuova password</x-primary-button>
    </form>
@endsection
