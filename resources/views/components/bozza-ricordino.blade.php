@props([
    'ricordino',            // Modules\Memorial\Models\Ricordino
    'larghezza' => 'w-44',  // larghezza di ogni facciata
])

{{--
    Le due facciate del ricordino, affiancate.
    Fronte e retro vanno mostrati insieme: il retro porta la preghiera, ed è
    metà del lavoro che il cliente deve approvare.
--}}
@php
    $facciate = array_filter([
        'Fronte' => $ricordino->anteprima_fronte,
        'Retro' => $ricordino->anteprima_retro,
    ]);
@endphp

@if ($facciate)
    <div {{ $attributes->merge(['class' => 'flex flex-wrap gap-5']) }}>
        @foreach ($facciate as $lato => $percorso)
            <figure class="{{ $larghezza }}">
                <img src="{{ asset('storage/'.$percorso) }}"
                     alt="Ricordino, {{ strtolower($lato) }}"
                     class="w-full border border-caffe/25 bg-bianco">
                <figcaption class="mt-2 text-center font-sans text-[10px] tracking-[0.2em] uppercase text-testo-soft">
                    {{ $lato }}
                </figcaption>
            </figure>
        @endforeach
    </div>
@else
    <p {{ $attributes->merge(['class' => 'font-sans font-light text-[13px] text-testo-soft']) }}>
        L'anteprima compare dopo il primo salvataggio nel Designer.
    </p>
@endif
