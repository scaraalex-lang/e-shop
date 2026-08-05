@extends('layouts.account')

@section('title', 'Ordine '.$ordine->numero.' — MemorAI')
@section('titolo', 'Ordine '.$ordine->numero)
@section('sottotitolo', $ordine->stato->racconto())

@section('account')
@php
    use Modules\Commerce\Enums\StatoPagamento;
@endphp

@if (session('appena_confermato'))
    <div class="mb-10 border-l-2 border-successo bg-panna px-6 py-5">
        <p class="font-serif text-xl">Grazie, l'ordine è registrato.</p>
        <p class="mt-2 font-sans font-light text-[14px] leading-relaxed text-testo-soft">
            Ti abbiamo assegnato il numero <strong class="font-normal text-testo">{{ $ordine->numero }}</strong>:
            citalo se ci scrivi. Da questa pagina segui ogni passaggio.
        </p>
    </div>
@endif

@if (session('stato'))
    <p class="mb-10 border-l-2 border-successo bg-panna px-5 py-4 font-sans text-[13px]">
        {{ session('stato') }}
    </p>
@endif

@if ($ordine->agenzia_id && ($scadenza = $ordine->prossimaScadenzaSuggerita()))
    <div class="mb-10 border-l-2 border-oro bg-panna/60 px-6 py-5">
        <p class="font-sans font-light text-[14px] leading-relaxed text-testo-soft">
            Da segnarsi: <strong class="font-normal text-testo">{{ $scadenza['etichetta'] }}</strong>
            intorno al <strong class="font-normal text-testo">{{ $scadenza['quando']->translatedFormat('j F Y') }}</strong>.
        </p>
    </div>
@endif

{{-- ============ tracciamento ============ --}}
<section>
    <h2 class="font-sans text-[11px] tracking-[0.25em] uppercase text-oro-scuro">A che punto siamo</h2>

    <ol class="mt-6 flex flex-col sm:flex-row gap-6 sm:gap-0">
        @foreach ($ordine->percorso() as $i => $tappa)
            <li class="flex-1 relative sm:pr-6">
                {{-- filo che unisce le tappe --}}
                @if (! $loop->last)
                    <span aria-hidden="true"
                          class="hidden sm:block absolute top-[0.45rem] left-4 right-0 h-px
                                 {{ $tappa['fatta'] ? 'bg-oro' : 'bg-caffe/20' }}"></span>
                @endif

                <span aria-hidden="true"
                      class="relative z-10 block w-[0.9rem] h-[0.9rem] rotate-45
                             {{ $tappa['fatta'] ? 'bg-oro' : 'bg-bianco border border-caffe/30' }}"></span>

                <p @class([
                    'mt-3 pr-4 font-sans text-[11px] tracking-[0.16em] uppercase',
                    'text-oro-scuro' => $tappa['attuale'],
                    'text-testo' => $tappa['fatta'] && ! $tappa['attuale'],
                    'text-testo-soft/60' => ! $tappa['fatta'],
                ])>
                    {{ $tappa['stato']->etichetta() }}
                </p>
            </li>
        @endforeach
    </ol>

    @if ($ordine->richiede_lavorazione)
        <div class="mt-8 border-l-2 border-oro bg-panna/60 px-6 py-5">
            <h3 class="font-serif text-xl">La lavorazione della fotografia</h3>
            <p class="mt-2 font-sans font-light text-[14px] leading-relaxed text-testo-soft">
                Questo ordine comprende articoli personalizzati con il ritratto. Il passo
                successivo è caricare la fotografia e approvare la bozza.
            </p>

            @if ($ricordino)
                {{-- Fronte e retro insieme: il retro porta la preghiera, ed è
                     metà di quello che il cliente deve approvare. --}}
                <x-bozza-ricordino :ricordino="$ricordino" class="mt-6" />
            @endif

            @if ($ordine->lavorazioneApribile())
                <div class="mt-5 flex flex-wrap items-center gap-4">
                    <x-button :href="route('lavorazione', $ordine)">
                        {{ $ordine->defunto_id ? 'Riprendi la lavorazione' : 'Comincia dalla fotografia' }}
                    </x-button>

                    @if ($ricordino && ! $ricordino->approvato())
                        <form method="POST" action="{{ route('lavorazione.approva', $ordine) }}">
                            @csrf
                            <x-button type="submit" variant="contornata">Approva la bozza</x-button>
                        </form>
                    @elseif ($ricordino?->approvato())
                        <p class="font-sans text-[11px] tracking-[0.15em] uppercase text-successo">
                            ✓ Bozza approvata
                        </p>
                    @endif
                </div>
            @else
                <p class="mt-4 font-sans text-[12px] tracking-[0.1em] text-testo-soft">
                    La lavorazione si apre appena l'ordine è preso in carico.
                </p>
            @endif
        </div>
    @endif
</section>

@if ($ricordino?->approvato() && $categorieStampa->isNotEmpty())
    {{-- ============ composizione dell'ordine di stampa ============ --}}
    <section class="mt-12">
        <h2 class="font-sans text-[11px] tracking-[0.25em] uppercase text-oro-scuro">Componi l'ordine di stampa</h2>
        <p class="mt-3 max-w-2xl font-sans font-light text-[13px] leading-relaxed text-testo-soft">
            La bozza è approvata: scegli formato e quantità di ricordini, coroncine o accessori.
            Quello che aggiungi finisce nel carrello, insieme per questa pratica — poi si paga
            con i crediti a disposizione o online, come per ogni altro ordine.
        </p>

        <div class="mt-6 space-y-8">
            @foreach ($categorieStampa as $categoria)
                <div>
                    <h3 class="font-serif text-lg">{{ $categoria->name }}</h3>

                    <div class="mt-3 border border-caffe/15">
                        @foreach ($categoria->prodotti as $p)
                            <form method="POST" action="{{ route('ordini.stampa', $ordine) }}"
                                  class="flex flex-wrap items-center gap-4 px-6 py-4 border-b border-caffe/10 last:border-b-0">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $p->id }}">

                                <div class="flex-1 min-w-[10rem]">
                                    <p class="font-serif text-base leading-snug">{{ $p->name }}</p>
                                    @if ($p->is_kit)
                                        <p class="mt-0.5 font-sans font-light text-[12px] text-testo-soft">
                                            {{ $p->included_units }} inclusi, poi <x-prezzo :centesimi="$p->extra_unit_price" class="text-[12px]" /> a pezzo
                                        </p>
                                    @endif
                                </div>

                                <x-prezzo :centesimi="$p->price" class="font-serif text-base" />

                                {{-- min = step: sennò l'input HTML5 rifiuta il suo stesso valore di
                                     default (es. min=1/step=50 accetta 1, 51, 101... mai 50). Resta
                                     solo un suggerimento: il prezzo (Product::priceForQuantity) accetta
                                     comunque qualunque quantità digitata a mano. --}}
                                <input type="number" name="quantita"
                                       min="{{ $p->is_kit ? $p->included_units : 1 }}"
                                       value="{{ $p->is_kit ? $p->included_units : 1 }}"
                                       step="{{ $p->is_kit ? $p->included_units : 1 }}"
                                       class="w-24 bg-bianco border border-caffe/25 px-3 py-2 font-sans text-[14px]
                                              focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40">

                                <x-button type="submit" variant="contornata" compatta>Aggiungi</x-button>
                            </form>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            <x-button :href="route('carrello')" variant="contornata">Vai al carrello per pagare</x-button>
        </div>
    </section>
@endif

@if ($ordine->servizi->isNotEmpty())
    {{-- ============ servizi attivati (ordine a crediti) ============ --}}
    <section class="mt-12">
        <h2 class="font-sans text-[11px] tracking-[0.25em] uppercase text-oro-scuro">Servizi attivati</h2>

        @if ($fotoUrl)
            <div class="mt-5 flex items-center gap-4">
                <div class="h-24 w-24 shrink-0 border border-oro/40 bg-panna overflow-hidden">
                    <img src="{{ $fotoUrl }}" alt="" class="h-full w-full object-cover">
                </div>
                <div>
                    <p class="font-sans text-[10px] tracking-[0.2em] uppercase text-testo-soft">Foto principale</p>
                    <p class="mt-1 font-serif text-lg">{{ $defunto->nomeCompleto() }}</p>
                </div>
            </div>
        @endif

        <div class="mt-5 border border-caffe/15">
            @foreach ($ordine->servizi as $servizio)
                <article class="flex items-center justify-between gap-5 px-6 py-4 border-b border-caffe/10 last:border-b-0">
                    <h3 class="font-serif text-lg leading-snug">{{ $servizio->servizioEditor?->etichetta }}</h3>
                    <span class="font-sans text-[13px] text-testo-soft tabular-nums">{{ $servizio->costo_crediti }} crediti</span>
                </article>
            @endforeach
        </div>
    </section>
@endif

@if ($ordine->righe->isNotEmpty())
{{-- ============ articoli ============ --}}
<section class="mt-12">
    <h2 class="font-sans text-[11px] tracking-[0.25em] uppercase text-oro-scuro">Cosa hai ordinato</h2>

    <div class="mt-5 border border-caffe/15">
        @foreach ($ordine->righe as $riga)
            <article class="flex flex-wrap items-center gap-5 px-6 py-5 border-b border-caffe/10 last:border-b-0">
                <div class="w-16 shrink-0 border border-caffe/25 bg-panna aspect-[4/5] overflow-hidden">
                    @if ($riga->product?->primaryImage)
                        <img src="{{ asset('storage/'.$riga->product->primaryImage->path) }}"
                             alt="" class="h-full w-full object-cover">
                    @endif
                </div>

                <div class="flex-1 min-w-[10rem]">
                    <h3 class="font-serif text-lg leading-snug">{{ $riga->nome }}</h3>
                    <p class="mt-1 font-sans font-light text-[12px] text-testo-soft">
                        {{ $riga->sku }} · {{ $riga->quantita }} {{ $riga->quantita === 1 ? 'pezzo' : 'pezzi' }}
                        @if ($riga->richiede_foto)
                            <span class="text-oro-scuro">· con fotografia</span>
                        @endif
                    </p>
                </div>

                <div class="text-right">
                    <x-prezzo :centesimi="$riga->prezzo" class="font-serif text-lg" />
                    @if ($riga->haSconto())
                        <br><x-prezzo :centesimi="$riga->prezzo_pieno" barrato class="font-sans text-[12px]" />
                    @endif
                </div>
            </article>
        @endforeach
    </div>
</section>
@endif

{{-- ============ conto e consegna ============ --}}
<div class="mt-12 grid gap-10 lg:grid-cols-2">
    <section>
        <h2 class="font-sans text-[11px] tracking-[0.25em] uppercase text-oro-scuro">Consegna</h2>
        <address class="mt-4 font-sans font-light text-[14px] leading-relaxed text-testo not-italic">
            {{ $ordine->consegna_nome }}<br>
            {{ $ordine->consegna_indirizzo }}<br>
            {{ $ordine->consegna_cap }} {{ $ordine->consegna_citta }} ({{ $ordine->consegna_provincia }})<br>
            <span class="text-testo-soft">{{ $ordine->consegna_telefono }}</span>
        </address>

        @if ($ordine->note)
            <p class="mt-4 border-l-2 border-caffe/20 pl-4 font-sans font-light text-[13px] leading-relaxed text-testo-soft">
                {{ $ordine->note }}
            </p>
        @endif
    </section>

    <section>
        <h2 class="font-sans text-[11px] tracking-[0.25em] uppercase text-oro-scuro">Il conto</h2>

        @if ($ordine->righe->isEmpty() && $ordine->servizi->isNotEmpty())
            <p class="mt-4 font-sans text-[14px]">
                Pagato in crediti:
                <span class="font-serif text-2xl tabular-nums">{{ $ordine->servizi->sum('costo_crediti') }}</span>
            </p>
        @else
        <dl class="mt-4 font-sans text-[14px]">
            @if ($ordine->haSconti())
                <div class="flex justify-between py-1 text-testo-soft">
                    <dt class="font-light">Listino pubblico</dt>
                    <dd><x-prezzo :centesimi="$ordine->totale_pieno" /></dd>
                </div>
                <div class="flex justify-between py-1 text-successo">
                    <dt class="font-light">Le tue condizioni</dt>
                    <dd>−<x-prezzo :centesimi="$ordine->risparmio()" /></dd>
                </div>
            @endif

            <div class="flex justify-between py-1 text-testo-soft">
                <dt class="font-light">Merce</dt>
                <dd><x-prezzo :centesimi="$ordine->totale_merce" /></dd>
            </div>
            <div class="flex justify-between py-1 text-testo-soft">
                <dt class="font-light">Spedizione</dt>
                <dd>{{ $ordine->spedizione === 0 ? 'Inclusa' : null }}
                    @if ($ordine->spedizione > 0)<x-prezzo :centesimi="$ordine->spedizione" />@endif
                </dd>
            </div>

            <div class="mt-3 pt-3 border-t border-caffe/15 flex justify-between items-baseline">
                <dt class="font-sans text-[11px] tracking-[0.2em] uppercase text-testo-soft">Totale</dt>
                <dd><x-prezzo :centesimi="$ordine->totale" class="font-serif text-2xl" /></dd>
            </div>
        </dl>

        <p class="mt-4 font-sans font-light text-[13px] text-testo-soft">
            {{ $ordine->metodo_pagamento->etichetta() }} ·
            <span @class(['text-successo' => $ordine->stato_pagamento === StatoPagamento::Pagato,
                          'text-errore' => $ordine->stato_pagamento === StatoPagamento::Fallito])>
                {{ $ordine->stato_pagamento->etichetta() }}
            </span>
            @if ($ordine->pagato_at)
                il {{ $ordine->pagato_at->format('d/m/Y') }}
            @endif
        </p>

        @if ($ordine->riferimento_pagamento)
            <p class="mt-1 font-sans font-light text-[12px] text-testo-soft/70 tabular-nums">
                Riferimento {{ $ordine->riferimento_pagamento }}
            </p>
        @endif
        @endif
    </section>
</div>
@endsection
