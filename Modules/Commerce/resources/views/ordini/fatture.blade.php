@extends('layouts.account')

@section('title', 'Fatture — MemorAI')
@section('titolo', 'Fatture')
@section('sottotitolo', 'Ogni ordine con il metodo di pagamento e a che punto è.')

@section('account')
@php
    use Modules\Commerce\Enums\MetodoPagamento;
    use Modules\Commerce\Enums\StatoPagamento;
@endphp

@if ($ordini->isEmpty())
    <div class="border border-caffe/15 bg-panna/50 px-8 py-14 text-center">
        <p class="font-serif text-2xl">Nessun ordine ancora</p>
        <p class="mt-3 font-sans font-light text-[15px] text-testo-soft">
            Qui trovi lo stato di pagamento e di fatturazione di ogni ordine, appena ne farai uno.
        </p>
    </div>
@else
    <div class="border border-caffe/15">
        @foreach ($ordini as $ordine)
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
            <a href="{{ route('ordine', $ordine) }}"
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

                <x-prezzo :centesimi="$ordine->totale" class="font-serif text-lg text-right min-w-[6rem]" />
            </a>
        @endforeach
    </div>

    <div class="mt-8">{{ $ordini->links() }}</div>
@endif
@endsection
