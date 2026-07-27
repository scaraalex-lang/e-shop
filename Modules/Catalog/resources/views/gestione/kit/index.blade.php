@extends('layouts.gestione')

@section('title', 'Kit componibili — Gestione MemorAI')
@section('titolo', 'Kit componibili')

@section('gestione')
    <div class="flex flex-wrap items-center justify-between gap-6">
        <p class="font-sans font-light text-[14px] text-testo-soft max-w-md">
            Un kit componibile è un prodotto venduto come gli altri, ma composto scegliendo più articoli dal magazzino.
        </p>
        <a href="{{ route('gestione.kit.create') }}"
           class="inline-flex items-center justify-center gap-2 cursor-pointer select-none
                  font-sans uppercase text-[12px] tracking-[0.22em] px-8 py-3.5
                  bg-oro text-bianco hover:bg-oro-scuro transition-all duration-300 ease-out">
            Nuovo kit
        </a>
    </div>

    @if ($kit->isEmpty())
        <p class="mt-10 font-sans font-light text-[15px] text-testo-soft">
            Nessun kit componibile ancora.
        </p>
    @else
        <div class="mt-8 overflow-x-auto border border-caffe/15">
            <table class="w-full min-w-[46rem] border-collapse">
                <thead>
                    <tr class="bg-panna text-left font-sans text-[10px] tracking-[0.22em] uppercase text-testo-soft">
                        <th class="px-5 py-4 font-normal">Kit</th>
                        <th class="px-5 py-4 font-normal">Prezzo</th>
                        <th class="px-5 py-4 font-normal">Contiene</th>
                        <th class="px-5 py-4 font-normal">Stato</th>
                    </tr>
                </thead>
                <tbody class="font-sans font-light text-[14px]">
                    @foreach ($kit as $k)
                        <tr class="border-t border-caffe/10 hover:bg-panna/50 transition-colors duration-200">
                            <td class="px-5 py-4">
                                <a href="{{ route('gestione.kit.show', $k) }}"
                                   class="font-serif text-[17px] text-testo hover:text-oro-scuro transition-colors duration-300">
                                    {{ $k->name }}
                                </a>
                            </td>
                            <td class="px-5 py-4"><x-prezzo :centesimi="$k->price" /></td>
                            <td class="px-5 py-4 text-testo-soft">
                                @if ($k->componenti->isEmpty())
                                    <span class="text-errore">vuoto</span>
                                @else
                                    {{ $k->componenti->count() }} articoli
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-sans text-[10px] tracking-[0.18em] uppercase {{ $k->is_active ? 'text-successo' : 'text-errore' }}">
                                    {{ $k->is_active ? 'Attivo' : 'Disattivo' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-8">
            {{ $kit->links() }}
        </div>
    @endif
@endsection
