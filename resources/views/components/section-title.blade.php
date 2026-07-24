@props([
    'occhiello' => null,   // piccolo testo in oro sopra il titolo
    'titolo' => '',        // titolo serif
    'allineamento' => 'center',   // center | left
])

@php
    $align = $allineamento === 'left' ? 'text-left items-start' : 'text-center items-center';
@endphp

<div {{ $attributes->merge(['class' => "flex flex-col $align gap-4 my-2"]) }}>
    @if ($occhiello)
        <span class="font-sans text-[11px] tracking-[0.35em] uppercase text-oro-scuro">
            {{ $occhiello }}
        </span>
    @endif

    <h2 class="font-serif font-medium text-testo text-3xl md:text-4xl leading-tight">
        {{ $titolo ?: $slot }}
    </h2>

    {{-- separatore: semplice linea sottile in oro --}}
    <span class="block h-px w-16 bg-oro"></span>
</div>
