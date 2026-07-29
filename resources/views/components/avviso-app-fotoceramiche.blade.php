{{--
    Avviso "c'è un'app dedicata" per le photoceramiche — categoria e schede
    prodotto. Contenuto governato da /gestione (Contenuti home): se il testo
    è vuoto non si mostra nulla.
--}}
@php
    $testo = \App\Models\ContenutoVetrina::valore('photoceramiche.avviso_testo');
@endphp

@if (filled($testo))
    <div class="flex flex-wrap items-center gap-6 justify-between border-2 border-oro bg-panna px-7 py-6">
        <div class="max-w-2xl">
            @if ($titolo = \App\Models\ContenutoVetrina::valore('photoceramiche.avviso_titolo'))
                <h3 class="font-serif text-xl font-medium text-caffe">{{ $titolo }}</h3>
            @endif
            <p class="mt-1.5 font-sans font-light text-[14px] leading-relaxed text-testo-soft">
                {{ $testo }}
            </p>
        </div>

        @if ($url = \App\Models\ContenutoVetrina::valore('photoceramiche.avviso_url'))
            <x-button variant="piena" :href="$url" target="_blank" rel="noopener noreferrer" class="shrink-0">
                {{ \App\Models\ContenutoVetrina::valore('photoceramiche.avviso_bottone', "Scopri l'app") }}
            </x-button>
        @endif
    </div>
@endif
