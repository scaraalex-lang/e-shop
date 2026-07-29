@extends('layouts.gestione')

@section('title', $preghiera->exists ? 'Modifica testo' : 'Nuovo testo')
@section('titolo', $preghiera->exists ? 'Modifica testo' : 'Nuovo testo')

@section('gestione')
@php
    $campo = 'w-full border-2 border-caffe bg-bianco px-4 py-3 font-sans text-[15px] text-testo '
           . 'placeholder:text-testo-soft/60 focus:outline-none focus:border-oro-scuro transition-colors';
    $etichetta = 'block font-sans text-[11px] tracking-[0.28em] uppercase text-oro-scuro mb-2';
@endphp

<div class="max-w-2xl">
    <a href="{{ route('gestione.preghiere.index') }}"
       class="font-sans text-[11px] tracking-[0.22em] uppercase text-testo-soft hover:text-oro-scuro transition-colors duration-300">
        ← Archivio preghiere
    </a>

    @if ($errors->any())
        <div role="alert" class="mt-6 border-2 border-oro-scuro bg-panna px-5 py-4">
            <ul class="space-y-1 font-sans font-light text-[14px] text-testo">
                @foreach ($errors->all() as $errore)
                    <li>· {{ $errore }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" class="mt-8 space-y-7"
          action="{{ $preghiera->exists ? route('gestione.preghiere.update', $preghiera) : route('gestione.preghiere.store') }}">
        @csrf
        @if ($preghiera->exists) @method('PUT') @endif

        <div>
            <label for="titolo" class="{{ $etichetta }}">Titolo *</label>
            <input id="titolo" name="titolo" type="text" required
                   value="{{ old('titolo', $preghiera->titolo) }}" class="{{ $campo }}">
        </div>

        <div>
            <label for="testo" class="{{ $etichetta }}">Testo *</label>
            <textarea id="testo" name="testo" rows="6" required
                      class="{{ $campo }} font-serif text-[17px] leading-relaxed">{{ old('testo', $preghiera->testo) }}</textarea>
            <p class="mt-2 font-sans font-light text-[13px] text-testo-soft">
                Vai a capo dove vuoi che vada a capo sul ricordino: le righe si rispettano.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 items-end">
            <div>
                <label for="categoria" class="{{ $etichetta }}">Categoria</label>
                <input id="categoria" name="categoria" type="text" list="categorie"
                       placeholder="Preghiere" value="{{ old('categoria', $preghiera->categoria) }}" class="{{ $campo }}">
                <datalist id="categorie">
                    <option value="Preghiere"></option>
                    <option value="Salmi"></option>
                    <option value="Frasi brevi"></option>
                </datalist>
            </div>
            <div>
                <label for="sort_order" class="{{ $etichetta }}">Posizione</label>
                <input id="sort_order" name="sort_order" type="number" min="0" max="9999" required
                       value="{{ old('sort_order', $preghiera->sort_order ?? 10) }}" class="{{ $campo }}">
            </div>
        </div>

        <label class="flex items-center gap-3 cursor-pointer">
            <input type="checkbox" name="is_active" value="1"
                   @checked(old('is_active', $preghiera->is_active ?? true))
                   class="h-4 w-4 accent-[#a5863f]">
            <span class="font-sans text-[14px] text-testo">Visibile nella galleria del Ricordino Designer</span>
        </label>

        <div class="flex flex-wrap items-center gap-4">
            <x-button variant="piena" type="submit">Salva</x-button>
            <a href="{{ route('gestione.preghiere.index') }}"
               class="font-sans text-[12px] tracking-[0.16em] uppercase text-testo-soft hover:text-oro-scuro transition-colors">
                Annulla
            </a>
        </div>
    </form>
</div>
@endsection
