@props([
    'categoria',        // istanza di Modules\Catalog\Models\Category
    'href' => null,     // override opzionale del link
])

@php
    // Copertina letta da storage/app/public/categories/ tramite Category->image.
    // Fallback pulito se il campo è vuoto o il file non esiste su disco.
    $img     = $categoria->image;
    $hasImg  = $img && is_file(storage_path('app/public/' . $img));
    $src     = $hasImg ? asset('storage/' . $img) : null;
    $link    = $href ?? url('/categoria/' . $categoria->slug);
    $desc    = $categoria->description;
@endphp

<a href="{{ $link }}" {{ $attributes->merge(['class' => 'group block text-center']) }}>
    {{-- copertina quadrata dentro cornice marrone 2px, con zoom morbido --}}
    <figure class="relative overflow-hidden border-2 border-caffe bg-panna aspect-square">
        @if ($src)
            <img src="{{ $src }}" alt="{{ $categoria->name }}"
                 class="h-full w-full object-cover
                        transition-transform duration-[900ms] ease-[cubic-bezier(0.22,1,0.36,1)]
                        group-hover:scale-[1.1]">
        @else
            {{-- fallback: nome centrato in marrone su fondo panna (mai immagine rotta) --}}
            <div class="h-full w-full flex items-center justify-center p-6 text-center
                        transition-transform duration-[900ms] ease-[cubic-bezier(0.22,1,0.36,1)]
                        group-hover:scale-[1.1]">
                <span class="font-serif text-2xl md:text-3xl text-caffe">{{ $categoria->name }}</span>
            </div>
        @endif
    </figure>

    {{-- nome + descrizione --}}
    <h3 class="mt-5 font-serif text-2xl text-testo leading-snug
               group-hover:text-oro-scuro transition-colors duration-300">
        {{ $categoria->name }}
    </h3>

    @if ($desc)
        <p class="mt-2 font-sans font-light text-[14px] text-testo-soft leading-relaxed">
            {{ \Illuminate\Support\Str::limit($desc, 110) }}
        </p>
    @endif
</a>
