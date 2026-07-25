@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge([
    'class' => 'block w-full bg-bianco border border-caffe/25 px-4 py-3 '
             . 'font-sans font-light text-[15px] text-testo placeholder:text-testo-soft/50 '
             . 'transition-colors duration-300 '
             . 'focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40 '
             . 'disabled:bg-panna disabled:text-testo-soft',
]) }}>
