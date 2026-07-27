@extends('layouts.gestione')

@section('title', $prodotto->name.' — Gestione MemorAI')
@section('titolo', $prodotto->name)

@section('gestione')
    <a href="{{ route('gestione.prodotti.index') }}"
       class="font-sans text-[11px] tracking-[0.22em] uppercase text-testo-soft hover:text-oro-scuro transition-colors duration-300">
        ← Tutti i prodotti
    </a>

    <form method="POST" action="{{ route('gestione.prodotti.update', $prodotto) }}" enctype="multipart/form-data" class="mt-8">
        @csrf
        @method('PUT')
        @include('catalog::gestione.prodotti._form', ['prodotto' => $prodotto])
    </form>
@endsection
