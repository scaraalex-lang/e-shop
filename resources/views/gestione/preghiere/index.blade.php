@extends('layouts.gestione')

@section('title', 'Archivio preghiere')
@section('titolo', 'Archivio preghiere')

@section('gestione')
<div class="flex flex-wrap items-end justify-between gap-4">
    <p class="max-w-2xl font-sans font-light text-testo-soft leading-relaxed">
        È la galleria che si apre nel Ricordino Designer: la preghiera si sceglie da qui,
        non si riscrive ogni volta. Tieni i testi corti — su un ricordino ci stanno poche righe.
    </p>
    <x-button variant="piena" :href="route('gestione.preghiere.create')">Nuovo testo</x-button>
</div>

@if ($preghiere->isEmpty())
    <p class="mt-10 font-sans font-light text-testo-soft">
        Archivio vuoto. Finché resta così, il blocco Preghiera del designer parte dal testo segnaposto.
    </p>
@else
    @foreach ($preghiere as $categoria => $gruppo)
        <h2 class="mt-12 font-sans text-[11px] tracking-[0.28em] uppercase text-oro-scuro">{{ $categoria }}</h2>

        <ul class="mt-4 space-y-4">
            @foreach ($gruppo as $p)
                <li class="border-2 border-caffe bg-bianco px-5 py-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="font-sans text-[11px] tracking-[0.24em] uppercase {{ $p->is_active ? 'text-oro-scuro' : 'text-testo-soft' }}">
                            {{ $p->is_active ? 'in galleria' : 'nascosta' }}
                        </span>
                        <span class="font-sans text-[11px] tracking-[0.16em] uppercase text-testo-soft">
                            posizione {{ $p->sort_order }}
                        </span>
                    </div>

                    <h3 class="mt-2 font-serif text-2xl text-caffe">{{ $p->titolo }}</h3>

                    <p class="mt-2 font-serif text-[17px] leading-relaxed text-testo whitespace-pre-line">{{ $p->testo }}</p>

                    <div class="mt-4 flex flex-wrap items-center gap-4 font-sans text-[12px] tracking-[0.14em] uppercase">
                        <a href="{{ route('gestione.preghiere.edit', $p) }}"
                           class="text-oro-scuro hover:text-caffe transition-colors">Modifica</a>

                        <form method="POST" action="{{ route('gestione.preghiere.attiva', $p) }}">
                            @csrf
                            <button type="submit" class="text-oro-scuro hover:text-caffe transition-colors cursor-pointer">
                                {{ $p->is_active ? 'Nascondi' : 'Mostra' }}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('gestione.preghiere.destroy', $p) }}"
                              onsubmit="return confirm('Eliminare «{{ $p->titolo }}» dall\'archivio?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-testo-soft hover:text-oro-scuro transition-colors cursor-pointer">Elimina</button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>
    @endforeach
@endif
@endsection
