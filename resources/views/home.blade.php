@extends('layouts.app')

@section('title', 'MemorAI — Articoli · Memoria · Devozione')

{{-- ============ HERO (a tutta larghezza, sopra la colonna) ============ --}}
@php
    use App\Models\ContenutoVetrina;
@endphp
@section('hero')
    <x-hero
        :occhiello="ContenutoVetrina::valore('hero.occhiello')"
        :sottotitolo="ContenutoVetrina::valore('hero.sottotitolo')"
        :immagine="$hero?->primaryImage ? asset('storage/'.$hero->primaryImage->path) : null"
        :immagineAlt="$hero?->name ?? 'MemorAI'"
        :primario="ContenutoVetrina::valore('hero.bottone_primario')" :primarioHref="ContenutoVetrina::valore('hero.bottone_primario_url', '#')"
        :secondario="ContenutoVetrina::valore('hero.bottone_secondario')" :secondarioHref="ContenutoVetrina::valore('hero.bottone_secondario_url', '#')"
        class="border-b-2 border-caffe">
        {{ ContenutoVetrina::valore('hero.titolo_intro') }}<br>
        con <em class="italic text-oro">{{ ContenutoVetrina::valore('hero.titolo_enfasi') }}</em>
    </x-hero>
@endsection

@section('content')
<div class="space-y-20">

    {{-- ============ COLLEZIONI (griglia categorie) ============ --}}
    {{-- Bordino oro sottile su ogni sezione: le stacca dalla pagina senza
         il peso di un bordo caffè, coerente con le greche dorate sottili
         dell'identità visiva. --}}
    <section class="border border-oro/30 px-8 py-10">
        <x-section-title :occhiello="ContenutoVetrina::valore('collezioni.occhiello')" :titolo="ContenutoVetrina::valore('collezioni.titolo')" allineamento="left" />
        <p class="mt-4 mb-10 max-w-xl font-sans font-light text-testo-soft leading-relaxed">
            {{ ContenutoVetrina::valore('collezioni.testo') }}
        </p>
        <x-category-grid />
    </section>

    {{-- ============ IN EVIDENZA (prodotti reali) ============ --}}
    @if ($evidenza->isNotEmpty())
        <section class="border border-oro/30 px-8 py-10">
            <x-section-title :occhiello="ContenutoVetrina::valore('evidenza.occhiello')" :titolo="ContenutoVetrina::valore('evidenza.titolo')" allineamento="left" />
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
                <x-button variant="contornata" :href="ContenutoVetrina::valore('evidenza.bottone_url', '#')">Vedi tutto il catalogo</x-button>
            </div>
        </section>
    @endif

    {{-- ============ FASCIA VALORI (blocco caffè che stacca, Opzione B —
         stesso trattamento della Gestione: un solo momento scuro, il resto
         della home resta bianco/panna com'è) ============ --}}
    <x-blocco variant="chiave" class="border border-oro/30 px-8 py-14">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10 text-center">
            @for ($i = 1; $i <= 3; $i++)
                <div class="flex flex-col items-center">
                    <span class="block h-px w-10 bg-oro mb-6"></span>
                    <h3 class="font-serif text-2xl text-panna">{{ ContenutoVetrina::valore("valori.{$i}.titolo") }}</h3>
                    <p class="mt-3 font-sans font-light text-[14px] text-panna/70 leading-relaxed max-w-xs">
                        {{ ContenutoVetrina::valore("valori.{$i}.testo") }}
                    </p>
                </div>
            @endfor
        </div>
    </x-blocco>

    {{-- ============ CTA FINALE ============ --}}
    <section class="text-center border border-oro/30 px-8 py-10">
        <h2 class="font-serif font-medium text-caffe text-4xl md:text-5xl leading-tight">
            {{ ContenutoVetrina::valore('cta.titolo_intro') }} <em class="italic text-oro">{{ ContenutoVetrina::valore('cta.titolo_enfasi') }}</em>
        </h2>
        <p class="mx-auto mt-5 max-w-xl font-sans font-light text-testo-soft text-lg leading-relaxed">
            {{ ContenutoVetrina::valore('cta.testo') }}
        </p>
        <div class="mt-8 flex flex-wrap justify-center gap-4">
            <x-button variant="piena" :href="ContenutoVetrina::valore('cta.bottone_primario_url', '#')">{{ ContenutoVetrina::valore('cta.bottone_primario') }}</x-button>
            <x-button variant="contornata" :href="ContenutoVetrina::valore('cta.bottone_secondario_url', '#')">{{ ContenutoVetrina::valore('cta.bottone_secondario') }}</x-button>
        </div>
    </section>

</div>
@endsection
