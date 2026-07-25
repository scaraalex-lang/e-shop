@extends('layouts.accesso')

@section('title', 'Pagina non trovata — MemorAI')
@section('occhiello', 'Errore 404')
@section('titolo', 'Questa pagina non esiste')

@section('modulo')
    <p class="font-sans font-light text-[15px] leading-relaxed text-testo-soft">
        L'indirizzo che hai aperto non porta da nessuna parte: forse è stato
        cambiato, o il collegamento che hai seguito era vecchio.
    </p>

    <div class="mt-8 flex flex-wrap gap-4">
        <x-button href="{{ url('/') }}">Torna alla vetrina</x-button>
        @auth
            <x-button variant="contornata" :href="route('account')">Il mio account</x-button>
        @endauth
    </div>
@endsection
