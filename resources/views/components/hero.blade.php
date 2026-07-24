@props([
    'occhiello' => null,       // piccolo testo in oro sopra il titolo
    'sottotitolo' => null,     // paragrafo Jost testo secondario
    'immagine' => null,        // url immagine prodotto (opzionale)
    'immagineAlt' => 'MemorAI',
    'primario' => null,        // etichetta bottone primario (oro pieno)
    'primarioHref' => '#',
    'secondario' => null,      // etichetta bottone secondario (bordo caffè)
    'secondarioHref' => '#',
])

{{-- Hero editoriale: fondo bianco caldo dominante, titolo serif caffè (con una
     parola in corsivo oro passata via slot), immagine in cornice marrone 2px. --}}
<section {{ $attributes->merge(['class' => 'bg-bianco']) }}>
    <div class="mx-auto max-w-7xl px-6 py-16 md:py-24
                grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">

        {{-- testo --}}
        <div class="max-w-xl">
            @if ($occhiello)
                <span class="font-sans text-[11px] tracking-[0.35em] uppercase text-oro-scuro">
                    {{ $occhiello }}
                </span>
            @endif

            <h1 class="mt-6 font-serif font-medium text-caffe leading-[1.05] text-5xl md:text-6xl">
                {{ $slot }}
            </h1>

            @if ($sottotitolo)
                <p class="mt-6 font-sans font-light text-testo-soft text-lg leading-relaxed">
                    {{ $sottotitolo }}
                </p>
            @endif

            @if ($primario || $secondario)
                <div class="mt-9 flex flex-wrap gap-4">
                    @if ($primario)
                        <x-button variant="piena" :href="$primarioHref">{{ $primario }}</x-button>
                    @endif
                    @if ($secondario)
                        <x-button variant="contornata" :href="$secondarioHref">{{ $secondario }}</x-button>
                    @endif
                </div>
            @endif
        </div>

        {{-- immagine prodotto dentro cornice marrone 2px --}}
        <div class="lg:justify-self-end w-full max-w-md">
            <figure class="border-2 border-caffe bg-panna aspect-[4/5] overflow-hidden">
                @if ($immagine)
                    <img src="{{ $immagine }}" alt="{{ $immagineAlt }}"
                         class="h-full w-full object-cover">
                @else
                    <div class="h-full w-full flex items-center justify-center">
                        <span class="font-serif text-3xl text-caffe/60">MemorAI</span>
                    </div>
                @endif
            </figure>
        </div>
    </div>
</section>
