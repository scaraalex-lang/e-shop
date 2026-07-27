@extends('layouts.gestione')

@section('title', 'Nuovo agente — Gestione MemorAI')
@section('titolo', 'Nuovo agente')

@section('gestione')
    <a href="{{ route('gestione.agenti.index') }}"
       class="font-sans text-[11px] tracking-[0.22em] uppercase text-testo-soft hover:text-oro-scuro transition-colors duration-300">
        ← Tutti gli agenti
    </a>

    <form method="POST" action="{{ route('gestione.agenti.store') }}" class="mt-8 max-w-xl space-y-6">
        @csrf
        @include('commerce::gestione.agenti._form', ['agente' => null])
    </form>
@endsection
