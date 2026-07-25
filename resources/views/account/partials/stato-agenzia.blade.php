@php
    use Modules\Commerce\Enums\StatoAgenzia;

    // Ogni stato ha il suo colore di bordo e il suo testo: l'agenzia deve
    // capire a colpo d'occhio se può già lavorare con le condizioni B2B.
    $stile = match ($agenzia->stato) {
        StatoAgenzia::Approvata => ['bordo' => 'border-successo', 'fondo' => 'bg-panna/60'],
        StatoAgenzia::Rifiutata => ['bordo' => 'border-errore',   'fondo' => 'bg-panna/60'],
        StatoAgenzia::Sospesa   => ['bordo' => 'border-errore',   'fondo' => 'bg-panna/60'],
        default                 => ['bordo' => 'border-oro',      'fondo' => 'bg-panna/60'],
    };

    $testo = match ($agenzia->stato) {
        StatoAgenzia::Approvata => 'Il tuo account agenzia è attivo: listino riservato, sconti a quantità e minimo d\'ordine B2B sono applicati automaticamente.',
        StatoAgenzia::Rifiutata => 'La richiesta non è stata approvata. Puoi scriverci per chiarire: se qualcosa non tornava, si rimedia.',
        StatoAgenzia::Sospesa   => 'Le condizioni riservate sono momentaneamente sospese. Contattaci per riattivarle.',
        default                 => 'Stiamo verificando i dati della tua agenzia. Nel frattempo puoi usare il tuo account normalmente: le condizioni riservate si attivano appena la richiesta è approvata.',
    };
@endphp

<section class="mb-10 border-l-2 {{ $stile['bordo'] }} {{ $stile['fondo'] }} px-7 py-6">
    <div class="flex flex-wrap items-baseline justify-between gap-3">
        <h2 class="font-serif text-xl font-medium">Account agenzia</h2>
        <span class="font-sans text-[11px] tracking-[0.22em] uppercase text-oro-scuro">
            {{ $agenzia->stato->etichetta() }}
        </span>
    </div>

    <p class="mt-3 max-w-2xl font-sans font-light text-[14px] leading-relaxed text-testo-soft">
        {{ $testo }}
    </p>

    @if ($agenzia->stato === StatoAgenzia::Rifiutata && $agenzia->motivo_rifiuto)
        <p class="mt-4 border-l-2 border-caffe/20 pl-4 font-sans font-light text-[13px] leading-relaxed text-testo">
            {{ $agenzia->motivo_rifiuto }}
        </p>
    @endif
</section>
