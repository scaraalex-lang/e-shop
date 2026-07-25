@extends('layouts.app')

@section('title', 'MemorAI — Articoli · Memoria · Devozione')

{{-- ============ HERO (a tutta larghezza, sopra la colonna) ============ --}}
@section('hero')
    <x-hero
        occhiello="Artigianato memoriale dal 2026"
        sottotitolo="Rosari, corone e ricordini di fattura artigianale, in materiali nobili. Piccoli oggetti da tenere fra le mani e tramandare — mai lutto, sempre cura."
        :immagine="$hero?->primaryImage ? asset('storage/'.$hero->primaryImage->path) : null"
        :immagineAlt="$hero?->name ?? 'MemorAI'"
        primario="Scopri le collezioni" primarioHref="#"
        secondario="Personalizza" secondarioHref="#"
        class="border-b-2 border-caffe">
        Custodire la memoria<br>
        con <em class="italic text-oro">bellezza</em>
    </x-hero>
@endsection

@section('content')
<div class="space-y-20">

    {{-- ============ COLLEZIONI (griglia categorie) ============ --}}
    <section>
        <x-section-title occhiello="Le collezioni" titolo="Un catalogo curato" allineamento="left" />
        <p class="mt-4 mb-10 max-w-xl font-sans font-light text-testo-soft leading-relaxed">
            Ogni collezione raccoglie oggetti scelti per bellezza e cura dei dettagli.
        </p>
        <x-category-grid />
    </section>

    {{-- ============ IN EVIDENZA (prodotti reali) ============ --}}
    @if ($evidenza->isNotEmpty())
        <section>
            <x-section-title occhiello="Dal catalogo" titolo="In evidenza" allineamento="left" />
            <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-8">
                @foreach ($evidenza as $p)
                    @php
                        $badge = $p->is_kit ? 'Kit · '.$p->included_units.' ricordini'
                            : ($p->has_qr_memorial ? 'QR Memoria'
                            : ($p->is_photo_printable ? 'Personalizzabile' : null));
                    @endphp
                    <x-product-card
                        :nome="$p->name"
                        :categoria="$p->category?->name"
                        :prezzo="$p->price"
                        :badge="$badge"
                        :immagine="$p->primaryImage ? asset('storage/'.$p->primaryImage->path) : null"
                        :href="route('prodotto', $p->slug)" />
                @endforeach
            </div>
            <div class="mt-10">
                <x-button variant="contornata" href="#">Vedi tutto il catalogo</x-button>
            </div>
        </section>
    @endif

    {{-- ============ FASCIA VALORI (blocco panna che stacca) ============ --}}
    <section class="bg-panna border-2 border-caffe">
        <div class="px-8 py-14">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10 text-center">
                @foreach ([
                    ['Fattura artigianale', 'Ogni pezzo è lavorato e rifinito a mano, in materiali nobili scelti con cura.'],
                    ['Personalizzazione', 'Ricordini e stampe su misura, con la fotografia e le parole che preferite.'],
                    ['QR Memoria', 'Una galleria online di foto e ricordi, da arricchire nel tempo, accanto all\'oggetto.'],
                ] as [$titolo, $testo])
                    <div class="flex flex-col items-center">
                        <span class="block h-px w-10 bg-oro mb-6"></span>
                        <h3 class="font-serif text-2xl text-caffe">{{ $titolo }}</h3>
                        <p class="mt-3 font-sans font-light text-[14px] text-testo-soft leading-relaxed max-w-xs">
                            {{ $testo }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============ CTA FINALE ============ --}}
    <section class="text-center py-8">
        <h2 class="font-serif font-medium text-caffe text-4xl md:text-5xl leading-tight">
            Un ricordo che <em class="italic text-oro">resta</em>
        </h2>
        <p class="mx-auto mt-5 max-w-xl font-sans font-light text-testo-soft text-lg leading-relaxed">
            Racconta a chi siamo che cosa desideri: ti guideremo nella scelta e nella
            personalizzazione, con la stessa cura con cui realizziamo ogni oggetto.
        </p>
        <div class="mt-8 flex flex-wrap justify-center gap-4">
            <x-button variant="piena" href="#">Richiedi assistenza</x-button>
            <x-button variant="contornata" href="#">Onoranze funebri (B2B)</x-button>
        </div>
    </section>

</div>
@endsection
