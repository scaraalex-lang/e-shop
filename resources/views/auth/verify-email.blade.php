@extends('layouts.accesso')

@section('title', 'Verifica email — MemorAI')
@section('occhiello', 'Ultimo passo')
@section('titolo', 'Verifica il tuo indirizzo email')
@section('sottotitolo', "Ti abbiamo appena scritto: apri il link che trovi nell'email per confermare l'indirizzo. Se non ti è arrivata nulla, te la rimandiamo volentieri.")

@section('modulo')
    @if (session('status') === 'verification-link-sent')
        <x-auth-session-status class="mb-6"
            status="Ti abbiamo inviato un nuovo link di verifica all'indirizzo indicato in fase di registrazione." />
    @endif

    <div class="flex flex-col items-center gap-6">
        <form method="POST" action="{{ route('verification.send') }}" class="w-full">
            @csrf
            <x-primary-button class="w-full">Invia di nuovo l'email</x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="font-sans text-[13px] text-testo-soft hover:text-oro-scuro transition-colors duration-300
                           underline underline-offset-4 decoration-oro/40 cursor-pointer">
                Esci
            </button>
        </form>
    </div>
@endsection
