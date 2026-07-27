@php $c = $categoria ?? null; @endphp

<div>
    <x-input-label for="name" value="Nome" />
    <x-text-input id="name" name="name" value="{{ old('name', $c?->name) }}" required autofocus />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div class="grid gap-6 sm:grid-cols-2">
    <div>
        <x-input-label for="slug" value="Slug (nell'indirizzo web)" />
        <x-text-input id="slug" name="slug" value="{{ old('slug', $c?->slug) }}" required />
        <x-input-error :messages="$errors->get('slug')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="parent_id" value="Categoria padre (facoltativa)" />
        <select id="parent_id" name="parent_id"
                class="block w-full bg-bianco border border-caffe/25 px-4 py-3 font-sans font-light text-[15px]
                       focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40">
            <option value="">Nessuna (categoria radice)</option>
            @foreach ($categorieRadice as $radice)
                <option value="{{ $radice->id }}" @selected((int) old('parent_id', $c?->parent_id) === $radice->id)>{{ $radice->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('parent_id')" class="mt-2" />
    </div>
</div>

<div>
    <x-input-label for="description" value="Descrizione (facoltativa)" />
    <textarea id="description" name="description" rows="3"
              class="block w-full bg-bianco border border-caffe/25 px-4 py-3 font-sans font-light text-[15px]
                     focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40">{{ old('description', $c?->description) }}</textarea>
    <x-input-error :messages="$errors->get('description')" class="mt-2" />
</div>

<div class="grid gap-6 sm:grid-cols-2 items-end">
    <div>
        <x-input-label for="sort_order" value="Ordine" />
        <x-text-input id="sort_order" type="number" min="0" name="sort_order" value="{{ old('sort_order', $c?->sort_order ?? 0) }}" required />
        <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
    </div>
    <label class="flex items-center gap-3 font-sans text-[14px] pb-3">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $c?->is_active ?? true))
               class="border-caffe/40 text-oro focus:ring-oro/40">
        Attiva (visibile in vetrina)
    </label>
</div>

<div>
    <x-input-label for="immagine" value="Immagine di copertina" />
    @if ($c?->image)
        <img src="{{ asset('storage/'.$c->image) }}" alt="" class="mb-3 h-32 w-32 object-cover border border-caffe/15">
    @endif
    <input id="immagine" type="file" name="immagine" accept="image/*" class="block w-full font-sans font-light text-[13px]">
    <x-input-error :messages="$errors->get('immagine')" class="mt-2" />
</div>

<x-primary-button>{{ $c ? 'Salva le modifiche' : 'Crea la categoria' }}</x-primary-button>
