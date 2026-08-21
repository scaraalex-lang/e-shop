@extends('layouts.nudo')

@php
    $poster = $video->posterUrl();
    $descrizione = "Un video in ricordo di {$video->nome_completo}.";
@endphp

@section('title', $video->nome_completo.' — Video memoriale — MemorAI')
@section('meta_description', $descrizione)

@push('meta')
    {{-- WhatsApp e Facebook non eseguono JavaScript: prendono questi meta
         (e provano a incorporare il player) da un link nudo. --}}
    <meta property="og:type" content="video.other">
    <meta property="og:title" content="{{ $video->nome_completo }} — Video memoriale">
    <meta property="og:description" content="{{ $descrizione }}">
    <meta property="og:url" content="{{ route('video.show', $video) }}">
    <meta property="og:site_name" content="MemorAI">
    <meta property="og:locale" content="it_IT">
    @if ($poster)
        <meta property="og:image" content="{{ $poster }}">
        <meta property="og:image:width" content="1920">
        <meta property="og:image:height" content="1080">
        <meta property="og:image:alt" content="Fotogramma dal video di {{ $video->nome_completo }}">
    @endif
    <meta property="og:video" content="{{ $video->cloudinary_url }}">
    <meta property="og:video:secure_url" content="{{ $video->cloudinary_url }}">
    <meta property="og:video:type" content="video/mp4">
    <meta property="og:video:width" content="1920">
    <meta property="og:video:height" content="1080">
    <meta name="twitter:card" content="{{ $poster ? 'summary_large_image' : 'summary' }}">

    {{-- Il link è pensato per chi lo riceve o scansiona il QR, non per la
         ricerca: fuori dai motori come il necrologio. --}}
    <meta name="robots" content="noindex, noarchive">
@endpush

@section('content')
<div class="w-full max-w-2xl">

    {{-- ============ la finestra video ============ --}}
    <article class="bg-bianco border border-caffe/15 shadow-[0_24px_70px_rgba(58,46,34,0.14)]">

        <div class="aspect-video w-full bg-[#0a0805]">
            <video
                controls
                playsinline
                preload="metadata"
                @if ($poster) poster="{{ $poster }}" @endif
                class="h-full w-full object-contain"
            >
                <source src="{{ $video->cloudinary_url }}" type="video/mp4">
                Il tuo browser non supporta la riproduzione video.
                <a href="{{ $video->cloudinary_url }}">Scarica il video</a>.
            </video>
        </div>

        <div class="px-8 py-9 text-center">
            <span class="font-sans text-[10px] tracking-[0.32em] uppercase text-oro-scuro">
                Video memoriale
            </span>

            <h1 class="mt-4 font-serif text-3xl font-medium leading-tight">{{ $video->nome_completo }}</h1>

            @if ($video->date)
                <p class="mt-2.5 font-sans font-light text-[13px] tracking-wide text-testo-soft">
                    {{ $video->date }}
                </p>
            @endif

            @if ($video->citazione)
                <span class="mx-auto mt-6 block h-px w-14 bg-oro"></span>
                <p class="mt-6 font-serif text-lg italic leading-relaxed text-testo-soft">
                    &ldquo;{{ $video->citazione }}&rdquo;
                </p>
            @endif
        </div>
    </article>

    <p class="mt-6 text-center font-sans font-light text-[11px] leading-relaxed text-testo-soft">
        Video memoriale realizzato con MemorAI.
    </p>
</div>
@endsection
