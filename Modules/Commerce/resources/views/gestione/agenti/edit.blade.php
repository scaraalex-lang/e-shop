@extends('layouts.gestione')

@section('title', $agente->nome.' — Gestione MemorAI')
@section('titolo', $agente->nome)

@section('gestione')
    <a href="{{ route('gestione.agenti.index') }}"
       class="font-sans text-[11px] tracking-[0.22em] uppercase text-testo-soft hover:text-oro-scuro transition-colors duration-300">
        ← Tutti gli agenti
    </a>

    <form method="POST" action="{{ route('gestione.agenti.update', $agente) }}" class="mt-8 max-w-xl space-y-6">
        @csrf
        @method('PUT')
        @include('commerce::gestione.agenti._form', ['agente' => $agente])
    </form>

    <div class="mt-10 max-w-xl border border-caffe/15 bg-bianco px-7 py-7">
        <h2 class="font-serif text-xl font-medium">Agenzie seguite</h2>

        @if ($agente->agenzie->isEmpty())
            <p class="mt-4 font-sans font-light text-[14px] text-testo-soft">Nessuna agenzia assegnata.</p>
        @else
            <ul class="mt-4 divide-y divide-caffe/10 font-sans text-[14px]">
                @foreach ($agente->agenzie as $agenzia)
                    <li class="py-3">
                        <a href="{{ route('gestione.agenzie.show', $agenzia) }}" class="hover:text-oro-scuro transition-colors duration-300">
                            {{ $agenzia->ragione_sociale }}
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif

        @if ($agente->agenzie->isEmpty())
            <form method="POST" action="{{ route('gestione.agenti.destroy', $agente) }}" class="mt-6"
                  onsubmit="return confirm('Eliminare questo agente?')">
                @csrf
                @method('DELETE')
                <x-danger-button>Elimina agente</x-danger-button>
            </form>
        @endif
    </div>
@endsection
