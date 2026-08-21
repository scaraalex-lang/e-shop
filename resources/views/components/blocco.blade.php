@props([
    // secondario (neutro, invariato) | chiave (Gestione: inversione caffè)
    // | tono-successo | tono-attenzione | tono-neutro (B2B/B2C: tinte funzionali)
    'variant' => 'secondario',
    'etichetta' => null,
    'valore' => null,
])

{{--
    Blocco di contrasto per il pannello di lavoro centrale (Gestione/B2B/B2C).
    Due famiglie di variant, decise a schermo con l'utente su una proposta a 3
    opzioni: "chiave" (Opzione B, caffè profondo) per la Gestione staff,
    "tono-*" (Opzione C, tinte funzionali salvia/terracotta/oro morbide) per
    l'account B2B/B2C. "secondario" resta il trattamento neutro già in uso.
--}}
@php
    $stili = [
        'secondario' => ['bg-panna/40 border border-caffe/15', 'text-testo-soft', 'text-testo'],
        'chiave' => ['bg-caffe', 'text-oro', 'text-panna'],
        'tono-successo' => ['bg-successo/10 border border-successo/35', 'text-successo', 'text-testo'],
        'tono-attenzione' => ['bg-errore/10 border border-errore/35', 'text-errore', 'text-testo'],
        'tono-neutro' => ['bg-oro/15 border border-oro-scuro/40', 'text-oro-scuro', 'text-testo'],
    ];
    [$sfondo, $coloreEtichetta, $coloreTesto] = $stili[$variant] ?? $stili['secondario'];

    // h1-h4 hanno un colore fisso in resources/css/app.css @layer base:
    // l'eredità del colore del contenitore non li raggiunge (una regola
    // diretta, per quanto poco specifica, batte sempre l'ereditarietà). La
    // classe .blocco-chiave forza il reset sulle intestazioni dentro un
    // blocco scuro — vedi la regola gemella in app.css.
    $classeReset = $variant === 'chiave' ? 'blocco-chiave' : '';
@endphp

<div {{ $attributes->merge(['class' => "$sfondo $coloreTesto $classeReset px-5 py-4"]) }}>
    @if ($etichetta)
        <span class="block font-sans text-[10px] tracking-[0.2em] uppercase {{ $coloreEtichetta }}">
            {{ $etichetta }}
        </span>
    @endif
    @if ($valore !== null)
        <span class="block font-serif text-[28px] leading-tight mt-2 tabular-nums">{{ $valore }}</span>
    @endif
    {{ $slot }}
</div>
