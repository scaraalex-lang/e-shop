@extends('layouts.gestione')

@section('title', $voce->etichetta.' — Gestione MemorAI')
@section('titolo', $voce->etichetta)

@section('gestione')
    <a href="{{ route('gestione.menu.index') }}"
       class="font-sans text-[11px] tracking-[0.22em] uppercase text-testo-soft hover:text-oro-scuro transition-colors duration-300">
        ← Menu e footer
    </a>

    <form method="POST" action="{{ route('gestione.menu.update', $voce) }}" class="mt-8 max-w-xl space-y-6">
        @csrf
        @method('PUT')
        @include('gestione.menu._form', ['voce' => $voce])
    </form>
@endsection
