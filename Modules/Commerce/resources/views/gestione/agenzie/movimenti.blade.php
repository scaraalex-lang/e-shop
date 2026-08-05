@extends('layouts.gestione')

@section('title', 'Movimenti — '.$agenzia->ragione_sociale.' — Gestione MemorAI')
@section('titolo', 'Movimenti — '.$agenzia->ragione_sociale)

@section('gestione')
@php
    use Modules\Commerce\Enums\MetodoPagamento;
    use Modules\Commerce\Enums\StatoPagamento;
@endphp

<a href="{{ route('gestione.agenzie.show', $agenzia) }}"
   class="font-sans text-[11px] tracking-[0.22em] uppercase text-testo-soft hover:text-oro-scuro transition-colors duration-300">
    ← {{ $agenzia->ragione_sociale }}
</a>

<div class="mt-8 grid gap-6 sm:grid-cols-3">
    <div class="border border-caffe/15 bg-panna/50 px-6 py-5">
        <p class="font-sans text-[10px] tracking-[0.2em] uppercase text-testo-soft">Da fatturare</p>
        <x-prezzo :centesimi="$daFatturare" class="mt-2 block font-serif text-2xl" />
    </div>
    <div class="border border-caffe/15 bg-panna/50 px-6 py-5">
        <p class="font-sans text-[10px] tracking-[0.2em] uppercase text-testo-soft">Fatturato, da saldare</p>
        <x-prezzo :centesimi="$daSaldare" class="mt-2 block font-serif text-2xl" />
    </div>
    <div class="border border-caffe/15 bg-panna/50 px-6 py-5">
        <p class="font-sans text-[10px] tracking-[0.2em] uppercase text-testo-soft">Incassato a carta</p>
        <x-prezzo :centesimi="$incassatoCarta" class="mt-2 block font-serif text-2xl" />
    </div>
</div>

<div class="mt-8 border border-caffe/15">
    @forelse ($ordini as $ordine)
        @php
            [$etichetta, $colore] = match (true) {
                $ordine->metodo_pagamento === MetodoPagamento::Contrassegno => ['Alla consegna', 'text-testo-soft'],
                $ordine->stato_pagamento === StatoPagamento::Pagato => ['Pagato il '.$ordine->pagato_at->format('d/m/Y'), 'text-successo'],
                $ordine->stato_pagamento === StatoPagamento::Fallito => ['Pagamento non riuscito', 'text-errore'],
                $ordine->metodo_pagamento === MetodoPagamento::Fattura && $ordine->fatturata() => ['Fatturato il '.$ordine->fattura_emessa_at->format('d/m/Y').' (n. '.$ordine->fattura_numero.')', 'text-oro-scuro'],
                $ordine->metodo_pagamento === MetodoPagamento::Fattura => ['Da fatturare', 'text-oro-scuro'],
                default => ['Da pagare', 'text-oro-scuro'],
            };
        @endphp
        <a href="{{ route('gestione.ordini.show', $ordine) }}"
           class="flex flex-wrap items-center gap-x-8 gap-y-2 px-6 py-5
                  border-b border-caffe/10 last:border-b-0
                  hover:bg-panna/40 transition-colors duration-200">
            <div class="min-w-[9rem]">
                <p class="font-serif text-lg tabular-nums">{{ $ordine->numero }}</p>
                <p class="mt-0.5 font-sans font-light text-[12px] text-testo-soft tabular-nums">
                    {{ $ordine->created_at->format('d/m/Y') }}
                </p>
            </div>

            <p class="min-w-[7rem] font-sans font-light text-[13px] text-testo-soft">
                {{ $ordine->metodo_pagamento->etichetta() }}
            </p>

            <p class="flex-1 min-w-[10rem] font-sans text-[13px] {{ $colore }}">
                {{ $etichetta }}
            </p>

            <div class="text-right min-w-[6rem]">
                <x-prezzo :centesimi="$ordine->valoreInDenaro()" class="font-serif text-lg" />
                @if ($ordine->crediti_usati > 0)
                    <p class="mt-0.5 font-sans font-light text-[11px] text-testo-soft">
                        +{{ $ordine->crediti_usati }} crediti
                    </p>
                @endif
            </div>
        </a>
    @empty
        <p class="px-6 py-10 text-center font-sans font-light text-[14px] text-testo-soft">
            Nessun ordine ancora per questa agenzia.
        </p>
    @endforelse
</div>

<div class="mt-8">{{ $ordini->links() }}</div>
@endsection
