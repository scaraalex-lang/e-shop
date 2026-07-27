@extends('layouts.gestione')

@section('title', 'Agenti di vendita — Gestione MemorAI')
@section('titolo', 'Agenti di vendita')

@section('gestione')
    <div class="flex justify-end">
        <a href="{{ route('gestione.agenti.create') }}"
           class="inline-flex items-center justify-center gap-2 cursor-pointer select-none
                  font-sans uppercase text-[12px] tracking-[0.22em] px-8 py-3.5
                  bg-oro text-bianco hover:bg-oro-scuro transition-all duration-300 ease-out">
            Nuovo agente
        </a>
    </div>

    @if ($agenti->isEmpty())
        <p class="mt-10 font-sans font-light text-[15px] text-testo-soft">
            Nessun agente ancora.
        </p>
    @else
        <div class="mt-8 overflow-x-auto border border-caffe/15">
            <table class="w-full min-w-[42rem] border-collapse">
                <thead>
                    <tr class="bg-panna text-left font-sans text-[10px] tracking-[0.22em] uppercase text-testo-soft">
                        <th class="px-5 py-4 font-normal">Nome</th>
                        <th class="px-5 py-4 font-normal">Contatti</th>
                        <th class="px-5 py-4 font-normal">Agenzie</th>
                        <th class="px-5 py-4 font-normal"></th>
                    </tr>
                </thead>
                <tbody class="font-sans font-light text-[14px]">
                    @foreach ($agenti as $agente)
                        <tr class="border-t border-caffe/10 hover:bg-panna/50 transition-colors duration-200">
                            <td class="px-5 py-4">
                                <a href="{{ route('gestione.agenti.edit', $agente) }}"
                                   class="font-serif text-[17px] text-testo hover:text-oro-scuro transition-colors duration-300">
                                    {{ $agente->nome }}
                                </a>
                            </td>
                            <td class="px-5 py-4 text-testo-soft">
                                {{ $agente->email ?: '—' }} @if ($agente->telefono) · {{ $agente->telefono }} @endif
                            </td>
                            <td class="px-5 py-4 tabular-nums text-testo-soft">{{ $agente->agenzie_count }}</td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('gestione.agenti.edit', $agente) }}"
                                   class="font-sans text-[11px] tracking-[0.14em] uppercase text-oro-scuro hover:underline">
                                    Modifica
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-8">
            {{ $agenti->links() }}
        </div>
    @endif
@endsection
