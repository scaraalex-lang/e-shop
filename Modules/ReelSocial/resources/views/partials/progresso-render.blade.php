{{--
    Feedback visivo durante il render — copia locale del partial già usato in
    TributeVideo (stessa idea, stessa correzione del timer già applicata lì:
    `render_avviato_il` invece di `created_at`, altrimenti un futuro
    "rigenera" mostrerebbe il tempo dalla creazione originale). Restare
    autonomi qui costa poco ed evita un include cross-modulo per 15 righe.

    Richiede $reel (Reel) nello scope di chi include il partial.
--}}
@php
    $secondiTrascorsi = (int) ($reel->render_avviato_il ?? $reel->created_at)->diffInSeconds(now());
    $minuti = intdiv($secondiTrascorsi, 60);
    $secondi = $secondiTrascorsi % 60;
@endphp

<div class="mt-6 flex items-center gap-6">
    <div class="relative w-16 h-16 shrink-0">
        <div class="absolute inset-0 rounded-full border-4 border-oro/20"></div>
        <div class="absolute inset-0 rounded-full border-4 border-transparent border-t-oro animate-spin"></div>
    </div>

    <div>
        <p class="font-serif text-2xl tabular-nums text-oro-scuro" aria-live="polite">
            {{ sprintf('%02d:%02d', $minuti, $secondi) }}
        </p>
        <p class="mt-1 font-sans text-[13px] text-testo-soft">
            Il render richiede qualche minuto. Questa pagina si aggiorna
            automaticamente ogni 4 secondi.
        </p>
    </div>
</div>
