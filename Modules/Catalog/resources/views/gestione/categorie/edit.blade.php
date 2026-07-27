@extends('layouts.gestione')

@section('title', $categoria->name.' — Gestione MemorAI')
@section('titolo', $categoria->name)

@section('gestione')
    <a href="{{ route('gestione.categorie.index') }}"
       class="font-sans text-[11px] tracking-[0.22em] uppercase text-testo-soft hover:text-oro-scuro transition-colors duration-300">
        ← Tutte le categorie
    </a>

    <form method="POST" action="{{ route('gestione.categorie.update', $categoria) }}" enctype="multipart/form-data" class="mt-8 max-w-xl space-y-6">
        @csrf
        @method('PUT')
        @include('catalog::gestione.categorie._form', ['categoria' => $categoria])
    </form>
@endsection
