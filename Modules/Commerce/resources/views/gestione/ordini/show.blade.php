@extends('layouts.gestione')

@section('title', 'Ordine '.$ordine->numero.' — Gestione MemorAI')
@section('titolo', 'Ordine '.$ordine->numero)

@section('gestione')
    @php
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
