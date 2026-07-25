{{-- Stessa lingua visiva di <x-button variant="piena"> --}}
<button {{ $attributes->merge([
    'type' => 'submit',
    'class' => 'inline-flex items-center justify-center gap-2 cursor-pointer select-none '
             . 'font-sans uppercase text-[12px] tracking-[0.22em] px-8 py-3.5 '
             . 'bg-oro text-bianco hover:bg-oro-scuro '
             . 'transition-all duration-300 ease-out disabled:opacity-40 disabled:cursor-not-allowed',
]) }}>
    {{ $slot }}
</button>
