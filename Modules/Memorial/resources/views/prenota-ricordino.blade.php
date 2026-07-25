@extends('layouts.app')

@section('title', 'Prenota i ricordini — MemorAI')
@section('meta_description', 'Registra i dati della persona da ricordare e componi il ricordino: fotografia, testi e stampa, passo dopo passo.')

@section('content')
@php
    $campo = 'w-full border-2 border-caffe bg-bianco px-4 py-3 font-sans text-[15px] text-testo '
           . 'placeholder:text-testo-soft/60 focus:outline-none focus:border-oro-scuro transition-colors';
    $etichetta = 'block font-sans text-[11px] tracking-[0.28em] uppercase text-oro-scuro mb-2';
@endphp

<div class="max-w-3xl">

    <x-section-title occhiello="Ricordini" titolo="I dati della persona da ricordare" allineamento="left" />

    <p class="mt-5 font-sans font-light text-testo-soft leading-relaxed">
        Bastano nome e cognome per iniziare: date, frase e preghiera si possono aggiungere o
        cambiare anche dopo, mentre componi il ricordino.
    </p>

    {{-- i tre passi del flusso --}}
    <ol class="mt-10 grid grid-cols-1 sm:grid-cols-3 gap-px bg-caffe/20 border-2 border-caffe">
        @foreach ([
            ['1', 'Dati', 'Chi ricordiamo e chi autorizza.'],
            ['2', 'Fotografia', 'Ritaglio, ritocco e sfondo nel Foto Manager.'],
            ['3', 'Ricordino', 'Composizione fronte e retro, pronta per la stampa.'],
        ] as $i => [$numero, $titolo, $testo])
            {{-- il passo corrente a pieno contrasto, i successivi smorzati:
                 l'opacità sta sul contenuto, non sul riquadro, altrimenti
                 trasparirebbe la linea di separazione sotto --}}
            <li class="bg-bianco px-5 py-6">
                <div class="{{ $i === 0 ? '' : 'opacity-55' }}">
                    <span class="font-serif text-3xl text-oro">{{ $numero }}</span>
                    <h3 class="mt-2 font-serif text-xl text-caffe">{{ $titolo }}</h3>
                    <p class="mt-1 font-sans font-light text-[13px] text-testo-soft leading-relaxed">{{ $testo }}</p>
                </div>
            </li>
        @endforeach
    </ol>

    @if ($errors->any())
        <div role="alert" class="mt-10 border-2 border-oro-scuro bg-panna px-5 py-4">
            <p class="font-sans text-[13px] tracking-[0.1em] uppercase text-oro-scuro">Controlla questi campi</p>
            <ul class="mt-2 space-y-1 font-sans font-light text-[14px] text-testo">
                @foreach ($errors->all() as $errore)
                    <li>· {{ $errore }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('prenota.ricordino.store') }}" class="mt-10 space-y-10">
        @csrf

        {{-- ---------- persona ---------- --}}
        <fieldset class="space-y-6">
            <legend class="sr-only">Dati della persona</legend>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="nome" class="{{ $etichetta }}">Nome *</label>
                    <input id="nome" name="nome" type="text" required autocomplete="off"
                           value="{{ old('nome') }}" class="{{ $campo }}">
                </div>
                <div>
                    <label for="cognome" class="{{ $etichetta }}">Cognome *</label>
                    <input id="cognome" name="cognome" type="text" required autocomplete="off"
                           value="{{ old('cognome') }}" class="{{ $campo }}">
                </div>
                <div>
                    <label for="data_nascita" class="{{ $etichetta }}">Data di nascita</label>
                    <input id="data_nascita" name="data_nascita" type="date"
                           value="{{ old('data_nascita') }}" class="{{ $campo }}">
                </div>
                <div>
                    <label for="data_morte" class="{{ $etichetta }}">Data di mancanza</label>
                    <input id="data_morte" name="data_morte" type="date"
                           value="{{ old('data_morte') }}" class="{{ $campo }}">
                </div>
            </div>

            <div>
                <label for="frase" class="{{ $etichetta }}">Frase di ricordo</label>
                <textarea id="frase" name="frase" rows="3"
                          placeholder="Una frase breve, come la direbbe la famiglia."
                          class="{{ $campo }}">{{ old('frase') }}</textarea>
            </div>

            <div>
                <label for="preghiera" class="{{ $etichetta }}">Preghiera</label>
                <textarea id="preghiera" name="preghiera" rows="4"
                          placeholder="Se ne desiderate una in particolare. Altrimenti la scegliamo insieme dopo."
                          class="{{ $campo }}">{{ old('preghiera') }}</textarea>
            </div>
        </fieldset>

        {{-- ---------- consenso ---------- --}}
        <fieldset class="border-2 border-caffe bg-panna px-6 py-6 space-y-6">
            <legend class="px-2 font-sans text-[11px] tracking-[0.28em] uppercase text-oro-scuro">
                Consenso al trattamento
            </legend>

            <p class="font-sans font-light text-[14px] text-testo-soft leading-relaxed">
                Fotografia e dati di una persona mancata si possono usare solo se autorizzati da un
                familiare o da chi ne ha titolo. Registriamo qui chi ci autorizza, e quando.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="gdpr_autorizzato_da" class="{{ $etichetta }}">Chi autorizza *</label>
                    <input id="gdpr_autorizzato_da" name="gdpr_autorizzato_da" type="text" required
                           value="{{ old('gdpr_autorizzato_da') }}" class="{{ $campo }}">
                </div>
                <div>
                    <label for="gdpr_parentela" class="{{ $etichetta }}">Parentela</label>
                    <input id="gdpr_parentela" name="gdpr_parentela" type="text"
                           placeholder="figlia, coniuge, nipote…"
                           value="{{ old('gdpr_parentela') }}" class="{{ $campo }}">
                </div>
            </div>

            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="gdpr_consenso" value="1" required
                       @checked(old('gdpr_consenso'))
                       class="mt-1 h-4 w-4 shrink-0 accent-[#a5863f]">
                <span class="font-sans font-light text-[14px] text-testo leading-relaxed">
                    Dichiaro di essere autorizzato a fornire i dati e l'immagine della persona indicata,
                    per la realizzazione dei ricordini. *
                </span>
            </label>
        </fieldset>

        <div class="flex flex-wrap items-center gap-4">
            <x-button variant="piena" type="submit">Prosegui con la fotografia</x-button>
            <a href="{{ url('/') }}"
               class="font-sans text-[12px] tracking-[0.16em] uppercase text-testo-soft hover:text-oro-scuro transition-colors">
                Annulla
            </a>
        </div>
    </form>
</div>
@endsection
