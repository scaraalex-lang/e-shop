@extends('layouts.nudo')

@php
    $nome = $defunto?->nomeCompleto() ?? 'un defunto';
    $descrizione = "Un reel in ricordo di {$nome}, creato con MemorAI.";
@endphp

@section('title', $nome.' — Reel — MemorAI')
@section('meta_description', $descrizione)

@push('meta')
    {{-- WhatsApp/Facebook/Instagram non eseguono JavaScript: prendono questi
         meta (e provano a incorporare il player) da un link nudo. --}}
    <meta property="og:type" content="video.other">
    <meta property="og:title" content="{{ $nome }} — Reel">
    <meta property="og:description" content="{{ $descrizione }}">
    <meta property="og:url" content="{{ route('reel.show', $reel) }}">
    <meta property="og:site_name" content="MemorAI">
    <meta property="og:locale" content="it_IT">
    <meta property="og:video" content="{{ $reel->cloudinary_url }}">
    <meta property="og:video:secure_url" content="{{ $reel->cloudinary_url }}">
    <meta property="og:video:type" content="video/mp4">
    <meta property="og:video:width" content="1080">
    <meta property="og:video:height" content="1920">
    <meta name="twitter:card" content="summary">

    {{-- Pensato per chi riceve il link, non per la ricerca — come video memoriale e storia. --}}
    <meta name="robots" content="noindex, noarchive">
@endpush

@section('content')
<div class="w-full max-w-sm">

    {{-- ============ il reel ============ --}}
    <article class="bg-bianco border border-caffe/15 shadow-[0_24px_70px_rgba(58,46,34,0.14)]">
        <div class="aspect-[9/16] w-full bg-[#0a0805]">
            <video
                controls
                playsinline
                preload="metadata"
                class="h-full w-full object-contain"
            >
                <source src="{{ $reel->cloudinary_url }}" type="video/mp4">
                Il tuo browser non supporta la riproduzione video.
                <a href="{{ $reel->cloudinary_url }}">Scarica il reel</a>.
            </video>
        </div>

        <div class="px-8 py-9 text-center">
            <span class="font-sans text-[10px] tracking-[0.32em] uppercase text-oro-scuro">
                Reel
            </span>
            <h1 class="mt-4 font-serif text-3xl font-medium leading-tight">{{ $nome }}</h1>

            <div class="mt-6">
                <x-button :href="$reel->downloadUrl()" download variant="contornata">
                    Scarica il reel
                </x-button>
            </div>
        </div>
    </article>

    <p class="mt-6 text-center font-sans font-light text-[11px] leading-relaxed text-testo-soft">
        Reel realizzato con MemorAI — salvalo o condividilo su Facebook e Instagram.
    </p>
</div>
@endsection
