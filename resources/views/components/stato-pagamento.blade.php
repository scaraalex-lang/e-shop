@props(['ordine'])

{{--
    Cosa sta succedendo al pagamento di un ordine, in una riga: usato da
    gestione/ordini/index, gestione/agenzie/{agenzia}/movimenti e
    account/fatture — prima era la stessa logica duplicata in tre viste.

    Il contrassegno non ha uno stato tracciato (lo incassa il corriere, non
    noi): "Alla consegna" e basta. Per tutti gli altri, l'ordine di lettura
    conta — un pagamento riuscito o fallito viene prima di "fatturato ma non
    ancora saldato", che a sua volta viene prima di "da fatturare".
--}}
@php
    use Modules\Commerce\Enums\MetodoPagamento;
    use Modules\Commerce\Enums\StatoPagamento;

    [$etichetta, $colore] = match (true) {
        $ordine->metodo_pagamento === MetodoPagamento::Contrassegno => ['Alla consegna', 'text-testo-soft'],
        $ordine->stato_pagamento === StatoPagamento::Pagato => ['Pagato il '.$ordine->pagato_at->format('d/m/Y'), 'text-successo'],
        $ordine->stato_pagamento === StatoPagamento::Fallito => ['Pagamento non riuscito', 'text-errore'],
        $ordine->metodo_pagamento === MetodoPagamento::Fattura && $ordine->fatturata() => ['Fatturato il '.$ordine->fattura_emessa_at->format('d/m/Y').' (n. '.$ordine->fattura_numero.')', 'text-oro-scuro'],
        $ordine->metodo_pagamento === MetodoPagamento::Fattura => ['Da fatturare', 'text-oro-scuro'],
        default => ['Da pagare', 'text-oro-scuro'],
    };
@endphp

<div {{ $attributes }}>
    <span class="font-sans text-[13px] {{ $colore }}">{{ $etichetta }}</span>
    @if ($ordine->riferimento_pagamento)
        <span class="block font-sans font-light text-[11px] text-testo-soft/70 tabular-nums mt-0.5">
            {{ $ordine->riferimento_pagamento }}
        </span>
    @endif
</div>
