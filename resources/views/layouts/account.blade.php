{{--
    Layout dell'area account (utente autenticato).
    Sostituisce la sidebar categorie con il menu dell'account.

    Le viste figlie riempiono: @section('titolo') e @section('account')
    (più @section('sottotitolo') se serve).
--}}
@extends('layouts.app')

@section('senza-sidebar', 1)

@php
    $utente = auth()->user();

    // Lo staff non è un cliente: non fa ordini a se stesso, quindi la voce
    // non porta al proprio elenco ordini (vuoto) ma al pannello di tutti
    // gli ordini della piattaforma.
    $voci = $utente->eStaff()
        ? [
            ['Panoramica', route('account'), 'account'],
            ['Ordini', route('gestione.ordini.index'), ['gestione.ordini.*']],
        ]
        : [
            ['Panoramica', route('account'), 'account'],
        ];

    // Dove si sceglie cosa mettere in un ordine nuovo (servizio/prodotto/kit):
    // un percorso da agenzia, l'equivalente B2B dello sfogliare la vetrina.
    // Prima di "I miei ordini": si comincia da qui, poi si tiene traccia.
    if (! $utente->eStaff() && $utente->eAgenziaApprovata()) {
        $voci[] = ['Nuovo ordine', route('ordini.nuovo'), 'ordini.nuovo'];
    }

    if (! $utente->eStaff()) {
        $voci[] = ['I miei ordini', route('ordini'), ['ordini', 'ordine', 'lavorazione*']];
    }

    // I necrologi sono uno strumento dell'agenzia, non un prodotto: non
    // passano dall'ordine e stanno accanto agli editor.
    if ($utente->eAgenzia()) {
        $voci[] = ['Necrologi', route('necrologi.index'), 'necrologi.*'];
    }

    // Gli editor compaiono solo a chi li apre di mestiere: il cliente ci
    // arriva dalla lavorazione del proprio ordine (vedi AccessoStudio). Si
    // entra dall'archivio delle pratiche, non dritti nel designer.
    if ($utente->eStaff() || $utente->eAgenziaApprovata()) {
        $voci[] = ['Studio ricordini', route('pratiche.index'), ['pratiche.*', 'studio.*']];
    }

    $voci[] = ['Profilo e accesso', route('account.profilo'), 'account.profilo'];

    if ($utente->eStaff()) {
        $voci[] = ['Gestione', route('gestione.agenzie.index'), 'gestione.*'];
    }
@endphp

@section('content')
    <div class="flex flex-col lg:flex-row gap-12">

        {{-- menu dell'account --}}
        <aside class="lg:w-60 shrink-0">
            <div class="lg:sticky lg:top-[4.5rem]">
                <span class="font-sans text-[11px] tracking-[0.35em] uppercase text-oro-scuro">
                    {{ $utente->eStaff() ? 'Area staff' : 'Il mio account' }}
                </span>
                <p class="mt-3 font-serif text-2xl leading-tight">{{ auth()->user()->name }}</p>
                <span class="mt-4 block h-px w-12 bg-oro"></span>

                <nav aria-label="Menu account" class="mt-6">
                    <ul class="flex flex-col gap-3 font-sans text-[12px] tracking-[0.16em] uppercase">
                        @foreach ($voci as [$voce, $href, $rotta])
                            @php $attiva = $rotta && request()->routeIs(...(array) $rotta); @endphp
                            <li>
                                <a href="{{ $href }}"
                                   @class([
                                       'inline-block transition-colors duration-300',
                                       'text-oro-scuro' => $attiva,
                                       'text-testo hover:text-oro-scuro' => ! $attiva,
                                   ])
                                   @if ($attiva) aria-current="page" @endif>
                                    {{ $voce }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>

                <hr class="my-6 h-px w-full border-0 bg-caffe/15">

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="font-sans text-[12px] tracking-[0.16em] uppercase text-testo-soft
                                   hover:text-oro-scuro transition-colors duration-300 cursor-pointer">
                        Esci
                    </button>
                </form>
            </div>
        </aside>

        {{-- contenuto --}}
        <div class="flex-1 min-w-0">
            <header>
                <h1 class="font-serif text-3xl md:text-4xl font-medium leading-tight">
                    @yield('titolo')
                </h1>
                <span class="mt-5 block h-px w-16 bg-oro"></span>

                @hasSection('sottotitolo')
                    <p class="mt-5 max-w-2xl font-sans font-light text-[14px] leading-relaxed text-testo-soft">
                        @yield('sottotitolo')
                    </p>
                @endif
            </header>

            <div class="mt-10">
                @yield('account')
            </div>
        </div>
    </div>
@endsection
