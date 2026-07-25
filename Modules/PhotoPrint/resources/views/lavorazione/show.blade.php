@extends('layouts.account')

@section('title', 'Lavorazione ordine '.$ordine->numero.' — MemorAI')
@section('titolo', 'La fotografia del ricordo')
@section('sottotitolo', 'Ordine '.$ordine->numero.' · qui prepariamo insieme il ritratto e le parole.')

@section('account')

@if (session('stato'))
    <p class="mb-8 border-l-2 border-successo bg-panna px-5 py-4 font-sans text-[13px]">{{ session('stato') }}</p>
@endif

@php
    $passi = [
        ['n' => 1, 'titolo' => 'I dati della persona', 'fatto' => (bool) $defunto],
        ['n' => 2, 'titolo' => 'La fotografia', 'fatto' => $foto->isNotEmpty()],
        ['n' => 3, 'titolo' => 'Il ricordino e la preghiera', 'fatto' => (bool) $ricordino],
        ['n' => 4, 'titolo' => 'La tua approvazione', 'fatto' => $ricordino?->stato === 'approvato'],
    ];
@endphp

{{-- ============ i quattro passi ============ --}}
<ol class="flex flex-wrap gap-x-8 gap-y-3 font-sans text-[11px] tracking-[0.16em] uppercase">
    @foreach ($passi as $passo)
        <li @class(['flex items-center gap-2', 'text-successo' => $passo['fatto'], 'text-testo-soft/60' => ! $passo['fatto']])>
            <span aria-hidden="true" class="w-[0.45rem] h-[0.45rem] rotate-45 {{ $passo['fatto'] ? 'bg-successo' : 'bg-caffe/25' }}"></span>
            {{ $passo['titolo'] }}
        </li>
    @endforeach
</ol>

<div class="mt-10 space-y-px bg-caffe/15 border border-caffe/15">

    {{-- ============ 1. il defunto ============ --}}
    <section class="bg-bianco px-7 py-8">
        <header class="flex flex-wrap items-baseline justify-between gap-3">
            <h2 class="font-serif text-2xl font-medium">Di chi parliamo</h2>
            @if ($defunto)
                <span class="font-sans text-[10px] tracking-[0.2em] uppercase text-successo">Registrato</span>
            @endif
        </header>

        <p class="mt-2 max-w-2xl font-sans font-light text-[14px] leading-relaxed text-testo-soft">
            Questi dati compongono il ricordino: nome, date e la frase che volete accanto al ritratto.
        </p>

        <form method="POST" action="{{ route('lavorazione.defunto', $ordine) }}" class="mt-6 max-w-2xl space-y-6">
            @csrf

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <x-input-label for="nome" value="Nome" />
                    <x-text-input id="nome" name="nome" required :value="old('nome', $defunto?->nome)" />
                    <x-input-error :messages="$errors->get('nome')" />
                </div>
                <div>
                    <x-input-label for="cognome" value="Cognome" />
                    <x-text-input id="cognome" name="cognome" required :value="old('cognome', $defunto?->cognome)" />
                    <x-input-error :messages="$errors->get('cognome')" />
                </div>
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <x-input-label for="data_nascita" value="Data di nascita" />
                    <x-text-input id="data_nascita" name="data_nascita" type="date"
                        :value="old('data_nascita', $defunto?->data_nascita?->format('Y-m-d'))" />
                    <x-input-error :messages="$errors->get('data_nascita')" />
                </div>
                <div>
                    <x-input-label for="data_morte" value="Data di mancanza" />
                    <x-text-input id="data_morte" name="data_morte" type="date"
                        :value="old('data_morte', $defunto?->data_morte?->format('Y-m-d'))" />
                    <x-input-error :messages="$errors->get('data_morte')" />
                </div>
            </div>

            <div>
                <x-input-label for="frase" value="Frase di apertura" />
                <x-text-input id="frase" name="frase"
                    :value="old('frase', $defunto?->frase ?? 'È mancata all\'affetto dei suoi cari')" />
                <x-input-error :messages="$errors->get('frase')" />
            </div>

            <div>
                <x-input-label for="preghiera" value="Preghiera o dedica (sul retro)" />
                <textarea id="preghiera" name="preghiera" rows="4"
                          class="block w-full bg-bianco border border-caffe/25 px-4 py-3 font-sans font-light
                                 text-[15px] leading-relaxed focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40"
                >{{ old('preghiera', $defunto?->preghiera ?? "L'eterno riposo dona a lei, o Signore,\ne splenda a lei la luce perpetua.\nRiposi in pace. Amen.") }}</textarea>
                <x-input-error :messages="$errors->get('preghiera')" />
            </div>

            {{-- consenso: senza, non si lavora la fotografia --}}
            <div class="border-l-2 border-oro bg-panna/60 px-5 py-5 space-y-4">
                <h3 class="font-sans text-[11px] tracking-[0.22em] uppercase text-oro-scuro">Consenso</h3>

                <div>
                    <x-input-label for="gdpr_parentela" value="Che parentela hai con la persona" />
                    <x-text-input id="gdpr_parentela" name="gdpr_parentela" required
                        placeholder="figlia, nipote, coniuge…"
                        :value="old('gdpr_parentela', $defunto?->gdpr_parentela)" />
                    <x-input-error :messages="$errors->get('gdpr_parentela')" />
                </div>

                <label for="gdpr_consenso" class="flex items-start gap-3 cursor-pointer">
                    <input id="gdpr_consenso" name="gdpr_consenso" type="checkbox" value="1"
                           class="mt-1 h-4 w-4 accent-oro" @checked(old('gdpr_consenso', $defunto?->gdpr_consenso))>
                    <span class="font-sans font-light text-[13px] leading-relaxed text-testo-soft">
                        Autorizzo MemorAI a usare la fotografia e i dati qui indicati per realizzare
                        gli articoli di questo ordine. Sono un familiare e posso darne il consenso.
                    </span>
                </label>
                <x-input-error :messages="$errors->get('gdpr_consenso')" />
            </div>

            <x-primary-button>{{ $defunto ? 'Aggiorna i dati' : 'Salva e prosegui' }}</x-primary-button>
        </form>
    </section>

    {{-- ============ 2. la foto ============ --}}
    <section class="bg-bianco px-7 py-8 {{ $defunto ? '' : 'opacity-45 pointer-events-none' }}">
        <header class="flex flex-wrap items-baseline justify-between gap-3">
            <h2 class="font-serif text-2xl font-medium">La fotografia</h2>
            @if ($foto->isNotEmpty())
                <span class="font-sans text-[10px] tracking-[0.2em] uppercase text-successo">
                    {{ $foto->count() }} {{ $foto->count() === 1 ? 'immagine' : 'immagini' }}
                </span>
            @endif
        </header>

        <p class="mt-2 max-w-2xl font-sans font-light text-[14px] leading-relaxed text-testo-soft">
            Carica il ritratto e lascialo elaborare: sfondo pulito, luce uniforme, volto rispettato.
            Se non sei soddisfatto puoi rifarlo quante volte vuoi.
        </p>

        @if ($foto->isNotEmpty())
            <div class="mt-5 flex flex-wrap gap-3">
                @foreach ($foto->take(6) as $immagine)
                    <figure class="w-24 border {{ $immagine->is_principale ? 'border-oro border-2' : 'border-caffe/25' }} bg-panna aspect-[4/5] overflow-hidden">
                        <img src="{{ $immagine->url() }}" alt="" class="h-full w-full object-cover">
                    </figure>
                @endforeach
            </div>
        @endif

        <div class="mt-6">
            <x-button :href="route('studio.foto')">
                {{ $foto->isEmpty() ? 'Apri il Foto Manager' : 'Riprendi la fotografia' }}
            </x-button>
        </div>
    </section>

    {{-- ============ 3. il ricordino ============ --}}
    <section class="bg-bianco px-7 py-8 {{ $foto->isNotEmpty() ? '' : 'opacity-45 pointer-events-none' }}">
        <header class="flex flex-wrap items-baseline justify-between gap-3">
            <h2 class="font-serif text-2xl font-medium">Il ricordino</h2>
            @if ($ricordino)
                <span class="font-sans text-[10px] tracking-[0.2em] uppercase text-successo">Bozza salvata</span>
            @endif
        </header>

        <p class="mt-2 max-w-2xl font-sans font-light text-[14px] leading-relaxed text-testo-soft">
            Componi fronte e retro: il ritratto, le date, la preghiera. Puoi partire da uno dei
            nostri modelli e cambiare quello che vuoi.
        </p>

        @if ($ricordino)
            <x-bozza-ricordino :ricordino="$ricordino" class="mt-5" />

            {{-- I giri di approvazione con la famiglia --}}
            @php $revisioni = $ricordino->revisioni; @endphp
            @if ($revisioni->isNotEmpty())
                <section class="mt-7 border-l-2 border-caffe/20 pl-5">
                    <h3 class="font-sans text-[11px] tracking-[0.22em] uppercase text-oro-scuro">
                        Con la famiglia
                    </h3>
                    <ol class="mt-3 space-y-3">
                        @foreach ($revisioni as $revisione)
                            <li class="font-sans font-light text-[13px] leading-relaxed">
                                <span class="text-testo-soft">
                                    {{ $revisione->inviata_at->format('d/m/Y H:i') }} ·
                                    inviata a {{ $revisione->inviata_a }} ·
                                </span>
                                <span @class([
                                    'text-successo' => $revisione->esito === \Modules\Memorial\Models\RevisioneRicordino::APPROVATA,
                                    'text-oro-scuro' => $revisione->esito === \Modules\Memorial\Models\RevisioneRicordino::MODIFICHE,
                                    'text-testo-soft' => $revisione->inAttesa(),
                                ])>{{ $revisione->esitoLeggibile() }}</span>

                                @if ($revisione->nota)
                                    <span class="mt-1 block text-testo">“{{ $revisione->nota }}”</span>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endif
        @endif

        <div class="mt-6">
            <x-button :href="route('studio.ricordino')">
                {{ $ricordino ? 'Riprendi la bozza' : 'Apri il Designer' }}
            </x-button>
        </div>
    </section>

    {{-- ============ 4. approvazione ============ --}}
    <section class="bg-panna/60 px-7 py-8">
        <h2 class="font-serif text-2xl font-medium">La tua approvazione</h2>
        <p class="mt-2 max-w-2xl font-sans font-light text-[14px] leading-relaxed text-testo-soft">
            Quando la bozza ti convince, approvala: da quel momento andiamo in stampa e non
            si può più correggere. Prenditi il tempo che ti serve.
        </p>

        @if ($ricordino)
            <form method="POST" action="{{ route('lavorazione.approva', $ordine) }}" class="mt-6">
                @csrf
                <x-primary-button>Approvo, mandate in stampa</x-primary-button>
            </form>
        @else
            <p class="mt-6 font-sans font-light text-[13px] text-testo-soft">
                Il pulsante compare quando c'è una bozza da approvare.
            </p>
        @endif
    </section>
</div>

<p class="mt-8">
    <a href="{{ route('ordine', $ordine) }}"
       class="font-sans text-[11px] tracking-[0.2em] uppercase text-testo-soft hover:text-oro-scuro transition-colors duration-300">
        ← Torna all'ordine
    </a>
</p>
@endsection
