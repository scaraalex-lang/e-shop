{{--
    Layout dell'area staff /gestione.
    Menu raggruppato in 5 macro-aree (dropdown orizzontale da md in su,
    fisarmonica sotto md). "Il mio account" resta fuori dalle macro-aree,
    separato a destra. Barra marrone caffè per distinguerla a colpo
    d'occhio dal bianco caldo/panna della vetrina pubblica.

    Le viste figlie riempiono: @section('titolo') e @section('gestione').
--}}
@extends('layouts.app')

@section('senza-sidebar', 1)

@php
    $macroAree = [
        'Operatività' => [
            ['Ordini', route('gestione.ordini.index'), 'gestione.ordini.*'],
            ['Studio ricordini', route('studio.ricordino'), 'studio.*'],
            ['Archivio preghiere', route('gestione.preghiere.index'), 'gestione.preghiere.*'],
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

    {{-- Barra staff: marrone caffè, per distinguerla a colpo d'occhio dal
         bianco caldo/panna della vetrina pubblica. Solo navigazione: le
         pagine restano invariate. --}}
    <div class="mb-10 bg-caffe">
        <div class="flex items-center justify-between gap-6 px-5 md:px-7 py-3.5">

            <span class="font-serif text-bianco text-lg tracking-[0.08em] shrink-0">
                Gestione
            </span>

            {{-- da md in su: dropdown orizzontale a 5 macro-aree --}}
            <nav aria-label="Sezioni della gestione" class="hidden md:block">
                <ul class="flex items-stretch gap-1 font-sans text-[12px] tracking-[0.14em] uppercase">
                    @foreach ($macroAree as $area => $voci)
                        @php $attiva = collect($voci)->contains(fn ($v) => request()->routeIs($v[2])); @endphp
                        <li>
                            <details class="group relative" data-macro-area>
                                <summary class="flex items-center gap-1.5 cursor-pointer select-none
                                                 px-3.5 py-2 transition-colors duration-300
                                                 [&::-webkit-details-marker]:hidden [&::marker]:hidden
                                                 {{ $attiva ? 'text-oro' : 'text-bianco/85 hover:text-oro' }}">
                                    {{ $area }}
                                    <svg class="w-2.5 h-2.5 transition-transform duration-200 group-open:rotate-180" viewBox="0 0 10 6" fill="none" aria-hidden="true">
                                        <path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </summary>

                                <ul class="absolute left-0 top-full z-40 min-w-[15rem] py-2
                                           bg-caffe border-t-2 border-oro shadow-lg shadow-caffe/30">
                                    @foreach ($voci as [$voce, $href, $pattern])
                                        @php $vociAttiva = request()->routeIs($pattern); @endphp
                                        <li>
                                            <a href="{{ $href }}"
                                               @class([
                                                   'block px-4 py-2 normal-case tracking-normal text-[13px] font-sans transition-colors duration-300',
                                                   'text-oro' => $vociAttiva,
                                                   'text-bianco/85 hover:text-oro' => ! $vociAttiva,
                                               ])
                                               @if ($vociAttiva) aria-current="page" @endif>
                                                {{ $voce }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </details>
                        </li>
                    @endforeach
                </ul>
            </nav>

            {{-- "Il mio account" resta fuori dalle macro-aree, separato a destra --}}
            <a href="{{ route('account') }}"
               class="hidden md:block shrink-0 pl-6 ml-2 border-l border-bianco/25
                      font-sans text-[12px] tracking-[0.14em] uppercase text-bianco/70 hover:text-oro
                      transition-colors duration-300">
                Il mio account
            </a>

            {{-- sotto md: hamburger che apre la fisarmonica --}}
            <button type="button" id="gestione-menu-toggle" aria-controls="gestione-menu-mobile" aria-expanded="false"
                    aria-label="Apri il menu di gestione"
                    class="md:hidden text-bianco hover:text-oro transition-colors duration-300">
                <x-icon.menu class="w-6 h-6" data-gestione-menu-icon-open />
                <x-icon.close class="w-6 h-6 hidden" data-gestione-menu-icon-close />
            </button>
        </div>

        {{-- sotto md: fisarmonica verticale, una voce per macro-area --}}
        <div id="gestione-menu-mobile" class="hidden md:hidden border-t border-bianco/15">
            <ul class="divide-y divide-bianco/10 font-sans text-[12px] tracking-[0.14em] uppercase">
                @foreach ($macroAree as $area => $voci)
                    @php $attiva = collect($voci)->contains(fn ($v) => request()->routeIs($v[2])); @endphp
                    <li>
                        <details class="group" @if ($attiva) open @endif>
                            <summary class="flex items-center justify-between gap-2 cursor-pointer select-none
                                             px-5 py-3.5 [&::-webkit-details-marker]:hidden [&::marker]:hidden
                                             {{ $attiva ? 'text-oro' : 'text-bianco/85' }}">
                                {{ $area }}
                                <svg class="w-2.5 h-2.5 transition-transform duration-200 group-open:rotate-180" viewBox="0 0 10 6" fill="none" aria-hidden="true">
                                    <path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </summary>
                            <ul class="bg-bianco/5">
                                @foreach ($voci as [$voce, $href, $pattern])
                                    @php $vociAttiva = request()->routeIs($pattern); @endphp
                                    <li>
                                        <a href="{{ $href }}"
                                           @class([
                                               'block pl-8 pr-5 py-3 normal-case tracking-normal text-[13px]',
                                               'text-oro' => $vociAttiva,
                                               'text-bianco/80' => ! $vociAttiva,
                                           ])>
                                            {{ $voce }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </details>
                    </li>
                @endforeach
                <li>
                    <a href="{{ route('account') }}" class="block px-5 py-3.5 text-bianco/70">
                        Il mio account
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <header class="border-b-2 border-caffe pb-5">
        <div class="flex flex-wrap items-end justify-between gap-6">
            <div>
                <span class="font-sans text-[11px] tracking-[0.35em] uppercase text-oro-scuro">Gestione</span>
                <h1 class="mt-2 font-serif text-3xl md:text-4xl font-medium leading-tight">
                    @yield('titolo')
                </h1>
            </div>
        </div>
    </header>

    @if (session('stato'))
        <div class="mt-8 border-l-2 border-successo bg-panna px-5 py-4 font-sans text-[13px]">
            {{ session('stato') }}
        </div>
    @endif

    <div class="mt-10">
        @yield('gestione')
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            var puoUsareHover = window.matchMedia('(hover: hover) and (pointer: fine)');

            document.querySelectorAll('[data-macro-area]').forEach(function (dettaglio) {
                var chiudiTimer;

                dettaglio.addEventListener('toggle', function () {
                    if (dettaglio.open) {
                        document.querySelectorAll('[data-macro-area]').forEach(function (altro) {
                            if (altro !== dettaglio) altro.open = false;
                        });
                    }
                });

                dettaglio.addEventListener('mouseenter', function () {
                    if (!puoUsareHover.matches) return;
                    clearTimeout(chiudiTimer);
                    dettaglio.open = true;
                });

                dettaglio.addEventListener('mouseleave', function () {
                    if (!puoUsareHover.matches) return;
                    chiudiTimer = setTimeout(function () { dettaglio.open = false; }, 150);
                });
            });

            document.addEventListener('click', function (e) {
                document.querySelectorAll('[data-macro-area][open]').forEach(function (dettaglio) {
                    if (!dettaglio.contains(e.target)) dettaglio.open = false;
                });
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    document.querySelectorAll('[data-macro-area][open]').forEach(function (dettaglio) {
                        dettaglio.open = false;
                    });
                }
            });

            var toggle = document.getElementById('gestione-menu-toggle');
            var panel = document.getElementById('gestione-menu-mobile');
            if (toggle && panel) {
                var iconApri = toggle.querySelector('[data-gestione-menu-icon-open]');
                var iconChiudi = toggle.querySelector('[data-gestione-menu-icon-close]');

                toggle.addEventListener('click', function () {
                    var aperto = toggle.getAttribute('aria-expanded') === 'true';
                    toggle.setAttribute('aria-expanded', String(!aperto));
                    panel.classList.toggle('hidden', aperto);
                    iconApri.classList.toggle('hidden', !aperto);
                    iconChiudi.classList.toggle('hidden', aperto);
                });
            }
        })();
    </script>
@endpush
