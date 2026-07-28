@extends('layouts.nudo')

@php
    $nome = $defunto->nomeCompleto();
    $titolo = "Manifesto di {$nome}";
    $descrizione = "Il manifesto funebre di {$nome}.";
    // og:image usa la card del necrologio, già disegnata: il manifesto
    // spesso è un PDF, non un formato valido per un'anteprima social, e
    // generarne una miniatura richiederebbe una dipendenza in più
    // (Imagick/Ghostscript) fuori scope qui.
    $immagine = $necrologio->og_image ? asset('storage/'.$necrologio->og_image) : null;
    $ePdf = \Illuminate\Support\Str::endsWith(strtolower($necrologio->manifesto), '.pdf');
@endphp

@section('title', $titolo.' — MemorAI')
@section('meta_description', $descrizione)

@push('meta')
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

    <meta name="robots" content="noindex, noarchive">
@endpush

@section('content')
<div class="w-full max-w-md">

    <article class="bg-bianco border border-caffe/15 shadow-[0_24px_70px_rgba(58,46,34,0.14)]">

        @if ($immagine)
            <figure class="border-b border-caffe/10 bg-panna/40">
                <img src="{{ $immagine }}" alt="Ritratto di {{ $nome }}"
                     class="mx-auto max-h-[22rem] w-auto">
            </figure>
        @endif

        <div class="px-8 py-9 text-center">
            <span class="font-sans text-[10px] tracking-[0.32em] uppercase text-oro-scuro">
                Manifesto funebre
            </span>

            <h1 class="mt-4 font-serif text-3xl font-medium leading-tight">{{ $nome }}</h1>

            @if ($defunto->data_nascita || $defunto->data_morte)
                <p class="mt-2.5 font-sans font-light text-[13px] tabular-nums text-testo-soft">
                    {{ $defunto->data_nascita?->format('d/m/Y') }}
                    @if ($defunto->data_nascita && $defunto->data_morte) — @endif
                    {{ $defunto->data_morte?->format('d/m/Y') }}
                </p>
            @endif

            <span class="mx-auto mt-6 block h-px w-14 bg-oro"></span>

            <div class="mt-7">
                @if ($necrologio->manifestoAnteprimaUrl())
                    <img src="{{ $necrologio->manifestoAnteprimaUrl() }}" alt="Manifesto di {{ $nome }}"
                         class="w-full border border-caffe/15">
                @elseif ($ePdf)
                    {{-- Necrologi salvati prima dell'anteprima: fallback sul visualizzatore
                         PDF nativo del browser, incassato. --}}
                    <embed src="{{ asset('storage/'.$necrologio->manifesto) }}" type="application/pdf"
                           class="w-full h-[28rem] border border-caffe/15">
                @else
                    <img src="{{ asset('storage/'.$necrologio->manifesto) }}" alt="Manifesto di {{ $nome }}"
                         class="w-full border border-caffe/15">
                @endif

                @if ($ePdf)
                    <a href="{{ asset('storage/'.$necrologio->manifesto) }}" target="_blank" rel="noopener"
                       class="mt-4 inline-block font-sans text-[11px] tracking-[0.22em] uppercase text-oro-scuro
                              hover:text-caffe transition-colors duration-300
                              underline underline-offset-4 decoration-oro/40">
                        Scarica il manifesto (PDF)
                    </a>
                @endif
            </div>

            <div class="mt-8">
                <a href="{{ $necrologio->url($agenzia->slug) }}"
                   class="font-sans text-[11px] tracking-[0.22em] uppercase text-oro-scuro
                          hover:text-caffe transition-colors duration-300
                          underline underline-offset-4 decoration-oro/40">
                    Torna al necrologio
                </a>
            </div>
        </div>

        {{-- ============ condivisione, dentro la card ============ --}}
        <div class="border-t border-caffe/10 bg-panna/50 px-8 py-6 text-center">
            <p class="font-sans text-[10px] tracking-[0.24em] uppercase text-testo-soft">
                Fatelo sapere a chi vorrebbe esserci
            </p>

            <div class="mt-4 flex flex-wrap justify-center gap-3">
                <a href="{{ 'https://wa.me/?text='.rawurlencode($titolo.' '.$indirizzo) }}"
                   target="_blank" rel="noopener"
                   class="inline-flex items-center justify-center font-sans uppercase text-[11px]
                          tracking-[0.2em] px-6 py-3 bg-oro text-bianco
                          hover:bg-oro-scuro transition-colors duration-300">
                    WhatsApp
                </a>
                <a href="{{ 'https://www.facebook.com/sharer/sharer.php?u='.rawurlencode($indirizzo) }}"
                   target="_blank" rel="noopener"
                   class="inline-flex items-center justify-center font-sans uppercase text-[11px]
                          tracking-[0.2em] px-6 py-3 border border-caffe/30 text-caffe
                          hover:bg-caffe hover:text-bianco transition-colors duration-300">
                    Facebook
                </a>
            </div>
        </div>
    </article>

    {{-- ============ messaggi di cordoglio ============ --}}
    <article class="mt-6 bg-bianco border border-caffe/15 shadow-[0_24px_70px_rgba(58,46,34,0.14)] px-8 py-9">
        <span class="font-sans text-[10px] tracking-[0.32em] uppercase text-oro-scuro">
            Messaggi di cordoglio
        </span>
        <h2 class="mt-3 font-serif text-xl font-medium">Un pensiero per {{ $nome }}</h2>

        @if (session('messaggio_inviato'))
            <p class="mt-4 border-l-2 border-successo bg-panna px-4 py-3 font-sans text-[13px]">
                Grazie, il tuo messaggio è stato pubblicato.
            </p>
        @endif

        <form method="POST" action="{{ route('necrologio.manifesto.messaggio', ['agenzia' => $agenzia->slug, 'percorso' => $necrologio->percorso]) }}"
              class="mt-6 space-y-4">
            @csrf
            {{-- Honeypot: invisibile a chi guarda la pagina, visibile a chi la compila da script. --}}
            <div style="position:absolute;left:-9999px" aria-hidden="true">
                <label>Sito web</label>
                <input type="text" name="sito_web" tabindex="-1" autocomplete="off">
            </div>

            <div>
                <label for="cordoglio-nome" class="font-sans text-[11px] uppercase tracking-[0.15em] text-testo-soft">Nome</label>
                <input type="text" id="cordoglio-nome" name="nome" required maxlength="80" value="{{ old('nome') }}"
                       class="mt-1.5 w-full border border-caffe/20 bg-panna/30 px-3.5 py-2.5 font-sans text-[14px] focus:outline-none focus:border-oro">
                @error('nome')
                    <p class="mt-1.5 font-sans text-[12px] text-errore">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="cordoglio-messaggio" class="font-sans text-[11px] uppercase tracking-[0.15em] text-testo-soft">Messaggio</label>
                <textarea id="cordoglio-messaggio" name="messaggio" required maxlength="1000" rows="3"
                          class="mt-1.5 w-full border border-caffe/20 bg-panna/30 px-3.5 py-2.5 font-sans text-[14px] focus:outline-none focus:border-oro">{{ old('messaggio') }}</textarea>
                @error('messaggio')
                    <p class="mt-1.5 font-sans text-[12px] text-errore">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit"
                    class="inline-flex items-center justify-center font-sans uppercase text-[11px]
                           tracking-[0.2em] px-6 py-3 bg-oro text-bianco
                           hover:bg-oro-scuro transition-colors duration-300">
                Lascia un messaggio
            </button>
        </form>

        @if ($messaggi->isNotEmpty())
            <div class="mt-9 space-y-5 border-t border-caffe/10 pt-7">
                @foreach ($messaggi as $messaggio)
                    <div>
                        <p class="font-sans text-[13px] font-medium text-caffe">{{ $messaggio->nome }}</p>
                        <p class="mt-1 font-sans font-light text-[14px] leading-relaxed text-testo-soft whitespace-pre-line">{{ $messaggio->messaggio }}</p>
                        <p class="mt-1 font-sans text-[11px] text-testo-soft/70">{{ $messaggio->created_at->translatedFormat('j F Y') }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </article>

    <p class="mt-6 text-center font-sans font-light text-[11px] leading-relaxed text-testo-soft">
        Pubblicato da {{ $agenzia->ragione_sociale }} con il consenso della famiglia.
        @if ($necrologio->pubblicato_fino_al)
            <span class="block">Online fino al {{ $necrologio->pubblicato_fino_al->format('d/m/Y') }}.</span>
        @endif
    </p>
</div>
@endsection
