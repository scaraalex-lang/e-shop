{{--
    Layout unico dell'area staff: /gestione e /account per chi ha eStaff().
    Sidebar fissa a sinistra con tutte le macro-aree operative più Panoramica,
    Profilo e Esci — un solo shell per tutta l'operatività, mai la vetrina
    pubblica (niente carrello: vedi @section('senza-vetrina') in layouts.app).

    Le viste figlie riempiono: @section('titolo') e @section('gestione').
    layouts.account fa da ponte per le pagine condivise con l'account
    cliente (Panoramica, Profilo): @section('gestione') @yield('account') @endsection.
--}}
@extends('layouts.app')

@section('senza-sidebar', 1)
@section('senza-vetrina', 1)

@php
    $vociStaff = [
        ['Panoramica', route('account'), 'account'],
    ];

    $macroAree = [
        'Operatività' => [
            ['Ordini', route('gestione.ordini.index'), fn () => request()->routeIs('gestione.ordini.*') && request()->query('provenienza') !== 'b2b'],
            ['Ordini agenzie (B2B)', route('gestione.ordini.index', ['provenienza' => 'b2b']), fn () => request()->routeIs('gestione.ordini.*') && request()->query('provenienza') === 'b2b'],
            ['Studio ricordini', route('studio.ricordino'), 'studio.*'],
            ['Archivio preghiere', route('gestione.preghiere.index'), 'gestione.preghiere.*'],
            ['Video memoriale (test)', route('gestione.video-memoriale.index'), 'gestione.video-memoriale.*'],
        ],
        'Catalogo' => [
            ['Prodotti', route('gestione.prodotti.index'), 'gestione.prodotti.*'],
            ['Categorie', route('gestione.categorie.index'), 'gestione.categorie.*'],
            ['Kit componibili', route('gestione.kit.index'), 'gestione.kit.*'],
        ],
        'Anagrafica' => [
            ['Agenzie', route('gestione.agenzie.index'), 'gestione.agenzie.*'],
            ['Agenti di vendita', route('gestione.agenti.index'), 'gestione.agenti.*'],
        ],
        'Configurazione' => [
            ['Servizi (crediti)', route('gestione.servizi.index'), 'gestione.servizi.*'],
        ],
        'Contenuti vetrina' => [
            ['Contenuti home', route('gestione.contenuti.edit'), 'gestione.contenuti.*'],
            ['Menu di navigazione pubblico', route('gestione.menu.index'), 'gestione.menu.*'],
        ],
    ];
@endphp

@section('content')
    <div class="flex flex-col lg:flex-row gap-12">

        {{-- sidebar fissa: unica navigazione per tutta l'operatività. Caffè
             chiaro per staccarla dal pannello centrale, sempre bianco/panna. --}}
        <aside class="lg:w-64 shrink-0 bg-caffe/8 border border-oro/30 px-6 py-7">
            <div class="lg:sticky lg:top-[1.5rem]">
                <span class="font-sans text-[11px] tracking-[0.35em] uppercase text-oro-scuro">
                    Area staff
                </span>
                <p class="mt-3 font-serif text-2xl leading-tight">Gestione</p>
                <span class="mt-4 block h-px w-12 bg-oro"></span>

                <nav aria-label="Sezioni della gestione" class="mt-6 space-y-6">
                    <ul class="font-sans text-[12px] tracking-[0.16em] uppercase">
                        @foreach ($vociStaff as [$voce, $href, $rotta])
                            <li>
                                <a href="{{ $href }}"
                                   @class([
                                       'inline-block py-1 transition-colors duration-300',
                                       'text-oro-scuro' => request()->routeIs($rotta),
                                       'text-testo hover:text-oro-scuro' => ! request()->routeIs($rotta),
                                   ])
                                   @if (request()->routeIs($rotta)) aria-current="page" @endif>
                                    {{ $voce }}
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    {{-- Fisarmonica: un gruppo aperto alla volta (<details name="...">
                         nativo, niente JS). Il gruppo che contiene la pagina corrente
                         parte aperto, gli altri chiusi. --}}
                    @foreach ($macroAree as $area => $voci)
                        @php
                            $vociValutate = collect($voci)->map(fn ($v) => [
                                $v[0], $v[1], $v[2] instanceof \Closure ? $v[2]() : request()->routeIs($v[2]),
                            ]);
                            $areaAttiva = $vociValutate->contains(fn ($v) => $v[2]);
                        @endphp
                        <details name="macro-area" @if ($areaAttiva) open @endif
                                  class="group border-b border-caffe/10 pb-2 last:border-b-0 last:pb-0">
                            <summary class="flex items-center justify-between cursor-pointer list-none
                                             font-sans text-[10px] tracking-[0.25em] uppercase text-testo-soft/70
                                             py-1 [&::-webkit-details-marker]:hidden">
                                {{ $area }}
                                <span class="text-oro-scuro text-[13px] leading-none group-open:hidden">+</span>
                                <span class="text-oro-scuro text-[13px] leading-none hidden group-open:inline">&minus;</span>
                            </summary>
                            <ul class="mt-2 pb-3 font-sans text-[12px] tracking-[0.16em] uppercase space-y-1.5">
                                @foreach ($vociValutate as [$voce, $href, $attiva])
                                    <li>
                                        <a href="{{ $href }}"
                                           @class([
                                               'inline-block py-0.5 transition-colors duration-300',
                                               'text-oro-scuro' => $attiva,
                                               'text-testo hover:text-oro-scuro' => ! $attiva,
                                           ])
                                           @if ($attiva) aria-current="page" @endif>
                                            {{ $voce }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </details>
                    @endforeach
                </nav>

                <hr class="my-6 h-px w-full border-0 bg-caffe/15">

                <ul class="font-sans text-[12px] tracking-[0.16em] uppercase space-y-3">
                    <li>
                        <a href="{{ route('account.profilo') }}"
                           @class([
                               'inline-block transition-colors duration-300',
                               'text-oro-scuro' => request()->routeIs('account.profilo'),
                               'text-testo hover:text-oro-scuro' => ! request()->routeIs('account.profilo'),
                           ])
                           @if (request()->routeIs('account.profilo')) aria-current="page" @endif>
                            Profilo e accesso
                        </a>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="font-sans text-[12px] tracking-[0.16em] uppercase text-testo-soft
                                           hover:text-oro-scuro transition-colors duration-300 cursor-pointer">
                                Esci
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </aside>

        {{-- contenuto --}}
        <div class="flex-1 min-w-0">
            <header class="border-b-2 border-caffe pb-5">
                <span class="font-sans text-[11px] tracking-[0.35em] uppercase text-oro-scuro">Gestione</span>
                <h1 class="mt-2 font-serif text-3xl md:text-4xl font-medium leading-tight">
                    @yield('titolo')
                </h1>
            </header>

            @if (session('stato'))
                <div class="mt-8 border-l-2 border-successo bg-panna px-5 py-4 font-sans text-[13px]">
                    {{ session('stato') }}
                </div>
            @endif

            <div class="mt-10">
                @yield('gestione')
            </div>
        </div>
    </div>
@endsection
