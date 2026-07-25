@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'mt-2 font-sans text-[12px] text-errore space-y-1']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
