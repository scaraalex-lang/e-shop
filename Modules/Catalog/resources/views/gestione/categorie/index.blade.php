@extends('layouts.gestione')

@section('title', 'Categorie — Gestione MemorAI')
@section('titolo', 'Categorie')

@section('gestione')
    <div class="flex justify-end">
        <a href="{{ route('gestione.categorie.create') }}"
           class="inline-flex items-center justify-center gap-2 cursor-pointer select-none
                  font-sans uppercase text-[12px] tracking-[0.22em] px-8 py-3.5
                  bg-oro text-bianco hover:bg-oro-scuro transition-all duration-300 ease-out">
            Nuova categoria
        </a>
    </div>

    @if ($categorie->isEmpty())
        <p class="mt-10 font-sans font-light text-[15px] text-testo-soft">
            Nessuna categoria ancora.
        </p>
    @else
        <div class="mt-8 overflow-x-auto border border-caffe/15">
            <table class="w-full min-w-[42rem] border-collapse">
                <thead>
                    <tr class="bg-panna text-left font-sans text-[10px] tracking-[0.22em] uppercase text-testo-soft">
                        <th class="px-5 py-4 font-normal">Nome</th>
                        <th class="px-5 py-4 font-normal">Slug</th>
                        <th class="px-5 py-4 font-normal">Categoria padre</th>
                        <th class="px-5 py-4 font-normal">Stato</th>
                    </tr>
                </thead>
                <tbody class="font-sans font-light text-[14px]">
                    @foreach ($categorie as $categoria)
                        <tr class="border-t border-caffe/10 hover:bg-panna/50 transition-colors duration-200">
                            <td class="px-5 py-4">
                                <a href="{{ route('gestione.categorie.edit', $categoria) }}"
                                   class="font-serif text-[17px] text-testo hover:text-oro-scuro transition-colors duration-300">
                                    {{ $categoria->name }}
                                </a>
                            </td>
                            <td class="px-5 py-4 tabular-nums text-testo-soft">{{ $categoria->slug }}</td>
                            <td class="px-5 py-4 text-testo-soft">{{ $categoria->parent?->name ?? '—' }}</td>
                            <td class="px-5 py-4">
                                <span class="font-sans text-[10px] tracking-[0.18em] uppercase {{ $categoria->is_active ? 'text-successo' : 'text-errore' }}">
                                    {{ $categoria->is_active ? 'Attiva' : 'Disattiva' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-8">
            {{ $categorie->links() }}
        </div>
    @endif
@endsection
