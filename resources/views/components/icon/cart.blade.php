@props(['class' => 'w-5 h-5'])
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none"
     stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"
     aria-hidden="true">
    <path d="M3 4h2l2.2 11.2a1.5 1.5 0 0 0 1.5 1.2h8.6a1.5 1.5 0 0 0 1.5-1.2L21 7H6"></path>
    <circle cx="9.5" cy="20" r="1.2"></circle>
    <circle cx="17.5" cy="20" r="1.2"></circle>
</svg>
