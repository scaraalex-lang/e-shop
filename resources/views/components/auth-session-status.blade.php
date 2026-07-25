@props(['status'])

@if ($status)
    <div {{ $attributes->merge([
        'class' => 'border-l-2 border-successo bg-panna px-4 py-3 font-sans text-[13px] text-testo',
    ]) }}>
        {{ $status }}
    </div>
@endif
