@props(['value' => null])

<label {{ $attributes->merge([
    'class' => 'block font-sans text-[11px] tracking-[0.2em] uppercase text-testo-soft mb-2',
]) }}>
    {{ $value ?? $slot }}
</label>
