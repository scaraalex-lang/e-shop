@extends('layouts.app')

@section('title', $categoria->name . ' — MemorAI')

@section('content')
<div class="space-y-10">

    {{-- intestazione categoria --}}
    <header>
        @if ($categoria->image)
            <div class="mb-8 aspect-[21/9] w-full overflow-hidden border-2 border-caffe">
                <img src="{{ asset('storage/'.$categoria->image) }}" alt="{{ $categoria->name }}" class="h-full w-full object-cover">
            </div>
        @endif
        <x-section-title :occhiello="'Categoria'" :titolo="$categoria->name" allineamento="left" />
        @if ($categoria->description)
            <p class="mt-4 max-w-2xl font-sans font-light text-testo-soft leading-relaxed">
                {{ $categoria->description }}
            </p>
        @endif
        <p class="mt-3 font-sans text-[12px] tracking-[0.18em] uppercase text-oro-scuro">
            {{ $prodotti->count() }} {{ $prodotti->count() === 1 ? 'articolo' : 'articoli' }}
        </p>
    </header>

    {{-- griglia prodotti --}}
    @if ($prodotti->isNotEmpty())
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-8">
            @foreach ($prodotti as $p)
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
    @else
        {{-- stato vuoto pulito --}}
        <div class="border-2 border-caffe bg-panna px-8 py-16 text-center">
            <h3 class="font-serif text-2xl text-caffe">Collezione in arrivo</h3>
            <p class="mx-auto mt-3 max-w-md font-sans font-light text-testo-soft leading-relaxed">
                Stiamo curando gli articoli di questa categoria. Torna presto — oppure
                scrivici per una richiesta su misura.
            </p>
            <div class="mt-7 flex justify-center">
                <x-button variant="contornata" href="#">Richiedi informazioni</x-button>
            </div>
        </div>
    @endif

</div>
@endsection
