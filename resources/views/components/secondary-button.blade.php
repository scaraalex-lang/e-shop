{{-- Stessa lingua visiva di <x-button variant="contornata"> --}}
<button {{ $attributes->merge([
    'type' => 'button',
    'class' => 'inline-flex items-center justify-center gap-2 cursor-pointer select-none '
             . 'font-sans uppercase text-[12px] tracking-[0.22em] px-8 py-3.5 '
             . 'bg-transparent border-2 border-caffe text-caffe hover:bg-caffe hover:text-bianco '
             . 'transition-all duration-300 ease-out',
]) }}>
    {{ $slot }}
</button>
