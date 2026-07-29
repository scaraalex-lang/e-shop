@php
    $p = $prodotto ?? null;
    $vecchio = fn (string $campo, $default = null) => old($campo, $p?->{$campo} ?? $default);
@endphp

<div class="flex justify-end mb-8">
    <x-primary-button>{{ $p ? 'Salva le modifiche' : 'Crea il prodotto' }}</x-primary-button>
</div>

<div class="grid gap-10 lg:grid-cols-[1fr_20rem]">
    <div class="space-y-8">

        <section class="border border-caffe/15 bg-bianco px-7 py-7 space-y-5">
            <h2 class="font-serif text-xl font-medium">Anagrafica</h2>

            <div>
                <x-input-label for="name" value="Nome" />
                <x-text-input id="name" name="name" value="{{ $vecchio('name') }}" required autofocus />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <x-input-label for="sku" value="SKU" />
                    <x-text-input id="sku" name="sku" value="{{ $vecchio('sku') }}" required />
                    <x-input-error :messages="$errors->get('sku')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="slug" value="Slug (nell'indirizzo web)" />
                    <x-text-input id="slug" name="slug" value="{{ $vecchio('slug') }}" required />
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
                        <option value="{{ $c->id }}" @selected((int) $vecchio('category_id') === $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="short_description" value="Descrizione breve (vetrina, elenchi)" />
                <textarea id="short_description" name="short_description" rows="2"
                          class="block w-full bg-bianco border border-caffe/25 px-4 py-3 font-sans font-light text-[15px]
                                 focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40">{{ $vecchio('short_description') }}</textarea>
                <x-input-error :messages="$errors->get('short_description')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="description" value="Descrizione completa (scheda prodotto)" />
                <textarea id="description" name="description" rows="6"
                          class="block w-full bg-bianco border border-caffe/25 px-4 py-3 font-sans font-light text-[15px]
                                 focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40">{{ $vecchio('description') }}</textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <x-input-label for="material" value="Materiale (facoltativo)" />
                    <x-text-input id="material" name="material" value="{{ $vecchio('material') }}" />
                </div>
                <div>
                    <x-input-label for="color" value="Colore (facoltativo)" />
                    <x-text-input id="color" name="color" value="{{ $vecchio('color') }}" />
                </div>
            </div>
        </section>

        <section class="border border-caffe/15 bg-bianco px-7 py-7 space-y-5">
            <h2 class="font-serif text-xl font-medium">Prezzo e magazzino</h2>

            <div class="grid gap-5 sm:grid-cols-3">
                <div>
                    <x-input-label for="price" value="Prezzo (€)" />
                    <x-text-input id="price" name="price"
                                  value="{{ old('price', $p ? \Modules\Catalog\Support\Euro::daCentesimi($p->price) : '') }}"
                                  placeholder="26,00" required />
                    <x-input-error :messages="$errors->get('price')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="compare_at_price" value="Prezzo barrato (facoltativo)" />
                    <x-text-input id="compare_at_price" name="compare_at_price"
                                  value="{{ old('compare_at_price', $p?->compare_at_price ? \Modules\Catalog\Support\Euro::daCentesimi($p->compare_at_price) : '') }}"
                                  placeholder="32,00" />
                    <x-input-error :messages="$errors->get('compare_at_price')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="stock" value="Quantità disponibile" />
                    <x-text-input id="stock" type="number" min="0" name="stock" value="{{ $vecchio('stock', 0) }}" required />
                    <x-input-error :messages="$errors->get('stock')" class="mt-2" />
                </div>
            </div>
        </section>

        <section class="border border-caffe/15 bg-bianco px-7 py-7 space-y-5">
            <h2 class="font-serif text-xl font-medium">Kit a soglia</h2>
            <p class="font-sans font-light text-[13px] text-testo-soft">
                Il prezzo sopra copre già un tot. di pezzi (es. il kit trigesimo): oltre, ogni pezzo si paga a parte.
                Per comporre un kit scegliendo più articoli diversi, usa invece «Kit» nel menu.
            </p>

            <label class="flex items-center gap-3 font-sans text-[14px]">
                <input type="hidden" name="is_kit" value="0">
                <input type="checkbox" name="is_kit" value="1" @checked($vecchio('is_kit'))
                       class="border-caffe/40 text-oro focus:ring-oro/40">
                È un kit a soglia
            </label>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <x-input-label for="included_units" value="Pezzi inclusi nel prezzo base" />
                    <x-text-input id="included_units" type="number" min="0" name="included_units" value="{{ $vecchio('included_units') }}" />
                    <x-input-error :messages="$errors->get('included_units')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="extra_unit_price" value="Prezzo del pezzo extra (€)" />
                    <x-text-input id="extra_unit_price" name="extra_unit_price"
                                  value="{{ old('extra_unit_price', $p?->extra_unit_price ? \Modules\Catalog\Support\Euro::daCentesimi($p->extra_unit_price) : '') }}"
                                  placeholder="1,20" />
                    <x-input-error :messages="$errors->get('extra_unit_price')" class="mt-2" />
                </div>
            </div>
        </section>

        @if ($p)
            <section class="border border-caffe/15 bg-bianco px-7 py-7 space-y-5">
                <h2 class="font-serif text-xl font-medium">Fotografie</h2>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <x-input-label for="foto_catalogo" value="Foto catalogo (copertina)" />
                        @if ($catalogo = $p->images->firstWhere('is_primary', true))
                            <img src="{{ asset('storage/'.$catalogo->path) }}" alt="" class="mb-3 h-32 w-32 object-cover border border-caffe/15">
                        @endif
                        <input id="foto_catalogo" type="file" name="foto_catalogo" accept="image/*"
                               class="block w-full font-sans font-light text-[13px]">
                        <x-input-error :messages="$errors->get('foto_catalogo')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="foto_zoom" value="Foto zoom (dettaglio)" />
                        @if ($zoom = $p->images->firstWhere('is_primary', false))
                            <img src="{{ asset('storage/'.$zoom->path) }}" alt="" class="mb-3 h-32 w-32 object-cover border border-caffe/15">
                        @endif
                        <input id="foto_zoom" type="file" name="foto_zoom" accept="image/*"
                               class="block w-full font-sans font-light text-[13px]">
                        <x-input-error :messages="$errors->get('foto_zoom')" class="mt-2" />
                    </div>
                </div>
            </section>
        @else
            <p class="font-sans font-light text-[13px] text-testo-soft">
                Le fotografie si caricano dopo aver salvato il prodotto la prima volta.
            </p>
        @endif
    </div>

    <aside class="border border-caffe/15 bg-panna/50 px-6 py-7 self-start space-y-5">
        <h2 class="font-serif text-xl font-medium">Visibilità</h2>

        <label class="flex items-center gap-3 font-sans text-[14px]">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked($vecchio('is_active', true))
                   class="border-caffe/40 text-oro focus:ring-oro/40">
            Attivo (visibile in vetrina)
        </label>

        <label class="flex items-center gap-3 font-sans text-[14px]">
            <input type="hidden" name="is_featured" value="0">
            <input type="checkbox" name="is_featured" value="1" @checked($vecchio('is_featured'))
                   class="border-caffe/40 text-oro focus:ring-oro/40">
            In evidenza in home
        </label>

        <hr class="h-px w-full border-0 bg-caffe/15">

        <h2 class="font-serif text-xl font-medium">Flussi collegati</h2>

        <label class="flex items-center gap-3 font-sans text-[14px]">
            <input type="hidden" name="is_photo_printable" value="0">
            <input type="checkbox" name="is_photo_printable" value="1" @checked($vecchio('is_photo_printable'))
                   class="border-caffe/40 text-oro focus:ring-oro/40">
            Personalizzabile con foto
        </label>
        <label class="flex items-center gap-3 font-sans text-[14px]">
            <input type="hidden" name="has_qr_memorial" value="0">
            <input type="checkbox" name="has_qr_memorial" value="1" @checked($vecchio('has_qr_memorial'))
                   class="border-caffe/40 text-oro focus:ring-oro/40">
            Ha il QR memoriale
        </label>
        <label class="flex items-center gap-3 font-sans text-[14px]">
            <input type="hidden" name="is_configurable" value="0">
            <input type="checkbox" name="is_configurable" value="1" @checked($vecchio('is_configurable'))
                   class="border-caffe/40 text-oro focus:ring-oro/40">
            Configurabile
        </label>

        <hr class="h-px w-full border-0 bg-caffe/15">

        <x-primary-button class="w-full">{{ $p ? 'Salva le modifiche' : 'Crea il prodotto' }}</x-primary-button>
    </aside>
</div>
