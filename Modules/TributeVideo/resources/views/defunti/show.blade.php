@extends('layouts.account')

@section('title', 'Video memoriale — '.$defunto->nomeCompleto().' — MemorAI')
@section('titolo', 'Video memoriale')
@section('sottotitolo', $defunto->nomeCompleto())

@php
    $inCorso = $video && ! in_array($video->stato->value, ['pronto', 'errore'], true);
@endphp

@if ($inCorso)
    @push('meta')
        {{-- Polling senza JS: la pagina si aggiorna da sola finché il render non finisce. --}}
        <meta http-equiv="refresh" content="4">
    @endpush
@endif

@section('account')
<div class="max-w-2xl">

    @if (! $video)
        <p class="font-sans font-light text-[14px] leading-relaxed text-testo-soft">
            Carica le fotografie (nell'ordine in cui vuoi che appaiano) e, se vuoi,
            un audio di sottofondo e una citazione di chiusura. Il video nasce con
            zoom lento (Ken Burns) e dissolvenze — richiede qualche minuto per
            essere pronto.
        </p>

        <form method="POST" action="{{ route('defunti.video-memoriale.store', $defunto) }}"
              enctype="multipart/form-data" class="mt-8 space-y-6">
            @csrf

            @include('tributevideo::partials.editor-foto')

            <div>
                <x-input-label for="audio" value="Audio di sottofondo (facoltativo)" />
                <input id="audio" name="audio" type="file" accept="audio/*"
                       class="block w-full bg-bianco border border-caffe/25 px-4 py-3 font-sans font-light text-[14px]
                              focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40">
                <x-input-error :messages="$errors->get('audio')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="citazione" value="Citazione di chiusura (facoltativa)" />
                <textarea id="citazione" name="citazione" rows="3"
                          class="block w-full bg-bianco border border-caffe/25 px-4 py-3 font-sans font-light text-[15px]
                                 focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40">{{ old('citazione') }}</textarea>
                <x-input-error :messages="$errors->get('citazione')" class="mt-2" />
            </div>

            <x-primary-button>Genera il video</x-primary-button>
        </form>
    @else
        <span class="font-sans text-[11px] tracking-[0.22em] uppercase text-oro-scuro">
            Stato: {{ $video->stato->etichetta() }}
        </span>

        @if ($inCorso)
            <p class="mt-4 font-sans text-[13px] text-testo-soft">
                Il render richiede qualche minuto. Questa pagina si aggiorna
                automaticamente ogni 4 secondi.
            </p>
        @elseif ($video->pronto())
            <div class="mt-6 space-y-6">
                <video controls class="w-full border border-caffe/25" preload="metadata"
                       @if ($video->posterUrl()) poster="{{ $video->posterUrl() }}" @endif>
                    <source src="{{ $video->cloudinary_url }}" type="video/mp4">
                </video>

                <div>
                    <span class="font-sans text-[11px] tracking-[0.22em] uppercase text-testo-soft">
                        Link pubblico (nessuna scadenza legata all'ordine — pensato per il QR fisico)
                    </span>
                    <p class="mt-2">
                        <a href="{{ route('video.show', $video) }}" target="_blank"
                           class="font-sans text-[14px] text-oro-scuro break-all hover:underline">
                            {{ route('video.show', $video) }}
                        </a>
                    </p>
                </div>

                <div>
                    <span class="font-sans text-[11px] tracking-[0.22em] uppercase text-testo-soft">
                        QR da incidere sulla fotoceramica
                    </span>
                    <div class="mt-2 flex items-center gap-5">
                        <img src="{{ route('video.qr', $video) }}" alt="QR del video memoriale"
                             class="w-40 h-40 border border-caffe/25">
                        <a href="{{ route('video.qr', $video) }}" download="video-memoriale-{{ \Illuminate\Support\Str::slug($defunto->nomeCompleto()) }}.png">
                            <x-button variant="contornata">Scarica il QR</x-button>
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="mt-6 border-l-2 border-errore bg-panna px-5 py-4 font-sans text-[13px]">
                {{ $video->messaggio_errore ?? 'Errore sconosciuto durante il render.' }}
            </div>
        @endif
    @endif

    <p class="mt-10">
        <a href="{{ route('defunti.show', $defunto) }}"
           class="font-sans text-[11px] tracking-[0.2em] uppercase text-testo-soft hover:text-oro-scuro transition-colors duration-300">
            ← Torna alla scheda del defunto
        </a>
    </p>
</div>
@endsection
