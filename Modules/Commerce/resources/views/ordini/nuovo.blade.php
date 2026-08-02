@extends('layouts.account')

@section('title', 'Nuovo ordine — MemorAI')
@section('titolo', 'Nuovo ordine')
@section('sottotitolo', 'Cosa vuoi mettere in questo ordine: un prodotto dalla vetrina, o un kit già composto. Cerchi ricordini, manifesti o necrologio a crediti? Sono nella sezione Servizi.')

@section('account')
<div class="space-y-px bg-caffe/15 border border-caffe/15">

    {{-- ============ prodotti (vetrina) ============ --}}
    <section class="bg-bianco px-7 py-8">
        <h2 class="font-serif text-2xl font-medium">Prodotti</h2>
        <p class="mt-2 max-w-2xl font-sans font-light text-[14px] leading-relaxed text-testo-soft">
            Ricordini, rosari e corone, croci, photoceramiche: si sceglie dalla vetrina e si aggiunge al carrello
            come un ordine normale.
        </p>
        <div class="mt-6">
            <x-button :href="url('/')">Sfoglia le collezioni</x-button>
        </div>
    </section>

    {{-- ============ kit già composti ============ --}}
    <section class="bg-bianco px-7 py-8">
        <h2 class="font-serif text-2xl font-medium">Kit</h2>
        <p class="mt-2 max-w-2xl font-sans font-light text-[14px] leading-relaxed text-testo-soft">
            Composizioni già pronte (es. il kit trigesimo con i ricordini inclusi): si comprano come un prodotto
            unico, dalla sua scheda.
        </p>

        @if ($kit->isNotEmpty())
            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-8">
                @foreach ($kit as $k)
                    <x-product-card
                        :nome="$k->name"
                        :categoria="$k->category?->name"
                        :prezzo="$k->price"
                        :badge="$k->is_kit ? 'Kit · '.$k->included_units.' ricordini' : 'Kit'"
                        :immagine="$k->primaryImage ? asset('storage/'.$k->primaryImage->path) : null"
                        :href="route('prodotto', $k->slug)" />
                @endforeach
            </div>
        @else
            <p class="mt-6 font-sans font-light text-[13px] text-testo-soft italic">
                Nessun kit composto ancora.
            </p>
        @endif
    </section>
</div>
@endsection
