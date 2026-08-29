@extends('layouts.nudo')

@php
    $nome = $defunto->nomeCompleto();
    $occasione = $necrologio->occasione ?? \Modules\Commerce\Enums\Occasione::Trigesimo;

    // Il funerale legge data/luogo dal defunto (Defunto::cerimonia_at/chiesa/
    // indirizzo_chiesa, già raccolti in lavorazione): trigesimo_at sul
    // necrologio non è la data del funerale, è quella del trigesimo o
    // dell'anniversario.
    $quando = $occasione === \Modules\Commerce\Enums\Occasione::Funerale ? $defunto->cerimonia_at : $necrologio->trigesimo_at;
    $luogoEvento = $occasione === \Modules\Commerce\Enums\Occasione::Funerale ? $defunto->chiesa : $necrologio->trigesimo_luogo;
    $indirizzoEvento = $occasione === \Modules\Commerce\Enums\Occasione::Funerale ? $defunto->indirizzo_chiesa : $necrologio->trigesimo_indirizzo;

    $badge = match ($occasione) {
        \Modules\Commerce\Enums\Occasione::Funerale => 'Necrologio',
        \Modules\Commerce\Enums\Occasione::Anniversario => "{$necrologio->numero_anniversario}° anniversario della scomparsa",
        \Modules\Commerce\Enums\Occasione::Trigesimo => 'Nel trigesimo della scomparsa',
    };

    $titolo = match ($occasione) {
        \Modules\Commerce\Enums\Occasione::Funerale => "{$nome} " . $defunto->eVenutoAMancare(),
        \Modules\Commerce\Enums\Occasione::Anniversario => "{$necrologio->numero_anniversario}° Anniversario della scomparsa di {$nome}",
        \Modules\Commerce\Enums\Occasione::Trigesimo => "Trigesimo di {$nome}",
    };

    $descrizioneEvento = match ($occasione) {
        \Modules\Commerce\Enums\Occasione::Funerale => 'Il funerale sarà celebrato',
        \Modules\Commerce\Enums\Occasione::Anniversario => 'La funzione sarà',
        \Modules\Commerce\Enums\Occasione::Trigesimo => 'Il trigesimo sarà',
    };

    $descrizione = $quando
        ? "{$descrizioneEvento} il {$quando->translatedFormat('j F Y')} alle {$quando->format('H:i')}"
            .($luogoEvento ? ", {$luogoEvento}" : '').'.'
        : "In ricordo di {$nome}.";
    $immagine = $necrologio->og_image ? asset('storage/'.$necrologio->og_image) : null;

    $testoCondivisione = $quando
        ? "{$titolo} — {$quando->translatedFormat('j F')} alle {$quando->format('H:i')}"
        : $titolo;
@endphp

@section('title', $titolo.' — MemorAI')
@section('meta_description', $descrizione)

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
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:image:alt" content="Ritratto di {{ $nome }}">
    @endif
    <meta name="twitter:card" content="{{ $immagine ? 'summary_large_image' : 'summary' }}">

    {{-- Fuori dai motori di ricerca: la pagina è per chi riceve il link,
         non per chi cerca un nome su internet fra dieci anni. --}}
    <meta name="robots" content="noindex, noarchive">
@endpush

@section('content')
<div class="w-full max-w-md">

    {{-- ============ la card ============ --}}
    <article class="bg-bianco border border-caffe shadow-[0_24px_70px_rgba(58,46,34,0.14)]">

        @if ($immagine)
            <figure class="border-b border-caffe/10 bg-panna/40">
                <img src="{{ $immagine }}" alt="Ritratto di {{ $nome }}"
                     class="mx-auto max-h-[22rem] w-auto">
            </figure>
        @endif

        <div class="px-8 py-9 text-center">
            <span class="font-sans text-[10px] tracking-[0.32em] uppercase text-oro-scuro">
                {{ $badge }}
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

            @if ($quando)
                <div class="mt-7">
                    <p class="font-serif text-[1.6rem] leading-snug">
                        {{ $quando->translatedFormat('l j F Y') }}
                    </p>
                    <p class="mt-0.5 font-serif text-[1.6rem] text-oro-scuro tabular-nums">
                        ore {{ $quando->format('H:i') }}
                    </p>

                    @if ($luogoEvento)
                        <p class="mt-4 font-sans font-light text-[14px] leading-relaxed text-testo">
                            <a href="{{ 'https://www.google.com/maps/search/?api=1&query='.rawurlencode(trim($luogoEvento.' '.$indirizzoEvento)) }}"
                               target="_blank" rel="noopener"
                               class="inline-flex items-start gap-1.5 underline underline-offset-4 decoration-oro/40
                                      hover:decoration-oro transition-colors duration-300">
                                <svg viewBox="0 0 24 24" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-oro-scuro" fill="currentColor" aria-hidden="true">
                                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z"/>
                                </svg>
                                <span>
                                    {{ $luogoEvento }}
                                    @if ($indirizzoEvento)
                                        <span class="block text-testo-soft">{{ $indirizzoEvento }}</span>
                                    @endif
                                </span>
                            </a>
                        </p>
                    @endif
                </div>
            @endif

            @if ($necrologio->testo)
                <p class="mt-7 font-sans font-light text-[14px] leading-relaxed text-testo-soft whitespace-pre-line">
                    {{ $necrologio->testo }}
                </p>
            @endif

            @if ($necrologio->manifesto)
                <div class="mt-8">
                    <a href="{{ $necrologio->urlManifesto($agenzia->slug) }}" class="group inline-block">
                        @if ($necrologio->manifestoAnteprimaUrl())
                            {{-- Il manifesto può essere verticale (50x70, 70x100…) o orizzontale
                                 (61x45, 50x32…): max-h e max-w insieme, mai una misura fissa, così
                                 l'anteprima si adatta a entrambi senza deformarsi né traboccare. --}}
                            <img src="{{ $necrologio->manifestoAnteprimaUrl() }}" alt="Manifesto di {{ $nome }}"
                                 class="mx-auto max-h-64 max-w-full w-auto h-auto border border-caffe/15
                                        shadow-[0_10px_30px_rgba(58,46,34,0.14)]
                                        transition-opacity duration-300 group-hover:opacity-80">
                        @endif
                        <span class="mt-3 block font-sans text-[11px] tracking-[0.22em] uppercase text-oro-scuro
                                     group-hover:text-caffe transition-colors duration-300
                                     underline underline-offset-4 decoration-oro/40">
                            Guarda il manifesto
                        </span>
                    </a>
                </div>
            @endif
        </div>

        {{-- ============ condivisione, dentro la card ============ --}}
        <div class="border-t border-caffe/10 bg-panna/50 px-8 py-6 text-center">
            <p class="font-sans text-[10px] tracking-[0.24em] uppercase text-testo-soft">
                Fatelo sapere a chi vorrebbe esserci
            </p>

            <div class="mt-4 flex flex-wrap justify-center gap-3">
                <a href="{{ 'https://wa.me/?text='.rawurlencode($testoCondivisione.' '.$indirizzo) }}"
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

        <form method="POST" action="{{ route('necrologio.messaggio', ['agenzia' => $agenzia->slug, 'percorso' => $necrologio->percorso]) }}"
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

@if ($necrologio->embeddabile())
    @push('scripts')
        <script>
            // In esecuzione dentro l'iframe di chi ha comprato l'embed: comunica
            // l'altezza reale al genitore così lo snippet può ridimensionare senza
            // barre di scorrimento. Fuori da un iframe non fa nulla.
            (function () {
                if (window.self === window.top) return;

                function invia() {
                    window.parent.postMessage({
                        memorai: true,
                        tipo: 'necrologio-altezza',
                        altezza: document.documentElement.scrollHeight,
                    }, '*');
                }

                window.addEventListener('load', invia);
                if (window.ResizeObserver) {
                    new ResizeObserver(invia).observe(document.body);
                } else {
                    window.setInterval(invia, 1000);
                }
            })();
        </script>
    @endpush
@endif
@endsection
