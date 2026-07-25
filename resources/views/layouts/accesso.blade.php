{{--
    Layout delle pagine di accesso (login, registrazione, password).
    Vive dentro la cornice della vetrina, senza sidebar categorie:
    una colonna centrata, impaginata come una pagina di rivista.

    Le viste figlie riempiono: @section('titolo'), @section('modulo')
    e, se servono, @section('occhiello') / @section('sottotitolo') / @section('piede').
    @section('larghezza') cambia l'ampiezza della colonna (moduli lunghi: max-w-3xl).
--}}
@extends('layouts.app')

@section('senza-sidebar', 1)

@section('content')
    <div class="mx-auto w-full @yield('larghezza', 'max-w-lg')">

        <header class="text-center">
            <span class="font-sans text-[11px] tracking-[0.35em] uppercase text-oro-scuro">
                @yield('occhiello', 'Area riservata')
            </span>

            <h1 class="mt-4 font-serif text-3xl md:text-4xl font-medium leading-tight">
                @yield('titolo')
            </h1>

            <span class="mx-auto mt-5 block h-px w-16 bg-oro"></span>

            @hasSection('sottotitolo')
                <p class="mx-auto mt-6 max-w-md font-sans font-light text-[14px] leading-relaxed text-testo-soft">
                    @yield('sottotitolo')
                </p>
            @endif
        </header>

        <div class="mt-10 border border-caffe/15 bg-panna/50 px-6 py-10 sm:px-10">
            @yield('modulo')
        </div>

        @hasSection('piede')
            <p class="mt-8 text-center font-sans font-light text-[13px] text-testo-soft">
                @yield('piede')
            </p>
        @endif
    </div>
@endsection
