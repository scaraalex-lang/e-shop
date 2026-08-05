@extends('layouts.gestione')

@section('title', 'Movimenti — '.$agenzia->ragione_sociale.' — Gestione MemorAI')
@section('titolo', 'Movimenti — '.$agenzia->ragione_sociale)

@section('gestione')
<a href="{{ route('gestione.agenzie.show', $agenzia) }}"
   class="font-sans text-[11px] tracking-[0.22em] uppercase text-testo-soft hover:text-oro-scuro transition-colors duration-300">
    ← {{ $agenzia->ragione_sociale }}
</a>

{{-- Le tre cifre "a oggi": quanto resta da fatturare/saldare/incassare in
     assoluto, non solo nel mese scelto sotto — vedi GestioneAgenzieController::movimenti. --}}
<div class="mt-8 grid gap-6 sm:grid-cols-3">
    <div class="border border-caffe/15 bg-panna/50 px-6 py-5">
        <p class="font-sans text-[10px] tracking-[0.2em] uppercase text-testo-soft">Da fatturare</p>
        <x-prezzo :centesimi="$daFatturare" class="mt-2 block font-serif text-2xl" />
    </div>
    <div class="border border-caffe/15 bg-panna/50 px-6 py-5">
        <p class="font-sans text-[10px] tracking-[0.2em] uppercase text-testo-soft">Fatturato, da saldare</p>
        <x-prezzo :centesimi="$daSaldare" class="mt-2 block font-serif text-2xl" />
    </div>
    <div class="border border-caffe/15 bg-panna/50 px-6 py-5">
        <p class="font-sans text-[10px] tracking-[0.2em] uppercase text-testo-soft">Incassato a carta</p>
        <x-prezzo :centesimi="$incassatoCarta" class="mt-2 block font-serif text-2xl" />
    </div>
</div>

@php
    $rottaOrdine = 'gestione.ordini.show';
    $urlPrecedente = route('gestione.agenzie.movimenti', ['agenzia' => $agenzia, 'anno' => $periodoPrecedente->year, 'mese' => $periodoPrecedente->month]);
    $urlSuccessivo = route('gestione.agenzie.movimenti', ['agenzia' => $agenzia, 'anno' => $periodoSuccessivo->year, 'mese' => $periodoSuccessivo->month]);
@endphp
@include('commerce::partials.estratto-conto')
@endsection
