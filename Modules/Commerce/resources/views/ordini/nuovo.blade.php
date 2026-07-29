@extends('layouts.account')

@section('title', 'Nuovo ordine — MemorAI')
@section('titolo', 'Nuovo ordine')
@section('sottotitolo', 'Cosa vuoi mettere in questo ordine: un servizio, un prodotto, o un kit già composto.')

@section('account')
<div class="space-y-px bg-caffe/15 border border-caffe/15">

    {{-- ============ servizio (crediti, solo agenzia) ============ --}}
    <section class="bg-bianco px-7 py-8">
        <h2 class="font-serif text-2xl font-medium">Servizio</h2>
        <p class="mt-2 max-w-2xl font-sans font-light text-[14px] leading-relaxed text-testo-soft">
            Apre un ordine di solo servizio, senza kit fisico: apre subito la lavorazione per compilare i dati
            del defunto e attiva i designer scelti. Si paga in crediti.
        </p>

        @if (! is_null($creditiSaldo))
            <div class="mt-6 flex flex-wrap items-center justify-between gap-4 border border-caffe/15 bg-panna/40 px-6 py-5">
                <div>
                    <p class="font-sans text-[11px] tracking-[0.2em] uppercase text-oro-scuro">Crediti servizi</p>
                    <p class="mt-1 font-serif text-2xl tabular-nums">{{ $creditiSaldo }}</p>
                </div>
                @if ($prodottoCrediti)
                    <form method="POST" action="{{ route('carrello.aggiungi') }}">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $prodottoCrediti->id }}">
                        <input type="hidden" name="quantita" value="1">
                        <x-button type="submit">Ricarica crediti</x-button>
                    </form>
                @endif
            </div>

            <form method="POST" action="{{ route('ordini.servizio') }}" class="mt-6 max-w-2xl space-y-6">
                @csrf

                <div>
                    <x-input-label value="Occasione" />
                    <p class="mt-1 font-sans font-light text-[12px] text-testo-soft">
                        Solo un'etichetta: decide la dicitura del necrologio quando lo pubblicherete, non cosa potete attivare.
                    </p>
                    <div class="mt-2 flex flex-wrap gap-4">
                        @foreach (['funerale' => 'Funerale', 'trigesimo' => 'Trigesimo', 'anniversario' => 'Anniversario'] as $valore => $etichetta)
                            <label class="inline-flex items-center gap-2 font-sans font-light text-[14px]">
                                <input type="radio" name="occasione" value="{{ $valore }}"
                                    onchange="document.getElementById('nuovo-ordine-numero-anniversario').style.display = this.value === 'anniversario' ? 'block' : 'none'"
                                    @checked(old('occasione', 'trigesimo') === $valore)>
                                {{ $etichetta }}
                            </label>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('occasione')" class="mt-2" />

                    <div id="nuovo-ordine-numero-anniversario" class="mt-3 max-w-[10rem]" style="display: {{ old('occasione') === 'anniversario' ? 'block' : 'none' }}">
                        <x-input-label for="numero_anniversario" value="Quale anniversario" />
                        <x-text-input id="numero_anniversario" name="numero_anniversario" type="number" min="1" max="99"
                            :value="old('numero_anniversario')" />
                        <x-input-error :messages="$errors->get('numero_anniversario')" />
                    </div>
                </div>

                @if ($servizi && $servizi->isNotEmpty())
                    <div>
                        <x-input-label value="Quali servizi attivare" />
                        <div class="mt-2 flex flex-wrap gap-4">
                            @foreach ($servizi as $servizio)
                                <label class="inline-flex items-center gap-2 font-sans font-light text-[14px] border border-caffe/15 px-4 py-2.5">
                                    <input type="checkbox" name="servizi[]" value="{{ $servizio->codice }}"
                                        @checked(collect(old('servizi', []))->contains($servizio->codice))>
                                    {{ $servizio->etichetta }} ({{ $servizio->costo_crediti }} crediti)
                                </label>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('servizi')" class="mt-2" />
                    </div>

                    <x-button type="submit">Attiva i servizi scelti</x-button>
                @else
                    <p class="font-sans font-light text-[13px] text-testo-soft italic">
                        Nessun servizio attivo al momento.
                    </p>
                @endif
            </form>
        @else
            <p class="mt-6 font-sans font-light text-[13px] text-testo-soft italic">
                Il servizio a crediti è riservato alle agenzie.
            </p>
        @endif
    </section>

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
