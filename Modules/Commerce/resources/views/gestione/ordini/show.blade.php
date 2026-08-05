@extends('layouts.gestione')

@section('title', 'Ordine '.$ordine->numero.' — Gestione MemorAI')
@section('titolo', 'Ordine '.$ordine->numero)

@section('gestione')
    @php
        use Modules\Commerce\Enums\MetodoPagamento;
        use Modules\Commerce\Enums\StatoOrdine;
    @endphp

    <a href="{{ route('gestione.ordini.index') }}"
       class="font-sans text-[11px] tracking-[0.22em] uppercase text-testo-soft hover:text-oro-scuro transition-colors duration-300">
        ← Tutti gli ordini
    </a>

    <div class="mt-8 grid gap-10 lg:grid-cols-[1fr_22rem]">

        <div class="space-y-px bg-caffe/15 border border-caffe/15 self-start">
            <section class="bg-bianco px-7 py-7">
                <h2 class="font-serif text-xl font-medium">
                    {{ $ordine->agenzia_id ? 'Agenzia' : 'Cliente' }}
                </h2>
                <dl class="mt-5 grid gap-x-8 gap-y-3 sm:grid-cols-2 font-sans font-light text-[14px]">
                    <div>
                        <dt class="font-sans text-[10px] tracking-[0.2em] uppercase text-testo-soft">Nome</dt>
                        <dd class="mt-1 text-testo">{{ $ordine->agenzia?->ragione_sociale ?? $ordine->user?->name }}</dd>
                    </div>
                    <div>
                        <dt class="font-sans text-[10px] tracking-[0.2em] uppercase text-testo-soft">Email</dt>
                        <dd class="mt-1 text-testo">{{ $ordine->user?->email }}</dd>
                    </div>
                </dl>
            </section>

            <section class="bg-bianco px-7 py-7">
                <h2 class="font-serif text-xl font-medium">Consegna</h2>
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
                @if ($ordine->corriere)
                    <p class="mt-4 font-sans font-light text-[13px] text-testo-soft">
                        Spedito con <strong class="text-testo font-normal">{{ $ordine->corriere }}</strong> ·
                        tracking <strong class="text-testo font-normal tabular-nums">{{ $ordine->tracking_numero }}</strong>
                    </p>
                @endif
            </section>

            @if ($ordine->metodo_pagamento === MetodoPagamento::Fattura)
                <section class="bg-bianco px-7 py-7">
                    <h2 class="font-serif text-xl font-medium">Fatturazione</h2>

                    @if ($ordine->fatturata())
                        <p class="mt-2 font-sans font-light text-[14px] text-testo-soft">
                            Fattura <strong class="text-testo font-normal">{{ $ordine->fattura_numero }}</strong>
                            emessa il {{ $ordine->fattura_emessa_at->format('d/m/Y') }}.
                        </p>
                    @else
                        <p class="mt-2 font-sans font-light text-[14px] text-testo-soft">
                            Ordine a termini: non ancora fatturato.
                        </p>
                    @endif

                    @if (! $ordine->fatturata())
                        <form method="POST" action="{{ route('gestione.ordini.fattura', $ordine) }}" class="mt-4 flex flex-wrap items-end gap-4">
                            @csrf
                            <div>
                                <x-input-label for="fattura_numero" value="Numero fattura" />
                                <x-text-input id="fattura_numero" name="fattura_numero" value="{{ old('fattura_numero') }}" required />
                                <x-input-error :messages="$errors->get('fattura_numero')" class="mt-2" />
                            </div>
                            <x-secondary-button type="submit">Segna fatturato</x-secondary-button>
                        </form>
                    @elseif ($ordine->stato_pagamento !== \Modules\Commerce\Enums\StatoPagamento::Pagato)
                        <form method="POST" action="{{ route('gestione.ordini.fattura.pagata', $ordine) }}" class="mt-4 flex flex-wrap items-end gap-4">
                            @csrf
                            <div>
                                <x-input-label for="riferimento_pagamento" value="Riferimento del saldo (facoltativo)" />
                                <x-text-input id="riferimento_pagamento" name="riferimento_pagamento"
                                              value="{{ old('riferimento_pagamento') }}" placeholder="es. bonifico del 10/08" />
                            </div>
                            <x-secondary-button type="submit">Segna saldata</x-secondary-button>
                        </form>
                    @else
                        <p class="mt-3 font-sans font-light text-[13px] text-successo">
                            Saldata {{ $ordine->pagato_at ? 'il '.$ordine->pagato_at->format('d/m/Y') : '' }}.
                        </p>
                    @endif
                </section>
            @endif

            @if ($ordine->richiede_lavorazione)
                <section class="bg-bianco px-7 py-7">
                    <h2 class="font-serif text-xl font-medium">Lavorazione fotografica</h2>
                    <p class="mt-2 font-sans font-light text-[14px] text-testo-soft">
                        Questo ordine comprende articoli personalizzati con il ritratto.
                    </p>
                    @if ($ordine->lavorazioneApribile())
                        <div class="mt-4">
                            <a href="{{ route('lavorazione', $ordine) }}"
                               class="inline-flex items-center justify-center gap-2 cursor-pointer select-none
                                      font-sans uppercase text-[12px] tracking-[0.22em] px-8 py-3.5
                                      bg-oro text-bianco hover:bg-oro-scuro transition-all duration-300 ease-out">
                                Apri la lavorazione
                            </a>
                        </div>
                        <p class="mt-3 font-sans font-light text-[12px] text-testo-soft">
                            Da lì apri il Ricordino Designer e generi il PDF pronto stampa.
                        </p>
                    @else
                        <p class="mt-3 font-sans font-light text-[13px] text-testo-soft">
                            Non ancora aperta dal cliente, o l'ordine non è più aperto.
                        </p>
                    @endif
                </section>
            @endif

            @if ($ordine->servizi->isNotEmpty())
                <section class="bg-bianco px-7 py-7">
                    <h2 class="font-serif text-xl font-medium">Servizi attivati</h2>

                    @if ($fotoUrl)
                        <div class="mt-4 flex items-center gap-4">
                            <div class="h-24 w-24 shrink-0 border border-oro/40 bg-panna overflow-hidden">
                                <img src="{{ $fotoUrl }}" alt="" class="h-full w-full object-cover">
                            </div>
                            <div>
                                <p class="font-sans text-[10px] tracking-[0.2em] uppercase text-testo-soft">Foto principale</p>
                                <p class="mt-1 font-serif text-lg">{{ $defunto->nomeCompleto() }}</p>
                            </div>
                        </div>
                    @elseif ($defunto)
                        <p class="mt-3 font-sans font-light text-[13px] text-testo-soft">
                            Foto non ancora confermata per {{ $defunto->nomeCompleto() }}.
                        </p>
                    @endif

                    <div class="mt-4 divide-y divide-caffe/10">
                        @foreach ($ordine->servizi as $servizio)
                            <div class="flex items-center justify-between gap-4 py-4">
                                <p class="font-sans text-[14px]">{{ $servizio->servizioEditor?->etichetta }}</p>
                                <span class="font-sans text-[13px] text-testo-soft tabular-nums">{{ $servizio->costo_crediti }} crediti</span>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="bg-bianco px-7 py-7">
                <h2 class="font-serif text-xl font-medium">Cosa contiene</h2>
                <div class="mt-4 divide-y divide-caffe/10">
                    @foreach ($ordine->righe as $riga)
                        <div class="flex flex-wrap items-center justify-between gap-4 py-4">
                            <div>
                                <p class="font-sans text-[14px]">{{ $riga->nome }}</p>
                                <p class="mt-1 font-sans font-light text-[12px] text-testo-soft">
                                    {{ $riga->sku }} · {{ $riga->quantita }} {{ $riga->quantita === 1 ? 'pezzo' : 'pezzi' }}
                                    @if ($riga->richiede_foto)
                                        <span class="text-oro-scuro">· con fotografia</span>
                                    @endif
                                </p>
                            </div>
                            <x-prezzo :centesimi="$riga->prezzo" class="font-serif text-lg" />
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 pt-4 border-t border-caffe/15 flex justify-between items-baseline">
                    <dt class="font-sans text-[11px] tracking-[0.2em] uppercase text-testo-soft">Totale</dt>
                    <dd><x-prezzo :centesimi="$ordine->totale" class="font-serif text-xl" /></dd>
                </div>
                @if ($ordine->crediti_usati > 0)
                    <p class="mt-1 font-sans font-light text-[12px] text-oro-scuro text-right">
                        di cui {{ $ordine->crediti_usati }} crediti — resta in denaro
                        <x-prezzo :centesimi="$ordine->valoreInDenaro()" class="tabular-nums" />
                    </p>
                @endif
            </section>
        </div>

        <aside class="border border-caffe/15 bg-panna/50 px-6 py-7 self-start">
            <span class="font-sans text-[10px] tracking-[0.22em] uppercase text-testo-soft">Stato</span>
            <p class="mt-2 font-serif text-2xl">{{ $ordine->stato->etichetta() }}</p>
            <p class="mt-2 font-sans font-light text-[13px] text-testo-soft">{{ $ordine->stato->racconto() }}</p>

            <hr class="my-6 h-px w-full border-0 bg-caffe/15">

            @if ($ordine->stato === StatoOrdine::InProduzione)
                <form method="POST" action="{{ route('gestione.ordini.spedisci', $ordine) }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="corriere" value="Corriere" />
                        <x-text-input id="corriere" name="corriere" value="{{ old('corriere') }}" required />
                        <x-input-error :messages="$errors->get('corriere')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="tracking_numero" value="Numero di tracking" />
                        <x-text-input id="tracking_numero" name="tracking_numero" value="{{ old('tracking_numero') }}" required />
                        <x-input-error :messages="$errors->get('tracking_numero')" class="mt-2" />
                    </div>
                    <x-primary-button class="w-full">Segna spedito</x-primary-button>
                </form>
            @elseif ($ordine->stato === StatoOrdine::Spedito)
                <form method="POST" action="{{ route('gestione.ordini.consegnato', $ordine) }}">
                    @csrf
                    <x-primary-button class="w-full">Segna consegnato</x-primary-button>
                </form>
            @else
                <p class="font-sans font-light text-[13px] text-testo-soft">
                    Nessuna azione disponibile in questo stato.
                </p>
            @endif
        </aside>
    </div>
@endsection
