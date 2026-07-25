@extends('layouts.gestione')

@section('title', $slide->exists ? 'Modifica slide' : 'Nuova slide')

@section('content')
@php
    $campo = 'w-full border-2 border-caffe bg-bianco px-4 py-3 font-sans text-[15px] text-testo '
           . 'placeholder:text-testo-soft/60 focus:outline-none focus:border-oro-scuro transition-colors';
    $etichetta = 'block font-sans text-[11px] tracking-[0.28em] uppercase text-oro-scuro mb-2';

    // scorciatoie di destinazione più usate, per non far scrivere il path a mano
    $mete = [
        '/prenota/ricordino'                => 'Prenota i ricordini (flusso completo)',
        '/categoria/photoceramiche'         => 'Photoceramiche',
        '/categoria/articoli-trigesimali'   => 'Articoli trigesimali',
        '/categoria/devozionali'            => 'Devozionali',
        '/studio/foto'                      => 'Foto Manager',
    ];
@endphp

<div class="max-w-3xl">
    <h1 class="font-serif text-4xl text-caffe">{{ $slide->exists ? 'Modifica slide' : 'Nuova slide' }}</h1>

    @if ($errors->any())
        <div role="alert" class="mt-6 border-2 border-oro-scuro bg-panna px-5 py-4">
            <ul class="space-y-1 font-sans font-light text-[14px] text-testo">
                @foreach ($errors->all() as $errore)
                    <li>· {{ $errore }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" enctype="multipart/form-data" class="mt-8 space-y-9"
          action="{{ $slide->exists ? route('gestione.slide.update', $slide) : route('gestione.slide.store') }}">
        @csrf
        @if ($slide->exists) @method('PUT') @endif

        {{-- ---------- testi ---------- --}}
        <fieldset class="space-y-6">
            <legend class="font-sans text-[11px] tracking-[0.28em] uppercase text-testo-soft mb-4">Testi</legend>

            <div>
                <label for="occhiello" class="{{ $etichetta }}">Occhiello</label>
                <input id="occhiello" name="occhiello" type="text" placeholder="Ricordini trigesimali"
                       value="{{ old('occhiello', $slide->occhiello) }}" class="{{ $campo }}">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="sm:col-span-2">
                    <label for="titolo" class="{{ $etichetta }}">Titolo *</label>
                    <input id="titolo" name="titolo" type="text" required
                           value="{{ old('titolo', $slide->titolo) }}" class="{{ $campo }}">
                </div>
                <div>
                    <label for="titolo_corsivo" class="{{ $etichetta }}">Coda in corsivo oro</label>
                    <input id="titolo_corsivo" name="titolo_corsivo" type="text" placeholder="parola"
                           value="{{ old('titolo_corsivo', $slide->titolo_corsivo) }}" class="{{ $campo }}">
                </div>
            </div>

            <div>
                <label for="testo" class="{{ $etichetta }}">Testo</label>
                <textarea id="testo" name="testo" rows="3" class="{{ $campo }}">{{ old('testo', $slide->testo) }}</textarea>
            </div>
        </fieldset>

        {{-- ---------- immagine ---------- --}}
        <fieldset class="space-y-6 border-2 border-caffe px-6 py-6">
            <legend class="px-2 font-sans text-[11px] tracking-[0.28em] uppercase text-oro-scuro">Immagine</legend>

            @if ($slide->immagineUrl())
                <figure class="w-40 border-2 border-caffe bg-panna aspect-[4/5] overflow-hidden">
                    <img src="{{ $slide->immagineUrl() }}" alt="" class="h-full w-full object-cover">
                </figure>
            @endif

            <div>
                <label for="file_immagine" class="{{ $etichetta }}">Carica una foto</label>
                <input id="file_immagine" name="file_immagine" type="file" accept="image/jpeg,image/png,image/webp"
                       class="w-full font-sans text-[14px] text-testo-soft file:mr-4 file:border-2 file:border-caffe
                              file:bg-bianco file:px-4 file:py-2 file:font-sans file:text-[12px] file:uppercase
                              file:tracking-[0.16em] file:text-caffe hover:file:bg-panna file:cursor-pointer">
                <p class="mt-2 font-sans font-light text-[13px] text-testo-soft">
                    JPG, PNG o WEBP fino a 5 MB. Resa migliore in verticale (4:5).
                </p>
            </div>

            <div>
                <label for="immagine" class="{{ $etichetta }}">Oppure percorso già in archivio</label>
                <input id="immagine" name="immagine" type="text" placeholder="categories/rosari.jpg"
                       value="{{ old('immagine', $slide->immagine) }}" class="{{ $campo }}">
            </div>

            <div>
                <label for="immagine_alt" class="{{ $etichetta }}">Testo alternativo</label>
                <input id="immagine_alt" name="immagine_alt" type="text"
                       value="{{ old('immagine_alt', $slide->immagine_alt) }}" class="{{ $campo }}">
            </div>
        </fieldset>

        {{-- ---------- destinazioni ---------- --}}
        <fieldset class="space-y-6 border-2 border-caffe px-6 py-6">
            <legend class="px-2 font-sans text-[11px] tracking-[0.28em] uppercase text-oro-scuro">Dove porta</legend>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="cta_label" class="{{ $etichetta }}">Pulsante principale</label>
                    <input id="cta_label" name="cta_label" type="text" placeholder="Prenota i ricordini"
                           value="{{ old('cta_label', $slide->cta_label) }}" class="{{ $campo }}">
                </div>
                <div>
                    <label for="cta_href" class="{{ $etichetta }}">Destinazione</label>
                    <input id="cta_href" name="cta_href" type="text" list="mete"
                           value="{{ old('cta_href', $slide->cta_href) }}" class="{{ $campo }}">
                </div>
                <div>
                    <label for="cta2_label" class="{{ $etichetta }}">Pulsante secondario</label>
                    <input id="cta2_label" name="cta2_label" type="text"
                           value="{{ old('cta2_label', $slide->cta2_label) }}" class="{{ $campo }}">
                </div>
                <div>
                    <label for="cta2_href" class="{{ $etichetta }}">Destinazione</label>
                    <input id="cta2_href" name="cta2_href" type="text" list="mete"
                           value="{{ old('cta2_href', $slide->cta2_href) }}" class="{{ $campo }}">
                </div>
            </div>

            <datalist id="mete">
                @foreach ($mete as $href => $descrizione)
                    <option value="{{ $href }}">{{ $descrizione }}</option>
                @endforeach
            </datalist>
        </fieldset>

        {{-- ---------- pubblicazione ---------- --}}
        <fieldset class="grid grid-cols-1 sm:grid-cols-2 gap-6 items-end">
            <div>
                <label for="sort_order" class="{{ $etichetta }}">Posizione</label>
                <input id="sort_order" name="sort_order" type="number" min="0" max="9999" required
                       value="{{ old('sort_order', $slide->sort_order ?? 10) }}" class="{{ $campo }}">
            </div>

            <label class="flex items-center gap-3 cursor-pointer pb-3">
                <input type="checkbox" name="is_active" value="1"
                       @checked(old('is_active', $slide->is_active ?? true))
                       class="h-4 w-4 accent-[#a5863f]">
                <span class="font-sans text-[14px] text-testo">Pubblicata nel carosello</span>
            </label>
        </fieldset>

        <div class="flex flex-wrap items-center gap-4">
            <x-button variant="piena" type="submit">Salva</x-button>
            <a href="{{ route('gestione.slide.index') }}"
               class="font-sans text-[12px] tracking-[0.16em] uppercase text-testo-soft hover:text-oro-scuro transition-colors">
                Annulla
            </a>
        </div>
    </form>
</div>
@endsection
