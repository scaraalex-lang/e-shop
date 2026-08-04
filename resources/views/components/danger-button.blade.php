@props(['compatta' => false])

<button {{ $attributes->merge([
    'type' => 'submit',
    'class' => 'inline-flex items-center justify-center gap-2 cursor-pointer select-none '
             . 'font-sans uppercase '
             . ($compatta ? 'text-[11px] tracking-[0.15em] px-4 py-2 ' : 'text-[12px] tracking-[0.22em] px-8 py-3.5 ')
             . 'bg-transparent border-2 border-errore text-errore hover:bg-errore hover:text-bianco '
             . 'transition-all duration-300 ease-out',
]) }}>
    {{ $slot }}
</button>
