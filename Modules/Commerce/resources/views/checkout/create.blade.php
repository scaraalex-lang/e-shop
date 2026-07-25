@extends('layouts.app')

@section('title', 'Conferma il tuo ordine — MemorAI')
@section('senza-sidebar', 1)

@section('content')
@php
    $utente = auth()->user();
    $vecchio = fn (string $campo, $default = null) => old($campo, $default);
@endphp

<div class="mx-auto w-full max-w-5xl">

    <header>
        <span class="font-sans text-[11px] tracking-[0.35em] uppercase text-oro-scuro">Ultimo passo</span>
        <h1 class="mt-3 font-serif text-3xl md:text-4xl font-medium leading-tight">Conferma il tuo ordine</h1>
        <span class="mt-5 block h-px w-16 bg-oro"></span>
    </header>

    <form method="POST" action="{{ route('checkout') }}" class="mt-10 flex flex-col lg:flex-row gap-10">
        @csrf

        <div class="flex-1 space-y-10">

            {{-- ============ consegna ============ --}}
            <section class="border border-caffe/15 px-7 py-7">
                <h2 class="font-serif text-2xl font-medium">Dove lo spediamo</h2>

                @if ($agenzia)
                    <p class="mt-2 font-sans font-light text-[13px] leading-relaxed text-testo-soft">
                        Per gli account agenzia spediamo alla vostra sede: sarete voi a consegnare alla famiglia.
                    </p>
                @endif

                <div class="mt-6 space-y-6">
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <x-input-label for="nome" value="Nome di chi riceve" />
                            <x-text-input id="nome" name="nome" type="text" required
                                :value="$vecchio('nome', $agenzia?->ragione_sociale ?? $utente->name)" />
                            <x-input-error :messages="$errors->get('nome')" />
                        </div>
                        <div>
                            <x-input-label for="telefono" value="Telefono" />
                            <x-text-input id="telefono" name="telefono" type="tel" required
                                :value="$vecchio('telefono', $agenzia?->telefono ?? $utente->telefono)" />
                            <x-input-error :messages="$errors->get('telefono')" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="indirizzo" value="Indirizzo" />
                        <x-text-input id="indirizzo" name="indirizzo" type="text" required
                            :value="$vecchio('indirizzo', $agenzia?->indirizzo)" />
                        <x-input-error :messages="$errors->get('indirizzo')" />
                    </div>

                    <div class="grid gap-6 sm:grid-cols-3">
                        <div>
                            <x-input-label for="cap" value="CAP" />
                            <x-text-input id="cap" name="cap" type="text" required maxlength="5"
                                :value="$vecchio('cap', $agenzia?->cap)" />
                            <x-input-error :messages="$errors->get('cap')" />
                        </div>
                        <div>
                            <x-input-label for="citta" value="Città" />
                            <x-text-input id="citta" name="citta" type="text" required
                                :value="$vecchio('citta', $agenzia?->citta)" />
                            <x-input-error :messages="$errors->get('citta')" />
                        </div>
                        <div>
                            <x-input-label for="provincia" value="Provincia" />
                            <x-text-input id="provincia" name="provincia" type="text" required maxlength="2"
                                :value="$vecchio('provincia', $agenzia?->provincia)" />
                            <x-input-error :messages="$errors->get('provincia')" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="note" value="Note per noi (facoltative)" />
                        <textarea id="note" name="note" rows="3"
                                  class="block w-full bg-bianco border border-caffe/25 px-4 py-3 font-sans font-light
                                         text-[15px] focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40"
                                  placeholder="Per esempio: la data del trigesimo, o come preferite essere contattati">{{ old('note') }}</textarea>
                        <x-input-error :messages="$errors->get('note')" />
                    </div>
                </div>
            </section>

            {{-- ============ pagamento ============ --}}
            <section class="border border-caffe/15 px-7 py-7">
                <h2 class="font-serif text-2xl font-medium">Come paghi</h2>

                <div class="mt-6 space-y-3">
                    @foreach ($metodi as $i => $metodo)
                        <label class="flex items-start gap-4 border border-caffe/20 bg-panna/40 px-5 py-4
                                      cursor-pointer hover:border-oro transition-colors duration-300
                                      has-[:checked]:border-oro has-[:checked]:bg-panna">
                            <input type="radio" name="metodo_pagamento" value="{{ $metodo->value }}"
                                   class="mt-1 accent-oro"
                                   @checked(old('metodo_pagamento', $metodi[0]->value) === $metodo->value)
                                   data-metodo="{{ $metodo->value }}">
                            <span>
                                <span class="block font-sans text-[14px] text-testo">{{ $metodo->etichetta() }}</span>
                                <span class="mt-1 block font-sans font-light text-[13px] text-testo-soft">
                                    {{ $metodo->spiegazione() }}
                                </span>
                            </span>
                        </label>
                    @endforeach
                    <x-input-error :messages="$errors->get('metodo_pagamento')" />
                </div>

                {{-- Compare solo scegliendo la carta: senza JS resta visibile,
                     ed è comunque facoltativo per gli altri metodi. --}}
                <div id="riquadro-carta" class="mt-6 border-l-2 border-oro bg-panna/50 px-5 py-5">
                    <x-input-label for="carta" value="Numero della carta" />
                    <x-text-input id="carta" name="carta" type="text" inputmode="numeric"
                                  autocomplete="cc-number" placeholder="4242 4242 4242 4242"
                                  :value="old('carta')" />
                    <x-input-error :messages="$errors->get('carta')" />
                    <p class="mt-3 font-sans font-light text-[12px] leading-relaxed text-testo-soft">
                        <strong class="font-normal text-testo">Incasso simulato.</strong>
                        Nessun addebito reale: una carta qualsiasi va bene, e quelle che finiscono
                        per <strong class="font-normal text-testo">0</strong> vengono rifiutate, per
                        poter provare anche l'errore.
                    </p>
                </div>
            </section>
        </div>

        {{-- ============ riepilogo ============ --}}
        <aside class="lg:w-80 shrink-0">
            <div class="border border-caffe/15 bg-panna/50 px-6 py-6 lg:sticky lg:top-[4.5rem]">
                <h2 class="font-sans text-[11px] tracking-[0.25em] uppercase text-oro-scuro">Il tuo ordine</h2>

                <ul class="mt-5 space-y-3 font-sans font-light text-[13px]">
                    @foreach ($conto->voci as $voce)
                        <li class="flex justify-between gap-3">
                            <span class="text-testo-soft">
                                {{ $voce->riga->product->name }}
                                <span class="text-testo-soft/70">× {{ $voce->riga->quantita }}</span>
                            </span>
                            <x-prezzo :centesimi="$voce->prezzo->scontato" class="shrink-0" />
                        </li>
                    @endforeach
                </ul>

                <dl class="mt-5 pt-5 border-t border-caffe/15 font-sans text-[14px]">
                    @if ($conto->haSconti())
                        <div class="flex justify-between py-1 text-successo">
                            <dt class="font-light">Le tue condizioni</dt>
                            <dd>−<x-prezzo :centesimi="$conto->risparmio()" /></dd>
                        </div>
                    @endif

                    <div class="mt-3 pt-3 border-t border-caffe/15 flex justify-between items-baseline">
                        <dt class="font-sans text-[11px] tracking-[0.2em] uppercase text-testo-soft">Totale merce</dt>
                        <dd><x-prezzo :centesimi="$conto->totale()" class="font-serif text-2xl" /></dd>
                    </div>
                </dl>

                <p class="mt-2 font-sans font-light text-[12px] text-testo-soft">
                    Spedizione calcolata alla conferma
                </p>

                <div class="mt-6">
                    <x-primary-button class="w-full">Conferma l'ordine</x-primary-button>
                </div>

                <a href="{{ route('carrello') }}"
                   class="mt-4 block text-center font-sans text-[11px] tracking-[0.2em] uppercase
                          text-testo-soft hover:text-oro-scuro transition-colors duration-300">
                    Torna al carrello
                </a>
            </div>
        </aside>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Il riquadro della carta si mostra solo quando serve. Senza JS resta
    // visibile: il campo è facoltativo per gli altri metodi, quindi non blocca.
    (function () {
        const riquadro = document.getElementById('riquadro-carta');
        const scelte = document.querySelectorAll('input[name=metodo_pagamento]');

        const aggiorna = () => {
            const scelta = document.querySelector('input[name=metodo_pagamento]:checked');
            riquadro.hidden = !scelta || scelta.dataset.metodo !== 'carta';
        };

        scelte.forEach(s => s.addEventListener('change', aggiorna));
        aggiorna();
    })();
</script>
@endpush
