@props([
    'variant' => 'piena',   // piena (primario) | contornata (secondario)
    'href' => null,
    'type' => 'button',
])

@php
    $base = 'inline-flex items-center justify-center gap-2 font-sans uppercase '
          . 'text-[12px] tracking-[0.22em] px-8 py-3.5 '
          . 'transition-all duration-300 ease-out cursor-pointer select-none';

    $stili = [
        // primario: oro pieno, testo bianco caldo, nessun bordo
        'piena'      => 'bg-oro text-bianco hover:bg-oro-scuro',
        // secondario: trasparente con bordo marrone 2px; all'hover si riempie di marrone
        'contornata' => 'bg-transparent border-2 border-caffe text-caffe hover:bg-caffe hover:text-bianco',
    ];

    $classi = $base . ' ' . ($stili[$variant] ?? $stili['piena']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classi]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classi]) }}>
        {{ $slot }}
    </button>
@endif
