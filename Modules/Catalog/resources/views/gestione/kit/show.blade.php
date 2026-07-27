@extends('layouts.gestione')

@section('title', $kit->name.' — Gestione MemorAI')
@section('titolo', $kit->name)

@section('gestione')
    <a href="{{ route('gestione.kit.index') }}"
       class="font-sans text-[11px] tracking-[0.22em] uppercase text-testo-soft hover:text-oro-scuro transition-colors duration-300">
        ← Tutti i kit
    </a>

    <div class="mt-8 grid gap-10 lg:grid-cols-[1fr_22rem]">

        <div class="border border-caffe/15 bg-bianco px-7 py-7 self-start">
            <h2 class="font-serif text-xl font-medium">Cosa contiene</h2>

            @if ($kit->componenti->isEmpty())
                <p class="mt-4 font-sans font-light text-[14px] text-testo-soft">
                    Ancora nessun articolo: il kit non è acquistabile finché resta vuoto.
                </p>
            @else
                <ul class="mt-5 divide-y divide-caffe/10">
                    @foreach ($kit->componenti as $riga)
                        <li class="flex items-center justify-between gap-4 py-3">
                            <div>
                                <p class="font-sans text-[14px]">{{ $riga->componente->name }}</p>
                                <p class="font-sans text-[12px] text-testo-soft">{{ $riga->quantita }} pezzo{{ $riga->quantita > 1 ? 'i' : '' }}</p>
                            </div>
                            <form method="POST" action="{{ route('gestione.kit.componenti.destroy', [$kit, $riga]) }}"
                                  onsubmit="return confirm('Togliere questo articolo dal kit?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="font-sans text-[11px] tracking-[0.14em] uppercase text-errore hover:underline">
                                    Togli
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <aside class="border border-caffe/15 bg-panna/50 px-6 py-7 self-start">
            <h2 class="font-serif text-xl font-medium">Aggiungi un articolo</h2>

            <form method="POST" action="{{ route('gestione.kit.componenti.store', $kit) }}" class="mt-5 space-y-4">
                @csrf
                <div>
                    <x-input-label for="componente_product_id" value="Articolo" />
                    <select id="componente_product_id" name="componente_product_id" required
                            class="block w-full bg-bianco border border-caffe/25 px-4 py-3 font-sans font-light text-[14px]
                                   focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40">
                        <option value="">Seleziona…</option>
                        @foreach ($articoli as $articolo)
                            <option value="{{ $articolo->id }}">{{ $articolo->name }} ({{ $articolo->sku }})</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('componente_product_id')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="quantita" value="Quantità" />
                    <x-text-input id="quantita" type="number" min="1" name="quantita" value="1" required />
                    <x-input-error :messages="$errors->get('quantita')" class="mt-2" />
                </div>
                <x-primary-button class="w-full">Aggiungi al kit</x-primary-button>
            </form>
        </aside>
    </div>
@endsection
