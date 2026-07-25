@extends('layouts.account')

@section('title', 'Necrologi — MemorAI')
@section('titolo', 'Necrologi')
@section('sottotitolo', 'Le card che condividete su WhatsApp e Facebook, con l\'orario del trigesimo.')

@section('account')
@if (session('stato'))
    <p class="mb-8 border-l-2 border-successo bg-panna px-5 py-4 font-sans text-[13px]">{{ session('stato') }}</p>
@endif

<div class="flex justify-end">
    <x-button :href="route('necrologi.nuovo')">Nuovo necrologio</x-button>
</div>

@if ($necrologi->isEmpty())
    <div class="mt-8 border border-caffe/15 bg-panna/50 px-8 py-14 text-center">
        <p class="font-serif text-2xl">Nessun necrologio</p>
        <p class="mt-3 font-sans font-light text-[15px] text-testo-soft">
            Il primo si crea da un defunto già registrato in una lavorazione.
        </p>
    </div>
@else
    <div class="mt-8 border border-caffe/15">
        @foreach ($necrologi as $n)
            <article class="flex flex-wrap items-center gap-x-8 gap-y-2 px-6 py-5 border-b border-caffe/10 last:border-b-0">
                <div class="min-w-[12rem] flex-1">
                    <a href="{{ route('necrologi.modifica', $n) }}"
                       class="font-serif text-lg hover:text-oro-scuro transition-colors duration-300">
                        {{ $n->defunto?->nomeCompleto() ?? 'Senza nome' }}
                    </a>
                    <p class="mt-0.5 font-sans font-light text-[12px] text-testo-soft tabular-nums">
                        @if ($n->trigesimo_at)
                            Trigesimo {{ $n->trigesimo_at->format('d/m/Y H:i') }}
                        @else
                            Trigesimo da fissare
                        @endif
                    </p>
                </div>

                @php
                    $etichetta = match (true) {
                        $n->pubblico() => ['Online', 'text-successo'],
                        $n->scaduto() && $n->pubblicato => ['Scaduto', 'text-testo-soft'],
                        ! $n->pubblicazione_consenso => ['Manca il consenso', 'text-errore'],
                        default => ['Non pubblicato', 'text-testo-soft'],
                    };
                @endphp
                <span class="font-sans text-[10px] tracking-[0.2em] uppercase {{ $etichetta[1] }} min-w-[9rem]">
                    {{ $etichetta[0] }}
                </span>

                @if ($n->pubblico())
                    <a href="{{ $n->url($agenzia->slug) }}" target="_blank" rel="noopener"
                       class="font-sans text-[10px] tracking-[0.2em] uppercase text-oro-scuro hover:text-caffe transition-colors duration-300">
                        Apri →
                    </a>
                @endif
            </article>
        @endforeach
    </div>

    <div class="mt-8">{{ $necrologi->links() }}</div>
@endif
@endsection
