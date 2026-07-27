@php $a = $agente ?? null; @endphp

<div>
    <x-input-label for="nome" value="Nome" />
    <x-text-input id="nome" name="nome" value="{{ old('nome', $a?->nome) }}" required autofocus />
    <x-input-error :messages="$errors->get('nome')" class="mt-2" />
</div>

<div class="grid gap-6 sm:grid-cols-2">
    <div>
        <x-input-label for="email" value="Email (facoltativa)" />
        <x-text-input id="email" type="email" name="email" value="{{ old('email', $a?->email) }}" />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="telefono" value="Telefono (facoltativo)" />
        <x-text-input id="telefono" name="telefono" value="{{ old('telefono', $a?->telefono) }}" />
        <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
    </div>
</div>

<div>
    <x-input-label for="note" value="Note (facoltative)" />
    <textarea id="note" name="note" rows="3"
              class="block w-full bg-bianco border border-caffe/25 px-4 py-3 font-sans font-light text-[15px]
                     focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40">{{ old('note', $a?->note) }}</textarea>
    <x-input-error :messages="$errors->get('note')" class="mt-2" />
</div>

<x-primary-button>{{ $a ? 'Salva le modifiche' : 'Crea l\'agente' }}</x-primary-button>
