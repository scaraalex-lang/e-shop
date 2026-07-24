@extends('layouts.app')

@section('title', 'Styleguide — MemorAI')

{{-- ==================================================================== --}}
{{--  HERO — componente x-hero (a tutta larghezza, stesso usato dalla home) --}}
{{-- ==================================================================== --}}
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
<div class="space-y-24">

    {{-- intestazione styleguide --}}
    <section class="text-left">
        <x-section-title occhiello="Design System" titolo="Styleguide MemorAI" />
        <p class="mx-auto mt-4 max-w-xl font-sans font-light text-testo-soft leading-relaxed">
            Impianto pulito e arioso: fondo bianco caldo, contrasti netti in marrone caffè,
            oro solo nei dettagli. Gli unici elementi grafici sono tipografia e bordi.
        </p>
    </section>

    {{-- ============ PALETTE ============ --}}
    <section>
        <x-section-title occhiello="01" titolo="Palette" allineamento="left" />
        <div class="mt-8 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5">
            @foreach ([
                ['Bianco caldo', '#FDFCFA', 'bianco', 'text-caffe', 'Fondo dominante'],
                ['Panna', '#FAF6EC', 'panna', 'text-caffe', 'Accento / fasce'],
                ['Marrone caffè', '#3A2E22', 'caffe', 'text-bianco', 'Bordi, testi forti, footer'],
                ['Oro', '#C2A35A', 'oro', 'text-bianco', 'Pulsanti / dettagli'],
                ['Oro scuro', '#A5863F', 'oro-scuro', 'text-bianco', 'Hover / dettagli'],
                ['Testo', '#3A2E22', 'testo', 'text-bianco', 'Testo principale'],
                ['Testo soft', '#6B6152', 'testo-soft', 'text-bianco', 'Testo secondario'],
            ] as [$nome, $hex, $token, $txt, $uso])
                <div class="border-2 border-caffe">
                    <div class="h-24 flex items-end p-3 bg-{{ $token }} {{ $txt }}">
                        <span class="font-sans text-[11px] tracking-[0.15em] uppercase">{{ $nome }}</span>
                    </div>
                    <div class="px-3 py-2 bg-bianco font-sans text-[11px] text-testo-soft border-t-2 border-caffe">
                        <div class="text-caffe">{{ $hex }}</div>
                        <div>{{ $uso }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ============ TIPOGRAFIA ============ --}}
    <section>
        <x-section-title occhiello="02" titolo="Tipografia" allineamento="left" />
        <div class="mt-8 grid md:grid-cols-2 gap-12">
            <div class="space-y-4">
                <span class="font-sans text-[11px] tracking-[0.3em] uppercase text-oro-scuro">Cormorant Garamond · Serif</span>
                <p class="font-serif text-5xl text-caffe leading-tight">Bellezza <em class="italic text-oro">che dura</em></p>
                <p class="font-serif text-3xl text-testo">Memoria e devozione</p>
                <p class="font-serif italic text-2xl text-testo-soft">Fattura artigianale, dal 2026</p>
            </div>
            <div class="space-y-4">
                <span class="font-sans text-[11px] tracking-[0.3em] uppercase text-oro-scuro">Jost · Sans</span>
                <p class="font-sans text-2xl font-light text-testo">Menu, bottoni e micro-testi</p>
                <p class="font-sans text-base font-normal text-testo leading-relaxed">
                    Il corpo di servizio: leggibile, pulito, discreto. Accompagna la lettura
                    senza rubare la scena alla tipografia editoriale.
                </p>
                <p class="font-sans text-[13px] tracking-[0.22em] uppercase text-testo-soft">Etichette · Navigazione</p>
            </div>
        </div>
    </section>

    {{-- ============ BOTTONI + SEPARATORI ============ --}}
    <section>
        <x-section-title occhiello="03" titolo="Bottoni e separatori" allineamento="left" />
        <div class="mt-8 flex flex-wrap items-center gap-6">
            <x-button variant="piena" href="#">Primario · oro pieno</x-button>
            <x-button variant="contornata" href="#">Secondario · bordo caffè</x-button>
            <x-button variant="piena">Aggiungi al carrello</x-button>
        </div>
        <div class="mt-10 space-y-6">
            <div>
                <p class="mb-3 font-sans text-[11px] tracking-[0.25em] uppercase text-testo-soft">Separatore oro</p>
                <x-separator colore="oro" />
            </div>
            <div>
                <p class="mb-3 font-sans text-[11px] tracking-[0.25em] uppercase text-testo-soft">Separatore marrone</p>
                <x-separator colore="caffe" />
            </div>
        </div>
    </section>

    {{-- ============ ICONE ============ --}}
    <section>
        <x-section-title occhiello="04" titolo="Icone" allineamento="left" />
        <div class="mt-8 flex items-center gap-10 text-caffe">
            <div class="flex flex-col items-center gap-2">
                <x-icon.search class="w-7 h-7" />
                <span class="font-sans text-[11px] tracking-widest uppercase text-testo-soft">Cerca</span>
            </div>
            <div class="flex flex-col items-center gap-2">
                <x-icon.account class="w-7 h-7" />
                <span class="font-sans text-[11px] tracking-widest uppercase text-testo-soft">Account</span>
            </div>
            <div class="flex flex-col items-center gap-2">
                <x-icon.cart class="w-7 h-7" />
                <span class="font-sans text-[11px] tracking-widest uppercase text-testo-soft">Carrello</span>
            </div>
        </div>
    </section>

    {{-- ============ GRIGLIA CATEGORIE ============ --}}
    <section>
        <x-section-title occhiello="05" titolo="Griglia categorie"
                         allineamento="left" class="mb-4" />
        <p class="mb-8 font-sans font-light text-testo-soft">
            Categorie radice attive lette dal database (ordinate per sort_order). Tre colonne
            desktop, due tablet, una mobile. Le copertine senza immagine mostrano il fallback pulito.
        </p>
        <x-category-grid />
    </section>

    {{-- ============ CARD PRODOTTO ============ --}}
    <section>
        <x-section-title occhiello="06" titolo="Card prodotto"
                         allineamento="left" class="mb-4" />
        <p class="mb-8 font-sans font-light text-testo-soft">
            Cornice marrone 2px e zoom morbido dell'immagine al passaggio del mouse.
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            @forelse ($prodotti as $p)
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
                    href="#" />
            @empty
                <p class="col-span-full font-sans text-testo-soft">
                    Nessun prodotto nel catalogo. Esegui il seeder del modulo Catalog.
                </p>
            @endforelse
        </div>
    </section>

</div>
@endsection
