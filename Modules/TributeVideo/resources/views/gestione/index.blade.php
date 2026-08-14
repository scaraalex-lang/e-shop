@extends('layouts.gestione')

@section('title', 'Video memoriale — Gestione MemorAI')
@section('titolo', 'Video memoriale (test)')

@section('gestione')
    <p class="font-sans text-[13px] text-testo-soft max-w-xl">
        Pagina di test ad accesso diretto: genera un video vero (foto + audio
        opzionale + testo) senza passare da carrello/Stripe, per validare la
        pipeline (proxy Python → coda → Cloudinary) prima di agganciarla al
        checkout reale. Il video nasce da testo libero, non da un defunto reale.
    </p>

    <form method="POST" action="{{ route('gestione.video-memoriale.store') }}"
          enctype="multipart/form-data" class="mt-8 max-w-xl space-y-6">
        @csrf

        <div>
            <x-input-label for="nome_completo" value="Nome e cognome" />
            <x-text-input id="nome_completo" name="nome_completo"
                          value="{{ old('nome_completo') }}" required autofocus />
            <x-input-error :messages="$errors->get('nome_completo')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="date" value="Date (facoltativo, es. 1950 - 2026)" />
            <x-text-input id="date" name="date" value="{{ old('date') }}" />
            <x-input-error :messages="$errors->get('date')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="citazione" value="Citazione di chiusura (facoltativa)" />
            <textarea id="citazione" name="citazione" rows="3"
                      class="block w-full bg-bianco border border-caffe/25 px-4 py-3 font-sans font-light text-[15px]
                             focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40">{{ old('citazione') }}</textarea>
            <x-input-error :messages="$errors->get('citazione')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="foto" value="Fotografie (in ordine, almeno una)" />
            <input id="foto" name="foto[]" type="file" accept="image/*" multiple required
                   class="block w-full bg-bianco border border-caffe/25 px-4 py-3 font-sans font-light text-[14px]
                          focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40">
            <x-input-error :messages="$errors->get('foto')" class="mt-2" />
            <x-input-error :messages="$errors->get('foto.*')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="audio" value="Audio di sottofondo (facoltativo)" />
            <input id="audio" name="audio" type="file" accept="audio/*"
                   class="block w-full bg-bianco border border-caffe/25 px-4 py-3 font-sans font-light text-[14px]
                          focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40">
            <x-input-error :messages="$errors->get('audio')" class="mt-2" />
        </div>

        <x-primary-button>Genera il video</x-primary-button>
    </form>

    @if ($recenti->isNotEmpty())
        <div class="mt-14 max-w-xl">
            <span class="font-sans text-[11px] tracking-[0.22em] uppercase text-testo-soft">
                Test recenti
            </span>
            <ul class="mt-3 divide-y divide-caffe/15">
                @foreach ($recenti as $v)
                    <li class="py-3 flex items-center justify-between gap-4">
                        <a href="{{ route('gestione.video-memoriale.show', $v) }}"
                           class="font-sans text-[14px] text-testo hover:text-oro-scuro transition-colors duration-300">
                            {{ $v->nome_completo }}
                        </a>
                        <span class="font-sans text-[11px] tracking-[0.12em] uppercase text-testo-soft">
                            {{ $v->stato->etichetta() }}
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
@endsection
