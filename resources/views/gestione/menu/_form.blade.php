@php $v = $voce ?? null; @endphp

<div>
    <x-input-label for="zona" value="Zona" />
    <select id="zona" name="zona" required
            class="block w-full bg-bianco border border-caffe/25 px-4 py-3 font-sans font-light text-[15px]
                   focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40">
        @foreach ($zone as $zona)
            <option value="{{ $zona->value }}" @selected(old('zona', $v?->zona?->value) === $zona->value)>{{ $zona->etichetta() }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('zona')" class="mt-2" />
</div>

<div>
    <x-input-label for="etichetta" value="Etichetta" />
    <x-text-input id="etichetta" name="etichetta" value="{{ old('etichetta', $v?->etichetta) }}" required autofocus />
    <x-input-error :messages="$errors->get('etichetta')" class="mt-2" />
</div>

<div>
    <x-input-label for="url" value="Indirizzo (es. /categoria/rosari, oppure #)" />
    <x-text-input id="url" name="url" value="{{ old('url', $v?->url) }}" required />
    <x-input-error :messages="$errors->get('url')" class="mt-2" />
</div>

<div class="grid gap-6 sm:grid-cols-2 items-end">
    <div>
        <x-input-label for="sort_order" value="Ordine" />
        <x-text-input id="sort_order" type="number" min="0" name="sort_order" value="{{ old('sort_order', $v?->sort_order ?? 0) }}" required />
        <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
    </div>
    <label class="flex items-center gap-3 font-sans text-[14px] pb-3">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $v?->is_active ?? true))
               class="border-caffe/40 text-oro focus:ring-oro/40">
        Attiva
    </label>
</div>

<x-primary-button>{{ $v ? 'Salva le modifiche' : 'Crea la voce' }}</x-primary-button>
