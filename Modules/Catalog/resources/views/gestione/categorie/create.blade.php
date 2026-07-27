@extends('layouts.gestione')

@section('title', 'Nuova categoria — Gestione MemorAI')
@section('titolo', 'Nuova categoria')

@section('gestione')
    <a href="{{ route('gestione.categorie.index') }}"
       class="font-sans text-[11px] tracking-[0.22em] uppercase text-testo-soft hover:text-oro-scuro transition-colors duration-300">
        ← Tutte le categorie
    </a>

    <form method="POST" action="{{ route('gestione.categorie.store') }}" enctype="multipart/form-data" class="mt-8 max-w-xl space-y-6">
        @csrf
        @include('catalog::gestione.categorie._form', ['categoria' => null])
    </form>
@endsection
