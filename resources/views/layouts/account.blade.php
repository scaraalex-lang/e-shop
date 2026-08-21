{{--
    Layout dell'area account.
    - Cliente/agenzia: resta dentro la vetrina pubblica, con la sidebar del
      proprio account al posto delle categorie.
    - Staff: nessuna vetrina, nessuna sidebar propria — confluisce nella
      cornice unica di /gestione (layouts.gestione). "Panoramica" e "Profilo"
      sono solo un'altra pagina in quella sidebar, non una navigazione a sé.

    Le viste figlie riempiono: @section('titolo') e @section('account')
    (più @section('sottotitolo'), ignorato per lo staff).
--}}
@php
    $utente = auth()->user();
@endphp

@extends($utente->eStaff() ? 'layouts.gestione' : 'layouts.app')

@if ($utente->eStaff())

    @section('gestione')
        @yield('account')
    @endsection

@else

    @section('senza-sidebar', 1)

    @section('content')
        <div class="flex flex-col lg:flex-row gap-12">

            {{-- menu dell'account: caffè chiaro per staccarla dal pannello
                 centrale, come nella sidebar di Gestione. --}}
            <aside class="lg:w-60 shrink-0 bg-caffe/8 border border-oro/30 px-6 py-7">
                <div class="lg:sticky lg:top-[4.5rem]">
                    <span class="font-sans text-[11px] tracking-[0.35em] uppercase text-oro-scuro">
                        Il mio account
                    </span>
                    <p class="mt-3 font-serif text-2xl leading-tight">{{ $utente->name }}</p>
                    <span class="mt-4 block h-px w-12 bg-oro"></span>

                    @if ($utente->eAgenziaApprovata())
                        <div class="mt-5 flex items-center justify-between border border-caffe/15 bg-panna/40 px-4 py-3">
                            <span class="font-sans text-[10px] tracking-[0.2em] uppercase text-oro-scuro">Crediti</span>
                            <span class="font-serif text-lg tabular-nums" data-saldo-crediti>{{ $utente->agenzia->creditiSaldo() }}</span>
                        </div>
                    @endif

                    <nav aria-label="Menu account" class="mt-6">
                        <ul class="flex flex-col gap-3 font-sans text-[12px] tracking-[0.16em] uppercase">
                            @foreach ($vociAccount as [$voce, $href, $rotta])
                                @php $attiva = request()->routeIs(...(array) $rotta); @endphp
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

@endif
