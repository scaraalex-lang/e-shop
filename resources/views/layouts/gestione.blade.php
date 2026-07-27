{{--
    Layout dell'area staff /gestione.
    Primo pezzo della dashboard operativa: per ora c'è solo l'approvazione
    delle agenzie, le altre voci si aggiungono qui man mano.

    Le viste figlie riempiono: @section('titolo') e @section('gestione').
--}}
@extends('layouts.app')

@section('senza-sidebar', 1)

@php
    $sezioni = [
        ['Agenzie', route('gestione.agenzie.index'), 'gestione.agenzie.*'],
        ['Prodotti', route('gestione.prodotti.index'), 'gestione.prodotti.*'],
        ['Studio', route('studio.ricordino'), 'studio.*'],
    ];
@endphp

@section('content')
    <header class="border-b-2 border-caffe pb-5">
        <div class="flex flex-wrap items-end justify-between gap-6">
            <div>
                <span class="font-sans text-[11px] tracking-[0.35em] uppercase text-oro-scuro">Gestione</span>
                <h1 class="mt-2 font-serif text-3xl md:text-4xl font-medium leading-tight">
                    @yield('titolo')
                </h1>
            </div>

            <nav aria-label="Sezioni della gestione">
                <ul class="flex flex-wrap gap-6 font-sans text-[12px] tracking-[0.16em] uppercase">
                    @foreach ($sezioni as [$voce, $href, $pattern])
                        @php $attiva = request()->routeIs($pattern); @endphp
                        <li>
                            <a href="{{ $href }}"
                               @class([
                                   'transition-colors duration-300',
                                   'text-oro-scuro' => $attiva,
                                   'text-testo hover:text-oro-scuro' => ! $attiva,
                               ])
                               @if ($attiva) aria-current="page" @endif>
                                {{ $voce }}
                            </a>
                        </li>
                    @endforeach
                    <li>
                        <a href="{{ route('account') }}"
                           class="text-testo-soft hover:text-oro-scuro transition-colors duration-300">
                            Il mio account
                        </a>
                    </li>
                </ul>
            </nav>
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
