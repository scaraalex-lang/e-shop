@extends('layouts.app')

@section('title', $prodotto->name.' — MemorAI')
@section('meta_description', $prodotto->short_description ?? 'Articolo MemorAI di fattura artigianale.')

@section('content')
@php
    $badge = $prodotto->is_kit ? 'Kit · '.$prodotto->included_units.' ricordini'
        : ($prodotto->has_qr_memorial ? 'QR Memoria'
        : ($prodotto->is_photo_printable ? 'Personalizzabile' : null));

    $specifiche = collect([
        'Materiale' => $prodotto->material,
        'Colore' => $prodotto->color,
    ])->merge($prodotto->attributes ?? [])->filter(fn ($v) => filled($v) && ! is_array($v));
@endphp

<div class="space-y-16">

    {{-- filo di briciole --}}
    <nav aria-label="Percorso" class="font-sans text-[11px] tracking-[0.18em] uppercase text-testo-soft">
        <a href="{{ url('/') }}" class="hover:text-oro-scuro transition-colors duration-300">MemorAI</a>
        @if ($prodotto->category)
            <span class="mx-2 text-oro">·</span>
            <a href="{{ route('categoria', $prodotto->category->slug) }}"
               class="hover:text-oro-scuro transition-colors duration-300">{{ $prodotto->category->name }}</a>
        @endif
    </nav>

    @if ($prodotto->category?->slug === 'photoceramiche')
        <x-avviso-app-fotoceramiche />
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16">

        {{-- ============ immagini ============ --}}
        <div class="space-y-4">
            <figure class="relative border-2 border-caffe bg-panna aspect-[4/5] overflow-hidden">
                @if ($badge)
                    <span class="absolute top-4 left-4 z-10 font-sans text-[10px] tracking-[0.2em] uppercase
                                 bg-bianco text-caffe px-3 py-1.5 border-2 border-caffe">
                        {{ $badge }}
                    </span>
                @endif

                @if ($prodotto->images->isNotEmpty())
                    <img src="{{ asset('storage/'.$prodotto->images->first()->path) }}"
                         alt="{{ $prodotto->name }}" class="h-full w-full object-cover">
                @else
                    <div class="h-full w-full flex items-center justify-center p-8 text-center">
                        <span class="font-serif text-3xl text-caffe/60">{{ $prodotto->name }}</span>
                    </div>
                @endif
            </figure>

            @if ($prodotto->images->count() > 1)
                <div class="grid grid-cols-4 gap-4">
                    @foreach ($prodotto->images->skip(1)->take(4) as $immagine)
                        <figure class="border border-caffe/25 bg-panna aspect-square overflow-hidden">
                            <img src="{{ asset('storage/'.$immagine->path) }}" alt=""
                                 class="h-full w-full object-cover">
                        </figure>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ============ scheda ============ --}}
        <div>
            @if ($prodotto->category)
                <span class="font-sans text-[11px] tracking-[0.3em] uppercase text-oro-scuro">
                    {{ $prodotto->category->name }}
                </span>
            @endif

            <h1 class="mt-3 font-serif text-3xl md:text-4xl font-medium leading-tight">
                {{ $prodotto->name }}
            </h1>

            <span class="mt-5 block h-px w-16 bg-oro"></span>

            @if ($prodotto->short_description)
                <p class="mt-6 font-sans font-light text-[15px] leading-relaxed text-testo-soft">
                    {{ $prodotto->short_description }}
                </p>
            @endif

            {{-- prezzo --}}
            <div class="mt-8 flex flex-wrap items-baseline gap-4">
                <x-prezzo :centesimi="$prezzo->scontato" class="font-serif text-3xl text-testo" />

                @if ($prezzo->haSconto())
                    <x-prezzo :centesimi="$prezzo->pieno" barrato class="font-sans text-lg" />
                    <span class="font-sans text-[11px] tracking-[0.2em] uppercase text-successo">
                        Riservato agenzie · −{{ $prezzo->fonteSconto->scontoLeggibile() }}
                    </span>
                @elseif ($prodotto->compare_at_price)
                    <x-prezzo :centesimi="$prodotto->compare_at_price" barrato class="font-sans text-lg" />
                @endif
            </div>

            @if ($prodotto->is_kit)
                <p class="mt-3 font-sans font-light text-[13px] leading-relaxed text-testo-soft">
                    Il prezzo comprende {{ $prodotto->included_units }} ricordini. Ogni pezzo in più
                    costa <x-prezzo :centesimi="$prodotto->extra_unit_price" class="text-testo" />.
                </p>
            @endif

            {{-- aggiunta al carrello --}}
            <form method="POST" action="{{ route('carrello.aggiungi') }}" class="mt-8">
                @csrf
                <input type="hidden" name="product_id" value="{{ $prodotto->id }}">

                <div class="flex flex-wrap items-end gap-4">
                    <div>
                        <x-input-label for="quantita"
                            :value="$prodotto->is_kit ? 'Quanti ricordini' : 'Quantità'" />
                        <x-text-input id="quantita" name="quantita" type="number"
                                      value="{{ old('quantita', $quantitaIniziale) }}"
                                      min="1" step="1" required class="w-32" />
                    </div>

                    <x-primary-button>Aggiungi al carrello</x-primary-button>
                </div>

                <x-input-error :messages="$errors->get('quantita')" />
            </form>

            {{-- scaglioni: solo per chi può usarli --}}
            @if ($scaglioni->isNotEmpty())
                <section class="mt-10 border border-caffe/15 bg-panna/50 px-6 py-5">
                    <h2 class="font-sans text-[11px] tracking-[0.25em] uppercase text-oro-scuro">
                        Le tue condizioni riservate
                    </h2>

                    <table class="mt-4 w-full font-sans text-[14px]">
                        <thead>
                            <tr class="text-left font-light text-[10px] tracking-[0.18em] uppercase text-testo-soft">
                                <th class="pb-2 font-normal">Da</th>
                                <th class="pb-2 font-normal">Sconto</th>
                                <th class="pb-2 font-normal text-right">Prezzo</th>
                            </tr>
                        </thead>
                        <tbody class="font-light">
                            @foreach ($scaglioni as $scaglione)
                                <tr class="border-t border-caffe/10">
                                    <td class="py-2 tabular-nums">{{ $scaglione['da'] }} pezzi</td>
                                    <td class="py-2 text-successo">−{{ $scaglione['sconto'] }}</td>
                                    <td class="py-2 text-right">
                                        <x-prezzo :centesimi="$scaglione['prezzo']" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <p class="mt-4 font-sans font-light text-[12px] leading-relaxed text-testo-soft">
                        Lo sconto si applica da solo quando la quantità raggiunge la soglia.
                    </p>
                </section>
            @endif

            {{-- specifiche --}}
            @if ($specifiche->isNotEmpty())
                <dl class="mt-10 grid grid-cols-2 gap-x-8 gap-y-4 border-t border-caffe/15 pt-8">
                    @foreach ($specifiche as $etichetta => $valore)
                        <div>
                            <dt class="font-sans text-[10px] tracking-[0.2em] uppercase text-testo-soft">
                                {{ ucfirst(str_replace('_', ' ', (string) $etichetta)) }}
                            </dt>
                            <dd class="mt-1 font-sans font-light text-[14px]">{{ $valore }}</dd>
                        </div>
                    @endforeach
                </dl>
            @endif
        </div>
    </div>

    {{-- descrizione lunga --}}
    @if ($prodotto->description)
        <section class="max-w-3xl">
            <x-section-title occhiello="La lavorazione" titolo="Questo articolo" allineamento="left" />
            <div class="mt-6 font-sans font-light text-[15px] leading-relaxed text-testo-soft whitespace-pre-line">
                {{ $prodotto->description }}
            </div>
        </section>
    @endif

    {{-- correlati --}}
    @if ($correlati->isNotEmpty())
        <section>
            <x-section-title occhiello="Della stessa collezione" titolo="Potrebbero interessarti" />

            <div class="mt-10 grid grid-cols-1 sm:grid-cols-3 gap-8">
                @foreach ($correlati as $altro)
                    <x-product-card
                        :nome="$altro->name"
                        :categoria="$altro->category?->name"
                        :prezzo="$altro->price"
                        :immagine="$altro->primaryImage ? asset('storage/'.$altro->primaryImage->path) : null"
                        :href="route('prodotto', $altro->slug)" />
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
