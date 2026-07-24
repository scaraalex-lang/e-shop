@props([
    'colore' => 'oro',   // oro | caffe
    'larghezza' => 'piena',   // piena | corta
])

@php
    $bg = $colore === 'caffe' ? 'bg-caffe' : 'bg-oro';
    $w  = $larghezza === 'corta' ? 'w-16' : 'w-full';
@endphp

<hr {{ $attributes->merge(['class' => "block h-px border-0 $bg $w", 'role' => 'presentation']) }}>
