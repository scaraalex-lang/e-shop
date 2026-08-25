@extends('layouts.nudo')

@php
    $nome = $defunto?->nomeCompleto() ?? 'un defunto';
    $descrizione = "Una storia in ricordo di {$nome}, creata con MemorAI.";
@endphp

@section('title', $nome.' — Storia — MemorAI')
@section('meta_description', $descrizione)

@push('meta')
    {{-- WhatsApp/Facebook/Instagram non eseguono JavaScript: prendono questi
         meta da un link nudo per l'anteprima quando viene incollato. --}}
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $nome }} — Storia">
    <meta property="og:description" content="{{ $descrizione }}">
    <meta property="og:url" content="{{ route('storia-social.show', $storia) }}">
    <meta property="og:site_name" content="MemorAI">
    <meta property="og:locale" content="it_IT">
    @if ($storia->anteprimaUrl())
        <meta property="og:image" content="{{ $storia->anteprimaUrl() }}">
        <meta property="og:image:width" content="1080">
        <meta property="og:image:height" content="1920">
        <meta property="og:image:alt" content="Storia in ricordo di {{ $nome }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">

    {{-- Pensato per chi riceve il link, non per la ricerca — come il video memoriale. --}}
    <meta name="robots" content="noindex, noarchive">
@endpush

@section('content')
<div class="w-full max-w-sm">

    {{-- ============ la storia ============ --}}
    <article class="bg-bianco border border-caffe/15 shadow-[0_24px_70px_rgba(58,46,34,0.14)]">
        <div class="aspect-[9/16] w-full bg-[#0a0805]">
            @if ($storia->anteprimaUrl())
                <img src="{{ $storia->anteprimaUrl() }}" alt="Storia in ricordo di {{ $nome }}" class="h-full w-full object-contain">
            @endif
        </div>

        <div class="px-8 py-9 text-center">
            <span class="font-sans text-[10px] tracking-[0.32em] uppercase text-oro-scuro">
                Storia
            </span>
            <h1 class="mt-4 font-serif text-3xl font-medium leading-tight">{{ $nome }}</h1>

            @if ($storia->anteprimaUrl())
                <div class="mt-6">
                    <x-button :href="$storia->anteprimaUrl()" :download="$storia->nomeFile()" variant="contornata">
                        Scarica la storia
                    </x-button>
                </div>
            @endif
        </div>
    </article>

    <p class="mt-6 text-center font-sans font-light text-[11px] leading-relaxed text-testo-soft">
        Storia realizzata con MemorAI — salvala o condividila su Facebook e Instagram.
    </p>
</div>
@endsection
