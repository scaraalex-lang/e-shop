@extends('layouts.account')

@section('title', $defunto->nomeCompleto().' — MemorAI')
@section('titolo', $defunto->nomeCompleto())
@section('sottotitolo', 'Il percorso dei servizi: foto, manifesto, necrologio e ricordino.')

@section('account')
@if (session('stato'))
    <p class="mb-8 border-l-2 border-successo bg-panna px-5 py-4 font-sans text-[13px]">{{ session('stato') }}</p>
@endif

@php
    $formati = [
        'a4p' => 'A4 Portrait (21x29.7cm)', 'a3p' => 'A3 Portrait (29.7x42cm)',
        'a4l' => 'A4 Landscape (29.7x21cm)', 'a3l' => 'A3 Landscape (42x29.7cm)',
        '50x70' => '50x70cm', '70x100' => '70x100cm', '61x45' => 'Manifesto (61x45.7cm)', '50x32' => 'Manifesto (50x32cm)',
    ];
@endphp

{{-- ============ il percorso principale ============ --}}
<div class="grid gap-px bg-caffe/15 border border-caffe/15 sm:grid-cols-4">

    {{-- 1. foto --}}
    <section class="bg-bianco px-6 py-7">
        <h2 class="font-serif text-xl font-medium">1. Foto</h2>
        <p class="mt-2 font-sans text-[10px] tracking-[0.2em] uppercase {{ $fotoBloccata ? 'text-successo' : ($fotoPronta ? 'text-oro-scuro' : 'text-testo-soft') }}">
            @if ($fotoBloccata) 🔒 Confermata
            @elseif ($fotoPronta) Pronta
            @else Da fare
            @endif
        </p>
        <p class="mt-3 font-sans font-light text-[13px] leading-relaxed text-testo-soft">
            @if ($fotoBloccata)
                Foto scelta ed elaborata: pronta per proseguire con le prossime elaborazioni.
            @else
                Carica ed elabora il ritratto: una volta confermata la principale non si torna più indietro.
            @endif
        </p>

        @if ($fotoUrl)
            {{-- Solo per capire a colpo d'occhio quale foto è stata scelta: non
                 riapre il Foto Manager, che a foto confermata è comunque bloccato. --}}
            <div class="mt-5 h-24 w-24 border border-oro/40 bg-panna overflow-hidden">
                <img src="{{ $fotoUrl }}" alt="" class="h-full w-full object-cover">
            </div>
        @elseif ($ordinePrincipale)
            <div class="mt-5">
                <x-button :href="route('studio.foto')">Apri il Foto Manager</x-button>
            </div>
        @endif
    </section>

    {{-- 2. manifesto --}}
    <section class="bg-bianco px-6 py-7 {{ $manifestiAbilitato ? '' : 'opacity-45 pointer-events-none' }}">
        <h2 class="font-serif text-xl font-medium">2. Manifesto</h2>
        <p class="mt-2 font-sans text-[10px] tracking-[0.2em] uppercase {{ $manifesti->isNotEmpty() ? 'text-successo' : 'text-testo-soft' }}">
            {{ $manifesti->isNotEmpty() ? $manifesti->count().' '.($manifesti->count() === 1 ? 'manifesto' : 'manifesti') : 'Da fare' }}
        </p>
        <p class="mt-3 font-sans font-light text-[13px] leading-relaxed text-testo-soft">
            Un defunto può avere più manifesti collegati (funerale, partecipazioni, trigesimo...).
        </p>

        @if ($manifesti->isNotEmpty())
            <ul class="mt-5 space-y-5">
                @foreach ($manifesti as $manifesto)
                    <li class="border border-caffe/15">
                        <div class="aspect-[4/3] border-b border-caffe/15 bg-panna overflow-hidden">
                            @if ($manifesto->webUrl())
                                <img src="{{ $manifesto->webUrl() }}" alt="" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full items-center justify-center">
                                    <span class="font-sans text-[10px] tracking-[0.15em] uppercase text-testo-soft/50">Nessuna anteprima</span>
                                </div>
                            @endif
                        </div>
                        <div class="px-4 py-4">
                            <p class="font-serif text-base truncate">{{ $manifesto->etichetta }}</p>
                            @if ($manifesto->principale)
                                <p class="mt-1 font-sans text-[10px] tracking-[0.15em] uppercase text-oro-scuro">★ Principale</p>
                            @endif

                            <div class="mt-4 space-y-2">
                                <x-button :href="route('manifesti.designer', $manifesto)" class="w-full">Apri</x-button>
                                @unless ($manifesto->principale)
                                    <form method="POST" action="{{ route('manifesti.principale', $manifesto) }}">
                                        @csrf
                                        <x-button variant="contornata" type="submit" class="w-full">Imposta principale</x-button>
                                    </form>
                                @endunless
                                <form method="POST" action="{{ route('manifesti.destroy', $manifesto) }}" onsubmit="return confirm('Eliminare questo manifesto?');">
                                    @csrf @method('DELETE')
                                    <x-danger-button class="w-full">Elimina</x-danger-button>
                                </form>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        @if ($manifestiAbilitato)
            <details class="mt-5">
                <summary class="cursor-pointer font-sans text-[11px] tracking-[0.15em] uppercase text-oro-scuro">+ Nuovo manifesto</summary>
                <form method="POST" action="{{ route('defunti.manifesti.store', $defunto) }}" class="mt-3 space-y-3">
                    @csrf
                    <x-text-input name="etichetta" placeholder="Es. Manifesto funerale" required class="w-full text-sm" />
                    <select name="formato" class="block w-full border-caffe/25 rounded-md text-sm">
                        @foreach ($formati as $valore => $etichetta)
                            <option value="{{ $valore }}">{{ $etichetta }}</option>
                        @endforeach
                    </select>
                    <x-button type="submit">Disegna nuovo</x-button>
                </form>
                <form method="POST" action="{{ route('defunti.manifesti.carica', $defunto) }}" enctype="multipart/form-data" class="mt-4 space-y-3">
                    @csrf
                    <x-text-input name="etichetta" placeholder="Es. Partecipazioni" required class="w-full text-sm" />
                    <input type="file" name="file" accept=".pdf,image/*" required class="block w-full font-sans text-[13px]">
                    <x-secondary-button type="submit">Carica un file</x-secondary-button>
                </form>
            </details>
        @endif
    </section>

    {{-- 3. necrologio --}}
    <section class="bg-bianco px-6 py-7 {{ $necrologioAbilitato ? '' : 'opacity-45 pointer-events-none' }}">
        <h2 class="font-serif text-xl font-medium">3. Necrologio</h2>
        <p class="mt-2 font-sans text-[10px] tracking-[0.2em] uppercase {{ $necrologioFunerale?->pubblicato ? 'text-successo' : ($necrologioFunerale ? 'text-oro-scuro' : 'text-testo-soft') }}">
            @if ($necrologioFunerale?->pubblicato) 🔒 Pubblicato
            @elseif ($necrologioFunerale) Bozza
            @else Da fare
            @endif
        </p>
        <p class="mt-3 font-sans font-light text-[13px] leading-relaxed text-testo-soft">
            Testo, card social e — una volta pubblicato — solo data/ora/luogo restano modificabili.
        </p>

        <div class="mt-5 space-y-3">
            @if ($necrologioFunerale)
                <x-button :href="route('necrologi.modifica', $necrologioFunerale)">Modifica testo e date</x-button><br>
                <x-button :href="route('necrologi.designer', $necrologioFunerale)">Componi la card</x-button>
            @elseif ($necrologioAbilitato)
                <x-button :href="route('necrologi.nuovo', ['defunto_id' => $defunto->id])">Crea il necrologio</x-button>
            @endif
        </div>
    </section>

    {{-- 4. ricordino --}}
    <section class="bg-bianco px-6 py-7 {{ $ricordinoAbilitato ? '' : 'opacity-45 pointer-events-none' }}">
        <h2 class="font-serif text-xl font-medium">4. Ricordino</h2>
        <p class="mt-2 font-sans text-[10px] tracking-[0.2em] uppercase {{ $ricordino ? 'text-successo' : 'text-testo-soft' }}">
            {{ $ricordino ? 'Bozza salvata' : 'Disponibile' }}
        </p>
        <p class="mt-3 font-sans font-light text-[13px] leading-relaxed text-testo-soft">
            Sempre acquistabile: non deve aspettare il manifesto né il necrologio.
        </p>

        @if ($ordinePrincipale)
            @if ($ricordino)
                <div class="mt-5 border border-caffe/15">
                    <div class="aspect-[4/3] border-b border-caffe/15 bg-panna overflow-hidden">
                        @if ($ricordino->anteprima_fronte)
                            <img src="{{ '/storage/'.ltrim($ricordino->anteprima_fronte, '/') }}" alt="" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full items-center justify-center">
                                <span class="font-sans text-[10px] tracking-[0.15em] uppercase text-testo-soft/50">Nessuna anteprima</span>
                            </div>
                        @endif
                    </div>
                    <div class="px-4 py-4">
                        <x-button :href="route('studio.ricordino')" class="w-full">Riprendi la bozza</x-button>
                    </div>
                </div>
            @else
                <div class="mt-5">
                    <x-button :href="route('studio.ricordino')">Apri il Designer</x-button>
                </div>
            @endif
        @endif
    </section>
</div>

{{-- ============ extra: altri necrologi ============ --}}
@if ($necrologiAltri->isNotEmpty())
    <div class="mt-10">
        <h2 class="font-serif text-2xl font-medium">Altri necrologi</h2>
        <p class="mt-2 max-w-2xl font-sans font-light text-[14px] leading-relaxed text-testo-soft">
            Trigesimo, anniversario: acquistabili in qualsiasi momento, non legati al percorso principale.
        </p>

        <ul class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($necrologiAltri as $n)
                <li class="border border-caffe/15">
                    <div class="aspect-[4/3] border-b border-caffe/15 bg-panna overflow-hidden">
                        @if ($n->og_image)
                            <img src="{{ '/storage/'.ltrim($n->og_image, '/') }}" alt="" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full items-center justify-center">
                                <span class="font-sans text-[10px] tracking-[0.15em] uppercase text-testo-soft/50">Nessuna anteprima</span>
                            </div>
                        @endif
                    </div>
                    <div class="px-4 py-4">
                        <p class="font-serif text-base truncate">{{ $n->occasione?->etichetta() ?? 'Necrologio' }}</p>
                        <p class="mt-1 font-sans text-[10px] tracking-[0.15em] uppercase {{ $n->pubblicato ? 'text-successo' : 'text-testo-soft' }}">
                            {{ $n->pubblicato ? 'Pubblicato' : 'Bozza' }}
                        </p>

                        <div class="mt-4">
                            <x-button :href="route('necrologi.modifica', $n)" class="w-full">Apri</x-button>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@endif

<p class="mt-8">
    <a href="{{ route('necrologi.index') }}"
       class="font-sans text-[11px] tracking-[0.2em] uppercase text-testo-soft hover:text-oro-scuro transition-colors duration-300">
        ← Tutti i necrologi
    </a>
</p>
@endsection
