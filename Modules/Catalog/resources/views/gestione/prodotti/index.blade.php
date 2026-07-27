@extends('layouts.gestione')

@section('title', 'Prodotti — Gestione MemorAI')
@section('titolo', 'Prodotti')

@section('gestione')
    <div class="flex flex-wrap items-end justify-between gap-6">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <x-input-label for="q" value="Cerca" />
                <x-text-input id="q" name="q" value="{{ $ricerca }}" placeholder="Nome o SKU" class="w-56" />
            </div>
            <div>
                <x-input-label for="categoria" value="Categoria" />
                <select id="categoria" name="categoria"
                        class="block w-56 bg-bianco border border-caffe/25 px-4 py-3 font-sans font-light text-[14px]
                               focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40">
                    <option value="">Tutte</option>
                    @foreach ($categorie as $c)
                        <option value="{{ $c->id }}" @selected($categoriaAttiva === $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="stato" value="Stato" />
                <select id="stato" name="stato"
                        class="block w-40 bg-bianco border border-caffe/25 px-4 py-3 font-sans font-light text-[14px]
                               focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40">
                    <option value="">Tutti</option>
                    <option value="attivi" @selected($statoAttivo === 'attivi')>Attivi</option>
                    <option value="disattivi" @selected($statoAttivo === 'disattivi')>Disattivi</option>
                </select>
            </div>
            <x-secondary-button type="submit">Filtra</x-secondary-button>
        </form>

        <a href="{{ route('gestione.prodotti.create') }}"
           class="inline-flex items-center justify-center gap-2 cursor-pointer select-none
                  font-sans uppercase text-[12px] tracking-[0.22em] px-8 py-3.5
                  bg-oro text-bianco hover:bg-oro-scuro transition-all duration-300 ease-out">
            Nuovo prodotto
        </a>
    </div>

    @if ($prodotti->isEmpty())
        <p class="mt-10 font-sans font-light text-[15px] text-testo-soft">
            Nessun prodotto in questo elenco.
        </p>
    @else
        <div class="mt-8 overflow-x-auto border border-caffe/15">
            <table class="w-full min-w-[52rem] border-collapse">
                <thead>
                    <tr class="bg-panna text-left font-sans text-[10px] tracking-[0.22em] uppercase text-testo-soft">
                        <th class="px-5 py-4 font-normal">Prodotto</th>
                        <th class="px-5 py-4 font-normal">SKU</th>
                        <th class="px-5 py-4 font-normal">Categoria</th>
                        <th class="px-5 py-4 font-normal">Prezzo</th>
                        <th class="px-5 py-4 font-normal">Scorta</th>
                        <th class="px-5 py-4 font-normal">Stato</th>
                    </tr>
                </thead>
                <tbody class="font-sans font-light text-[14px]">
                    @foreach ($prodotti as $prodotto)
                        <tr class="border-t border-caffe/10 hover:bg-panna/50 transition-colors duration-200">
                            <td class="px-5 py-4">
                                <a href="{{ route('gestione.prodotti.edit', $prodotto) }}"
                                   class="font-serif text-[17px] text-testo hover:text-oro-scuro transition-colors duration-300">
                                    {{ $prodotto->name }}
                                </a>
                                @if ($prodotto->is_kit)
                                    <span class="ml-2 font-sans text-[9px] tracking-[0.16em] uppercase text-oro-scuro">Kit a soglia</span>
                                @endif
                                @if ($prodotto->is_componibile)
                                    <span class="ml-2 font-sans text-[9px] tracking-[0.16em] uppercase text-oro-scuro">Kit componibile</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 tabular-nums text-testo-soft">{{ $prodotto->sku }}</td>
                            <td class="px-5 py-4 text-testo-soft">{{ $prodotto->category?->name ?? '—' }}</td>
                            <td class="px-5 py-4"><x-prezzo :centesimi="$prodotto->price" /></td>
                            <td class="px-5 py-4 tabular-nums {{ $prodotto->stock <= 0 ? 'text-errore' : 'text-testo-soft' }}">
                                {{ $prodotto->stock }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-sans text-[10px] tracking-[0.18em] uppercase {{ $prodotto->is_active ? 'text-successo' : 'text-errore' }}">
                                    {{ $prodotto->is_active ? 'Attivo' : 'Disattivo' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-8">
            {{ $prodotti->links() }}
        </div>
    @endif
@endsection
