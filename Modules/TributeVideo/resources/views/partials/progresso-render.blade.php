{{--
    Feedback visivo durante il render: prima c'era solo la scritta "Stato: In
    elaborazione", nessun indizio che qualcosa si stesse davvero muovendo.

    Niente percentuale reale (il Job non scrive un progresso, solo lo stato
    in_coda/in_elaborazione/pronto/errore — vedi VideoMemoriale): il disco è
    quindi indeterminato (rotazione continua), non una vera progress bar a
    percentuale. Il timer invece è preciso, calcolato lato server da
    `created_at` a `now()` — si aggiorna da solo ad ogni refresh della pagina
    (il meta-refresh ogni 4s già esistente), niente JS necessario.

    Richiede $video (VideoMemoriale) nello scope di chi include il partial.
--}}
@php
    // Carbon 3 restituisce un float su diffInSeconds() (stesso comportamento
    // già noto per diffInYears altrove nel progetto): troncato esplicitamente,
    // non lasciato all'implicit cast di intdiv()/% (deprecation warning PHP 8.1+).
    $secondiTrascorsi = (int) ($video->render_avviato_il ?? $video->created_at)->diffInSeconds(now());
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
