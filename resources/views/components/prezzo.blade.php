@props([
    'centesimi' => 0,      // importo in centesimi (intero): mai float
    'barrato' => false,    // resa da "prezzo di prima", per gli sconti
])

@php
    $testo = number_format(((int) $centesimi) / 100, 2, ',', '.').' €';
@endphp

{{-- Nessuna riga a capo dopo lo span: il componente sta dentro una frase e
     un a capo diventerebbe uno spazio prima della punteggiatura. --}}
<span {{ $attributes->merge([
    'class' => 'tabular-nums'.($barrato ? ' line-through text-testo-soft/70' : ''),
]) }}>{{ $testo }}</span>