@extends('layouts.gestione')

@section('title', 'Video memoriale — Gestione MemorAI')
@section('titolo', $video->nome_completo)

@php
    $inCorso = ! in_array($video->stato->value, ['pronto', 'errore'], true);
@endphp

@if ($inCorso)
    @push('meta')
        {{-- Polling senza JS: la pagina si aggiorna da sola finché il render non finisce. --}}
        <meta http-equiv="refresh" content="4">
    @endpush
@endif

@section('gestione')
    <a href="{{ route('gestione.video-memoriale.index') }}"
       class="font-sans text-[11px] tracking-[0.22em] uppercase text-testo-soft hover:text-oro-scuro transition-colors duration-300">
        ← Nuovo test
    </a>

    <div class="mt-8 max-w-xl">
        <span class="font-sans text-[11px] tracking-[0.22em] uppercase text-oro-scuro">
            Stato: {{ $video->stato->etichetta() }}
        </span>

        @if ($inCorso)
            @include('tributevideo::partials.progresso-render')
        @elseif ($video->pronto())
            <div class="mt-6 space-y-6">
                <video controls class="w-full border border-caffe/25" preload="metadata">
                    <source src="{{ $video->cloudinary_url }}" type="video/mp4">
                </video>

                <div>
                    <span class="font-sans text-[11px] tracking-[0.22em] uppercase text-testo-soft">
                        Link pubblico (nessuna scadenza legata all'ordine)
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
                        QR (100% locale, endroid/qr-code)
                    </span>
                    <img src="{{ route('video.qr', $video) }}" alt="QR del video memoriale"
                         class="mt-2 w-40 h-40 border border-caffe/25">
                </div>
            </div>
        @else
            <div class="mt-6 border-l-2 border-errore bg-panna px-5 py-4 font-sans text-[13px]">
                {{ $video->messaggio_errore ?? 'Errore sconosciuto durante il render.' }}
            </div>
        @endif
    </div>
@endsection
