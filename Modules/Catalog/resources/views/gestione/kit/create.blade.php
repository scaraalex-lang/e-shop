@extends('layouts.gestione')

@section('title', 'Nuovo kit — Gestione MemorAI')
@section('titolo', 'Nuovo kit')

@section('gestione')
    <a href="{{ route('gestione.kit.index') }}"
       class="font-sans text-[11px] tracking-[0.22em] uppercase text-testo-soft hover:text-oro-scuro transition-colors duration-300">
        ← Tutti i kit
    </a>

    <form method="POST" action="{{ route('gestione.kit.store') }}" class="mt-8 max-w-xl space-y-5">
        @csrf

        <div>
            <x-input-label for="name" value="Nome del kit" />
            <x-text-input id="name" name="name" value="{{ old('name') }}" required autofocus />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <x-input-label for="sku" value="SKU" />
                <x-text-input id="sku" name="sku" value="{{ old('sku') }}" required />
                <x-input-error :messages="$errors->get('sku')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="slug" value="Slug (nell'indirizzo web)" />
                <x-text-input id="slug" name="slug" value="{{ old('slug') }}" required />
                <x-input-error :messages="$errors->get('slug')" class="mt-2" />
            </div>
        </div>

        <div>
            <x-input-label for="category_id" value="Categoria" />
            <select id="category_id" name="category_id" required
                    class="block w-full bg-bianco border border-caffe/25 px-4 py-3 font-sans font-light text-[15px]
                           focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40">
                <option value="">Seleziona…</option>
                @foreach ($categorie as $c)
                    <option value="{{ $c->id }}" @selected((int) old('category_id') === $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="short_description" value="Descrizione breve (facoltativa)" />
            <textarea id="short_description" name="short_description" rows="2"
                      class="block w-full bg-bianco border border-caffe/25 px-4 py-3 font-sans font-light text-[15px]
                             focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40">{{ old('short_description') }}</textarea>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <x-input-label for="price" value="Prezzo del kit (€)" />
                <x-text-input id="price" name="price" value="{{ old('price') }}" placeholder="79,00" required />
                <p class="mt-2 font-sans font-light text-[12px] text-testo-soft">
                    Deciso a mano, tipicamente più conveniente della somma dei singoli articoli.
                </p>
                <x-input-error :messages="$errors->get('price')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="stock" value="Quantità disponibile" />
                <x-text-input id="stock" type="number" min="0" name="stock" value="{{ old('stock', 0) }}" required />
                <x-input-error :messages="$errors->get('stock')" class="mt-2" />
            </div>
        </div>

        <x-primary-button>Crea il kit e componilo</x-primary-button>
    </form>
@endsection
