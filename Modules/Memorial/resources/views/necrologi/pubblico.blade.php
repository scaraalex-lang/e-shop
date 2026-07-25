@extends('layouts.app')

@php
    $nome = $defunto->nomeCompleto();
    $quando = $necrologio->trigesimo_at;
    $titolo = "Trigesimo di {$nome}";
    $descrizione = $quando
        ? "Il trigesimo sarà il {$quando->translatedFormat('j F Y')} alle {$quando->format('H:i')}"
            .($necrologio->trigesimo_luogo ? ", {$necrologio->trigesimo_luogo}" : '').'.'
        : "In ricordo di {$nome}.";
    $immagine = $necrologio->og_image ? asset('storage/'.$necrologio->og_image) : null;
@endphp

@section('title', $titolo.' — MemorAI')
@section('meta_description', $descrizione)
@section('senza-sidebar', 1)

@push('meta')
    {{-- WhatsApp e Facebook non eseguono JavaScript: prendono questi meta e
         un'immagine da un indirizzo. Senza, il link resta un link nudo. --}}
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $titolo }}">
    <meta property="og:description" content="{{ $descrizione }}">
    <meta property="og:url" content="{{ $indirizzo }}">
    <meta property="og:site_name" content="{{ $agenzia->ragione_sociale }}">
    <meta property="og:locale" content="it_IT">
    @if ($immagine)
        <meta property="og:image" content="{{ $immagine }}">
        <meta property="og:image:alt" content="Ritratto di {{ $nome }}">
    @endif
    <meta name="twitter:card" content="{{ $immagine ? 'summary_large_image' : 'summary' }}">

    {{-- Fuori dai motori di ricerca: la pagina è per chi riceve il link,
         non per chi cerca un nome su internet fra dieci anni. --}}
    <meta name="robots" content="noindex, noarchive">
@endpush

@section('content')
<div class="mx-auto w-full max-w-2xl">

    {{-- ============ la card ============ --}}
    <article class="border border-caffe/15 bg-panna/50">
        @if ($immagine)
            <figure class="border-b border-caffe/15 bg-bianco">
                <img src="{{ $immagine }}" alt="Ritratto di {{ $nome }}"
                     class="mx-auto max-h-[26rem] w-auto">
            </figure>
        @endif

        <div class="px-8 py-10 text-center">
            <span class="font-sans text-[11px] tracking-[0.35em] uppercase text-oro-scuro">
                Nel trigesimo della scomparsa
            </span>

            <h1 class="mt-4 font-serif text-3xl md:text-4xl font-medium leading-tight">{{ $nome }}</h1>

            @if ($defunto->data_nascita || $defunto->data_morte)
                <p class="mt-3 font-sans font-light text-[14px] tabular-nums text-testo-soft">
                    {{ $defunto->data_nascita?->format('d/m/Y') }}
                    @if ($defunto->data_nascita && $defunto->data_morte) — @endif
                    {{ $defunto->data_morte?->format('d/m/Y') }}
                </p>
            @endif

            <span class="mx-auto mt-6 block h-px w-16 bg-oro"></span>

            @if ($quando)
                <div class="mt-8">
                    <p class="font-serif text-2xl leading-snug">
                        {{ $quando->translatedFormat('l j F Y') }}
                    </p>
                    <p class="mt-1 font-serif text-2xl text-oro-scuro tabular-nums">
                        ore {{ $quando->format('H:i') }}
                    </p>

                    @if ($necrologio->trigesimo_luogo)
                        <p class="mt-4 font-sans font-light text-[15px] leading-relaxed text-testo">
                            {{ $necrologio->trigesimo_luogo }}
                            @if ($necrologio->trigesimo_indirizzo)
                                <span class="block text-testo-soft">{{ $necrologio->trigesimo_indirizzo }}</span>
                            @endif
                        </p>
                    @endif
                </div>
            @endif

            @if ($necrologio->testo)
                <p class="mt-8 mx-auto max-w-md font-sans font-light text-[15px] leading-relaxed text-testo-soft whitespace-pre-line">
                    {{ $necrologio->testo }}
                </p>
            @endif
        </div>
    </article>

    {{-- ============ il manifesto ============ --}}
    @if ($necrologio->manifesto)
        <div class="mt-8 text-center">
            <x-button variant="contornata" :href="asset('storage/'.$necrologio->manifesto)" target="_blank" rel="noopener">
                Guarda il manifesto
            </x-button>
        </div>
    @endif

    {{-- ============ condivisione ============ --}}
    @php
        $testoCondivisione = $quando
            ? "{$titolo} — {$quando->translatedFormat('j F')} alle {$quando->format('H:i')}"
            : $titolo;
    @endphp
    <section class="mt-12 border-t border-caffe/15 pt-8 text-center">
        <p class="font-sans text-[11px] tracking-[0.25em] uppercase text-testo-soft">
            Fatelo sapere a chi vorrebbe esserci
        </p>

        <div class="mt-5 flex flex-wrap justify-center gap-4">
            <x-button :href="'https://wa.me/?text='.rawurlencode($testoCondivisione.' '.$indirizzo)"
                      target="_blank" rel="noopener">
                Condividi su WhatsApp
            </x-button>
            <x-button variant="contornata"
                      :href="'https://www.facebook.com/sharer/sharer.php?u='.rawurlencode($indirizzo)"
                      target="_blank" rel="noopener">
                Condividi su Facebook
            </x-button>
        </div>
    </section>

    <p class="mt-12 text-center font-sans font-light text-[12px] leading-relaxed text-testo-soft">
        Pubblicato da {{ $agenzia->ragione_sociale }} con il consenso della famiglia.
        @if ($necrologio->pubblicato_fino_al)
            Questa pagina resterà online fino al {{ $necrologio->pubblicato_fino_al->format('d/m/Y') }}.
        @endif
    </p>
</div>
@endsection
