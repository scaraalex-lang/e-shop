@props([
    'nome' => 'Prodotto',
    'categoria' => null,
    'prezzo' => null,        // prezzo in centesimi (intero)
    'href' => '#',
    'immagine' => null,      // url immagine, opzionale
    'badge' => null,         // etichetta d'angolo, es. "Con QR Memoria"
])

@php
    $prezzoFmt = is_null($prezzo) ? null : number_format($prezzo / 100, 2, ',', '.') . ' €';
@endphp

<article class="group flex flex-col">
    <a href="{{ $href }}" class="block">
        {{-- immagine dentro cornice marrone 2px, con zoom morbido --}}
        <figure class="relative overflow-hidden border-2 border-caffe bg-panna aspect-[4/5]">
            @if ($badge)
                <span class="absolute top-3 left-3 z-10 font-sans text-[10px] tracking-[0.2em] uppercase
                             bg-bianco text-caffe px-3 py-1.5 border-2 border-caffe">
                    {{ $badge }}
                </span>
            @endif

            @if ($immagine)
                <img src="{{ $immagine }}" alt="{{ $nome }}"
                     class="h-full w-full object-cover transition-transform duration-700 ease-[cubic-bezier(0.22,1,0.36,1)]
                            group-hover:scale-[1.1]">
            @else
                {{-- fallback pulito: nome su fondo panna --}}
                <div class="h-full w-full flex items-center justify-center p-6 text-center
                            transition-transform duration-700 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-[1.1]">
                    <span class="font-serif text-2xl text-caffe/70">{{ $nome }}</span>
                </div>
            @endif
        </figure>
    </a>

    {{-- testo --}}
    <div class="mt-5 text-center">
        @if ($categoria)
            <span class="font-sans text-[10px] tracking-[0.28em] uppercase text-testo-soft">
                {{ $categoria }}
            </span>
        @endif

        <h3 class="mt-2 font-serif text-xl md:text-2xl text-testo leading-snug">
            <a href="{{ $href }}" class="hover:text-oro-scuro transition-colors duration-300">
                {{ $nome }}
            </a>
        </h3>

        @if ($prezzoFmt)
            <p class="mt-2 font-sans text-[15px] text-testo-soft">{{ $prezzoFmt }}</p>
        @endif
    </div>
</article>
