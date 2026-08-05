@extends('layouts.account')

@section('title', 'I miei ordini — MemorAI')
@section('titolo', 'I miei ordini')
@section('sottotitolo', 'Ogni ordine con il suo stato, dalla conferma alla consegna.')

@section('account')
@php
    use Modules\Commerce\Enums\MetodoPagamento;
    use Modules\Commerce\Enums\StatoPagamento;
@endphp
@if ($ordini->isEmpty())
    <div class="border border-caffe/15 bg-panna/50 px-8 py-14 text-center">
        <p class="font-serif text-2xl">Non hai ancora ordinato nulla</p>
        <p class="mt-3 font-sans font-light text-[15px] text-testo-soft">
            Quando farai il primo ordine lo troverai qui, con tutti i passaggi.
        </p>
        <div class="mt-8">
            <x-button :href="url('/')">Sfoglia le collezioni</x-button>
        </div>
    </div>
@else
    <div class="border border-caffe/15">
        @foreach ($ordini as $ordine)
            @php
                $defunto = $ordine->defunto_id ? ($defuntiPerOrdine[$ordine->defunto_id] ?? null) : null;
                $fotoDefunto = $defunto ? ($fotoPerDefunto[$defunto->id] ?? null) : null;
                $daPagare = $ordine->metodo_pagamento === MetodoPagamento::Carta
                    && in_array($ordine->stato_pagamento, [StatoPagamento::InAttesa, StatoPagamento::Fallito], true);
            @endphp
            <article class="flex flex-wrap items-center gap-x-8 gap-y-3 px-6 py-5
                            border-b border-caffe/10 last:border-b-0
                            hover:bg-panna/40 transition-colors duration-200">
                @if ($defunto)
                    <div class="flex items-center gap-3 min-w-[3rem]">
                        <div class="h-12 w-12 shrink-0 border border-oro/40 bg-panna overflow-hidden">
                            @if ($fotoDefunto)
                                <img src="{{ $fotoDefunto }}" alt="" class="h-full w-full object-cover">
                            @endif
                        </div>
                    </div>
                @endif

                <div class="min-w-[9rem]">
                    <a href="{{ route('ordine', $ordine) }}"
                       class="font-serif text-lg text-testo hover:text-oro-scuro transition-colors duration-300 tabular-nums">
                        {{ $ordine->numero }}
                    </a>
                    <p class="mt-0.5 font-sans font-light text-[12px] text-testo-soft tabular-nums">
                        {{ $ordine->created_at->format('d/m/Y') }}
                    </p>
                    @if ($defunto)
                        <p class="mt-0.5 font-sans font-light text-[12px] text-testo-soft">{{ $defunto->nomeCompleto() }}</p>
                    @endif
                </div>

                <p class="flex-1 min-w-[10rem] font-sans font-light text-[13px] text-testo-soft">
                    @if ($ordine->righe->isEmpty() && $ordine->servizi->isNotEmpty())
                        Servizio digitale · {{ $ordine->servizi->pluck('servizioEditor.etichetta')->filter()->implode(', ') }}
                    @else
                        {{ $ordine->righe->count() }} {{ $ordine->righe->count() === 1 ? 'articolo' : 'articoli' }}
                        · {{ $ordine->pezzi() }} pezzi
                        @if ($ordine->richiede_lavorazione)
                            <span class="text-oro-scuro">· con fotografia</span>
                        @endif
                    @endif
                </p>

                <span class="min-w-[8rem]">
                    <span class="block font-sans text-[10px] tracking-[0.2em] uppercase text-oro-scuro">
                        {{ $ordine->stato->etichetta() }}
                    </span>
                    @if ($daPagare)
                        <span @class([
                            'mt-1 block font-sans text-[10px] tracking-[0.15em] uppercase',
                            'text-errore' => $ordine->stato_pagamento === StatoPagamento::Fallito,
                            'text-oro-scuro' => $ordine->stato_pagamento === StatoPagamento::InAttesa,
                        ])>
                            {{ $ordine->stato_pagamento === StatoPagamento::Fallito ? 'Pagamento non riuscito' : 'Da pagare' }}
                        </span>
                    @endif
                </span>

                @if ($ordine->righe->isEmpty() && $ordine->servizi->isNotEmpty())
                    <p class="font-serif text-lg text-right min-w-[6rem] tabular-nums">
                        {{ $ordine->servizi->sum('costo_crediti') }} crediti
                    </p>
                @else
                    <x-prezzo :centesimi="$ordine->totale" class="font-serif text-lg text-right min-w-[6rem]" />
                @endif
            </article>
        @endforeach
    </div>

    <div class="mt-8">{{ $ordini->links() }}</div>
@endif
@endsection
