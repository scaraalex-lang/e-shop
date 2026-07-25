@extends('layouts.account')

@section('title', 'Il mio account — MemorAI')
@section('titolo', 'Panoramica')
@section('sottotitolo', 'Da qui segui i tuoi ordini, riprendi le bozze dei ricordini e aggiorni i tuoi dati.')

@section('account')
    @php
        $utente = auth()->user();
        $agenzia = $utente->eAgenzia() ? $utente->agenzia : null;

        $riquadri = [
            [
                'titolo' => 'I miei ordini',
                'testo'  => 'Stato di lavorazione, spedizioni e storico degli acquisti.',
                'azione' => 'Vedi gli ordini',
                'href'   => route('ordini'),
            ],
            [
                'titolo' => 'Profilo e accesso',
                'testo'  => 'Nome, indirizzo email e password del tuo account.',
                'azione' => 'Gestisci',
                'href'   => route('account.profilo'),
            ],
        ];
    @endphp

    {{-- Stato della richiesta di account agenzia --}}
    @if ($agenzia)
        @include('account.partials.stato-agenzia', ['agenzia' => $agenzia])
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-px bg-caffe/15 border border-caffe/15">
        @foreach ($riquadri as $r)
            <article class="bg-bianco px-7 py-8 flex flex-col">
                <h2 class="font-serif text-xl font-medium">{{ $r['titolo'] }}</h2>
                <p class="mt-3 flex-1 font-sans font-light text-[14px] leading-relaxed text-testo-soft">
                    {{ $r['testo'] }}
                </p>

                @if ($r['href'])
                    <a href="{{ $r['href'] }}"
                       class="mt-6 self-start font-sans text-[11px] tracking-[0.22em] uppercase text-oro-scuro
                              hover:text-caffe transition-colors duration-300">
                        {{ $r['azione'] }} →
                    </a>
                @else
                    <span class="mt-6 self-start font-sans text-[11px] tracking-[0.22em] uppercase text-testo-soft/60">
                        {{ $r['azione'] }}
                    </span>
                @endif
            </article>
        @endforeach

        @if ($agenzia)
            {{-- dati dell'agenzia --}}
            <article class="bg-panna px-7 py-8 flex flex-col">
                <span class="font-sans text-[11px] tracking-[0.3em] uppercase text-oro-scuro">Onoranze funebri</span>
                <h2 class="mt-3 font-serif text-xl font-medium">{{ $agenzia->ragione_sociale }}</h2>
                <dl class="mt-4 flex-1 font-sans font-light text-[13px] leading-relaxed text-testo-soft space-y-1">
                    <div class="flex gap-2">
                        <dt class="shrink-0">P. IVA</dt>
                        <dd>{{ $agenzia->partita_iva }}</dd>
                    </div>
                    <div class="flex gap-2">
                        <dt class="shrink-0">Sede</dt>
                        <dd>{{ $agenzia->indirizzoCompleto() }}</dd>
                    </div>
                </dl>
            </article>
        @else
            {{-- invito alle onoranze funebri --}}
            <article class="bg-panna px-7 py-8 flex flex-col">
                <span class="font-sans text-[11px] tracking-[0.3em] uppercase text-oro-scuro">Onoranze funebri</span>
                <h2 class="mt-3 font-serif text-xl font-medium">Account agenzia</h2>
                <p class="mt-3 flex-1 font-sans font-light text-[14px] leading-relaxed text-testo-soft">
                    Listino riservato, ordini ricorrenti e bozze condivisibili con la famiglia.
                </p>
                <a href="{{ route('registrazione.agenzia') }}"
                   class="mt-6 self-start font-sans text-[11px] tracking-[0.22em] uppercase text-oro-scuro
                          hover:text-caffe transition-colors duration-300">
                    Richiedi l'accesso →
                </a>
            </article>
        @endif
    </div>
@endsection
