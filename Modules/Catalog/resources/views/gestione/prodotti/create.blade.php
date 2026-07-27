@extends('layouts.gestione')

@section('title', 'Nuovo prodotto — Gestione MemorAI')
@section('titolo', 'Nuovo prodotto')

@section('gestione')
    <a href="{{ route('gestione.prodotti.index') }}"
       class="font-sans text-[11px] tracking-[0.22em] uppercase text-testo-soft hover:text-oro-scuro transition-colors duration-300">
        ← Tutti i prodotti
    </a>

    <form method="POST" action="{{ route('gestione.prodotti.store') }}" enctype="multipart/form-data" class="mt-8">
        @csrf
        @include('catalog::gestione.prodotti._form', ['prodotto' => null])
    </form>
@endsection
