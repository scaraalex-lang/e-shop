@extends('layouts.account')

@section('title', 'Pubblicazione social — '.$defunto->nomeCompleto().' — MemorAI')
@section('titolo', 'Pubblicazione social')
@section('sottotitolo', $defunto->nomeCompleto())

@php
    $reelInCorso = $reel && ! in_array($reel->stato->value, ['pronto', 'errore'], true);
    $storiaPronta = $storia?->pronta() ?? false;
    $videoPronto = $video?->pronto() ?? false;
@endphp

@if ($reelInCorso)
    @push('meta')
        {{-- Polling senza JS, stesso schema di Video Memoriale. --}}
        <meta http-equiv="refresh" content="4">
    @endpush
@endif

@section('account')
<div class="max-w-2xl space-y-10">

    @if (session('stato'))
        <p class="border-l-2 border-successo bg-panna px-5 py-4 font-sans text-[13px]">{{ session('stato') }}</p>
    @endif

    <p class="font-sans font-light text-[14px] leading-relaxed text-testo-soft">
        La storia e il video, uniti in un unico reel verticale pronto per Facebook e
        Instagram — oppure, se preferisci, i due link separati da incollare a mano come
        due slide di una storia.
    </p>

    {{-- ============ storia social ============ --}}
    <div class="border border-caffe/15 bg-bianco px-6 py-8">
        <h2 class="font-serif text-xl font-medium">Storia social</h2>

        @if (! $storiaPronta)
            <p class="mt-2 font-sans font-light text-[13px] text-testo-soft">Non ancora creata.</p>
            <div class="mt-4">
                <x-button :href="route('defunti.storia-social.show', $defunto)" variant="contornata">
                    Crea la storia
                </x-button>
            </div>
        @else
            <div class="mt-4 flex items-start gap-4">
                <img src="{{ $storia->anteprimaUrl() }}" alt="Anteprima storia" class="w-20 border border-caffe/15">
                <div class="flex-1">
                    <p class="font-sans text-[11px] tracking-[0.18em] uppercase text-successo">Pronta</p>
                    <p class="mt-2">
                        <a href="{{ route('storia-social.show', $storia) }}" target="_blank"
                           class="font-sans text-[13px] text-oro-scuro break-all hover:underline">
                            {{ route('storia-social.show', $storia) }}
                        </a>
                    </p>
                    <a href="{{ route('defunti.storia-social.show', $defunto) }}"
                       class="mt-2 inline-block font-sans text-[11px] tracking-[0.18em] uppercase text-testo-soft hover:text-oro-scuro transition-colors duration-300">
                        Modifica la storia
                    </a>
                </div>
            </div>
        @endif
    </div>

    {{-- ============ video memoriale ============ --}}
    <div class="border border-caffe/15 bg-bianco px-6 py-8">
        <h2 class="font-serif text-xl font-medium">Video memoriale</h2>

        @if (! $video)
            <p class="mt-2 font-sans font-light text-[13px] text-testo-soft">Non ancora creato.</p>
            <div class="mt-4">
                <x-button :href="route('defunti.video-memoriale.show', $defunto)" variant="contornata">
                    Genera il video
                </x-button>
            </div>
        @else
            <p class="mt-2 font-sans text-[11px] tracking-[0.18em] uppercase {{ $videoPronto ? 'text-successo' : 'text-testo-soft' }}">
                {{ $video->stato->etichetta() }}
            </p>
            @if ($videoPronto)
                <div class="mt-4">
                    <p class="font-sans text-[13px]">
                        <a href="{{ route('video.show', $video) }}" target="_blank"
                           class="text-oro-scuro break-all hover:underline">
                            {{ route('video.show', $video) }}
                        </a>
                    </p>
                    <a href="{{ route('defunti.video-memoriale.show', $defunto) }}"
                       class="mt-2 inline-block font-sans text-[11px] tracking-[0.18em] uppercase text-testo-soft hover:text-oro-scuro transition-colors duration-300">
                        Vedi o modifica il video
                    </a>
                </div>
            @else
                <div class="mt-4">
                    <x-button :href="route('defunti.video-memoriale.show', $defunto)" variant="contornata">
                        Vedi lo stato
                    </x-button>
                </div>
            @endif
        @endif
    </div>

    {{-- ============ reel ============ --}}
    <div class="border border-caffe/15 bg-bianco px-6 py-8">
        <h2 class="font-serif text-xl font-medium">Reel</h2>

        @if (! $reel)
            @if ($storiaPronta && $videoPronto)
                <p class="mt-2 font-sans font-light text-[13px] leading-relaxed text-testo-soft">
                    Storia e video sono pronti: puoi unirli in un unico reel verticale,
                    copertina più video, con un solo link da pubblicare.
                </p>
                <form method="POST" action="{{ route('defunti.pubblicazione-social.crea-reel', $defunto) }}" class="mt-4">
                    @csrf
                    <x-primary-button>Crea reel</x-primary-button>
                </form>
            @elseif ($storiaPronta || $videoPronto)
                <p class="mt-2 font-sans font-light text-[13px] leading-relaxed text-testo-soft">
                    Serve anche {{ $storiaPronta ? 'il video' : 'la storia' }} per creare un reel unico.
                    Nel frattempo puoi già usare {{ $storiaPronta ? 'il link della storia' : 'il link del video' }} qui sopra.
                </p>
            @else
                <p class="mt-2 font-sans font-light text-[13px] text-testo-soft">
                    Crea prima la storia e il video: il reel li unisce in un solo link.
                </p>
            @endif
        @else
            <p class="mt-2 font-sans text-[11px] tracking-[0.18em] uppercase {{ $reel->pronto() ? 'text-successo' : 'text-testo-soft' }}">
                {{ $reel->stato->etichetta() }}
            </p>

            @if ($reelInCorso)
                @include('reelsocial::partials.progresso-render')
            @elseif ($reel->pronto())
                <div class="mt-4 space-y-4">
                    <div class="w-40">
                        <video controls preload="metadata" class="w-full border border-caffe/15" style="aspect-ratio:9/16">
                            <source src="{{ $reel->cloudinary_url }}" type="video/mp4">
                        </video>
                    </div>
                    <p class="font-sans text-[13px]">
                        <a href="{{ route('reel.show', $reel) }}" target="_blank"
                           class="text-oro-scuro break-all hover:underline">
                            {{ route('reel.show', $reel) }}
                        </a>
                    </p>
                </div>
            @else
                <div class="mt-4 border-l-2 border-errore bg-panna px-5 py-4 font-sans text-[13px]">
                    {{ $reel->messaggio_errore ?? 'Errore sconosciuto durante il render.' }}
                </div>
                <form method="POST" action="{{ route('defunti.pubblicazione-social.crea-reel', $defunto) }}" class="mt-4">
                    @csrf
                    <x-primary-button>Riprova</x-primary-button>
                </form>
            @endif
        @endif
    </div>

    <p>
        <a href="{{ route('defunti.show', $defunto) }}"
           class="font-sans text-[11px] tracking-[0.2em] uppercase text-testo-soft hover:text-oro-scuro transition-colors duration-300">
            ← Torna alla scheda del defunto
        </a>
    </p>
</div>
@endsection
