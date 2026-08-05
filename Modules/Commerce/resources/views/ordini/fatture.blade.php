@extends('layouts.account')

@section('title', 'Pagamenti — MemorAI')
@section('titolo', 'Pagamenti e fatture')
@section('sottotitolo', 'Ogni movimento — fatture emesse, pagamenti ricevuti, crediti usati — mese per mese.')

@section('account')
{{-- Le tre cifre "a oggi": quanto resta da fatturare/saldare in assoluto,
     non solo nel mese scelto sotto — vedi OrdiniController::fatture. --}}
<div class="grid gap-6 sm:grid-cols-3">
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
    $rottaOrdine = 'ordine';
    $urlPrecedente = route('fatture', ['anno' => $periodoPrecedente->year, 'mese' => $periodoPrecedente->month]);
    $urlSuccessivo = route('fatture', ['anno' => $periodoSuccessivo->year, 'mese' => $periodoSuccessivo->month]);
@endphp
@include('commerce::partials.estratto-conto')
@endsection
