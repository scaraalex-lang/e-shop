@extends('layouts.app')

@section('title', 'Il tuo carrello — MemorAI')
@section('senza-sidebar', 1)

@section('content')
@php
    $minimo = $agenzia?->ordineMinimoPezzi() ?? 0;
    $sottoMinimo = $minimo > 0 && $pezzi < $minimo && ! $soloCrediti;
@endphp

<div class="mx-auto w-full max-w-5xl">

    <header>
        <span class="font-sans text-[11px] tracking-[0.35em] uppercase text-oro-scuro">Il tuo ordine</span>
        <h1 class="mt-3 font-serif text-3xl md:text-4xl font-medium leading-tight">Carrello</h1>
        <span class="mt-5 block h-px w-16 bg-oro"></span>
    </header>

    @if (session('stato'))
        <p class="mt-8 border-l-2 border-successo bg-panna px-5 py-4 font-sans text-[13px]">
            {{ session('stato') }}
        </p>
    @endif

    @if (! $carrello || $carrello->vuoto())
        <div class="mt-12 border border-caffe/15 bg-panna/50 px-8 py-14 text-center">
            <p class="font-serif text-2xl">Il carrello è vuoto</p>
            <p class="mt-3 font-sans font-light text-[15px] text-testo-soft">
                Sfoglia le collezioni: trigesimali, rosari e corone, photoceramiche.
            </p>
            <div class="mt-8">
                <x-button :href="url('/')">Torna alla vetrina</x-button>
            </div>
        </div>
    @else
        {{-- ============ righe ============ --}}
        <div class="mt-10 border border-caffe/15">
            @foreach ($conto->voci as $voce)
                @php $prodotto = $voce->riga->product; @endphp

                <article class="flex flex-wrap items-start gap-6 px-6 py-6 border-b border-caffe/10 last:border-b-0">

                    <a href="{{ route('prodotto', $prodotto->slug) }}"
                       class="block w-24 shrink-0 border border-caffe/25 bg-panna aspect-[4/5] overflow-hidden">
                        @if ($prodotto->primaryImage)
                            <img src="{{ asset('storage/'.$prodotto->primaryImage->path) }}"
                                 alt="{{ $prodotto->name }}" class="h-full w-full object-cover">
                        @else
                            {{-- senza foto il riquadro vuoto sembra un errore: ci sta il nome --}}
                            <span class="h-full w-full flex items-center justify-center p-2 text-center
                                         font-serif text-[13px] leading-tight text-caffe/60">
                                {{ $prodotto->name }}
                            </span>
                        @endif
                    </a>

                    <div class="flex-1 min-w-[12rem]">
                        <h2 class="font-serif text-xl leading-snug">
                            <a href="{{ route('prodotto', $prodotto->slug) }}"
                               class="hover:text-oro-scuro transition-colors duration-300">{{ $prodotto->name }}</a>
                        </h2>

                        @if ($prodotto->is_kit)
                            <p class="mt-1 font-sans font-light text-[12px] text-testo-soft">
                                Kit da {{ $prodotto->included_units }} ricordini compresi,
                                poi <x-prezzo :centesimi="$prodotto->extra_unit_price" /> a pezzo
                            </p>
                        @endif

                        @if ($voce->prezzo->haSconto())
                            <p class="mt-2 font-sans text-[11px] tracking-[0.18em] uppercase text-successo">
                                Sconto agenzia −{{ $voce->prezzo->fonteSconto->scontoLeggibile() }}
                            </p>
                        @endif

                        <div class="mt-4 flex flex-wrap items-center gap-4">
                            <form method="POST" action="{{ route('carrello.aggiorna', $voce->riga) }}"
                                  class="flex items-center gap-2">
                                @csrf
                                @method('patch')
                                <label for="q-{{ $voce->riga->id }}"
                                       class="font-sans text-[10px] tracking-[0.2em] uppercase text-testo-soft">
                                    {{ $prodotto->is_kit ? 'Ricordini' : 'Quantità' }}
                                </label>
                                <input id="q-{{ $voce->riga->id }}" name="quantita" type="number"
                                       value="{{ $voce->riga->quantita }}" min="0" step="1"
                                       class="w-20 bg-bianco border border-caffe/25 px-3 py-2 font-sans text-[14px]
                                              tabular-nums focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40">
                                <button type="submit"
                                        class="font-sans text-[10px] tracking-[0.2em] uppercase text-oro-scuro
                                               hover:text-caffe transition-colors duration-300 cursor-pointer">
                                    Aggiorna
                                </button>
                            </form>

                            <form method="POST" action="{{ route('carrello.rimuovi', $voce->riga) }}">
                                @csrf
                                @method('delete')
                                <button type="submit"
                                        class="font-sans text-[10px] tracking-[0.2em] uppercase text-testo-soft
                                               hover:text-errore transition-colors duration-300 cursor-pointer">
                                    Togli
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="text-right min-w-[7rem]">
                        <x-prezzo :centesimi="$voce->prezzo->scontato" class="font-serif text-xl" />
                        @if ($voce->prezzo->haSconto())
                            <br>
                            <x-prezzo :centesimi="$voce->prezzo->pieno" barrato class="font-sans text-[13px]" />
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        {{-- ============ totali ============ --}}
        <div class="mt-10 flex flex-col lg:flex-row gap-10">

            <div class="flex-1">
                @if ($agenzia)
                    <section class="border-l-2 {{ $sottoMinimo ? 'border-oro' : 'border-successo' }} bg-panna/60 px-6 py-5">
                        <h2 class="font-sans text-[11px] tracking-[0.25em] uppercase text-oro-scuro">
                            {{ $agenzia->ragione_sociale }}
                        </h2>

                        <p class="mt-3 font-sans font-light text-[14px] leading-relaxed text-testo-soft">
                            @if ($soloCrediti)
                                Solo pacchetti crediti nel carrello: il minimo d'ordine non si applica.
                            @elseif ($sottoMinimo)
                                Il minimo d'ordine per il tuo account è di <strong class="font-normal text-testo">{{ $minimo }} pezzi</strong>.
                                Nel carrello ce ne sono {{ $pezzi }}: ne mancano {{ $minimo - $pezzi }}.
                            @elseif ($minimo > 0)
                                Minimo d'ordine raggiunto: {{ $pezzi }} pezzi su {{ $minimo }} richiesti.
                            @else
                                Nessun minimo d'ordine sul tuo account.
                            @endif
                        </p>

                        <p class="mt-3 font-sans font-light text-[13px] leading-relaxed text-testo-soft">
                            Spedizione alla vostra sede: {{ $agenzia->indirizzoCompleto() }}.
                        </p>
                    </section>
                @endif
            </div>

            <div class="lg:w-80 shrink-0">
                <div class="border border-caffe/15 bg-panna/50 px-6 py-6">
                    <dl class="font-sans text-[14px]">
                        @if ($conto->haSconti())
                            <div class="flex justify-between py-1 text-testo-soft">
                                <dt class="font-light">Listino pubblico</dt>
                                <dd><x-prezzo :centesimi="$conto->totalePieno()" /></dd>
                            </div>
                            <div class="flex justify-between py-1 text-successo">
                                <dt class="font-light">Le tue condizioni</dt>
                                <dd>−<x-prezzo :centesimi="$conto->risparmio()" /></dd>
                            </div>
                        @endif

                        {{-- il filetto separa dal dettaglio sconti: senza sconti
                             sopra non c'è niente da separare --}}
                        <div @class([
                            'flex justify-between items-baseline',
                            'mt-3 pt-3 border-t border-caffe/15' => $conto->haSconti(),
                        ])>
                            <dt class="font-sans text-[11px] tracking-[0.2em] uppercase text-testo-soft">Totale</dt>
                            <dd><x-prezzo :centesimi="$conto->totale()" class="font-serif text-2xl" /></dd>
                        </div>
                    </dl>

                    <p class="mt-2 font-sans font-light text-[12px] text-testo-soft">
                        {{ $pezzi }} {{ $pezzi === 1 ? 'pezzo' : 'pezzi' }} · spedizione calcolata all'ordine
                    </p>

                    <div class="mt-6">
                        @if ($sottoMinimo)
                            <button type="button" disabled
                                    class="w-full inline-flex items-center justify-center font-sans uppercase
                                           text-[12px] tracking-[0.22em] px-8 py-3.5 bg-oro/40 text-bianco
                                           cursor-not-allowed">
                                Concludi l'ordine
                            </button>
                            <p class="mt-3 text-center font-sans font-light text-[12px] text-testo-soft">
                                Mancano {{ $minimo - $pezzi }} pezzi al minimo
                            </p>
                        @else
                            <x-button :href="route('checkout')" class="w-full">Concludi l'ordine</x-button>
                            @guest
                                <p class="mt-3 text-center font-sans font-light text-[12px] text-testo-soft">
                                    Ti chiederemo di accedere: il carrello ti segue.
                                </p>
                            @endguest
                        @endif
                    </div>
                </div>

                <a href="{{ url('/') }}"
                   class="mt-5 block text-center font-sans text-[11px] tracking-[0.2em] uppercase
                          text-testo-soft hover:text-oro-scuro transition-colors duration-300">
                    Continua a sfogliare
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
