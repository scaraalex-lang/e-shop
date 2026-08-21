@props(['class' => 'w-5 h-5'])
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none"
     stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"
     aria-hidden="true">
    <rect x="3" y="3" width="6" height="6" rx="1"></rect>
    <rect x="15" y="3" width="6" height="6" rx="1"></rect>
    <rect x="3" y="15" width="6" height="6" rx="1"></rect>
    <circle cx="16.4" cy="16.4" r="0.9" fill="currentColor" stroke="none"></circle>
    <circle cx="20" cy="16.4" r="0.9" fill="currentColor" stroke="none"></circle>
    <circle cx="16.4" cy="20" r="0.9" fill="currentColor" stroke="none"></circle>
    <circle cx="20" cy="20" r="0.9" fill="currentColor" stroke="none"></circle>
</svg>
