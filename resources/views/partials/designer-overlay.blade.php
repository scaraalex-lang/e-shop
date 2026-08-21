{{--
    Overlay condiviso per i 4 designer a canvas (Ricordino, Manifesto,
    Necrologio Card, Storia Social): li apre in un iframe dentro un pannello
    sopra la pagina corrente, invece di navigarci via — la pagina di partenza
    resta visibile/scorrevole sotto, niente più "fullscreen" che la fa sparire.

    Uso: aggiungere l'attributo `data-designer-overlay` a qualunque
    `<a href>`/`<x-button :href>` che punta a uno dei 4 designer (x-button
    passa già gli attributi extra al tag <a> finale via $attributes->merge()).
    Nessuna riscrittura di markup altrove, solo quell'attributo in più.

    Incluso una volta per layout (account, gestione) — non nei 4 designer
    stessi, che restano pagine autonome invariate all'interno dell'iframe.
--}}
<div id="designer-overlay" class="fixed inset-0 z-[2000] hidden items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="relative w-[96vw] h-[94vh] bg-bianco shadow-2xl">
        <button type="button" id="designer-overlay-chiudi"
                class="absolute -top-3 -right-3 w-9 h-9 rounded-full bg-testo text-bianco text-lg leading-none z-10 cursor-pointer"
                aria-label="Chiudi">
            &times;
        </button>
        <iframe id="designer-overlay-iframe" class="w-full h-full border-0" allow="clipboard-write"></iframe>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const overlay = document.getElementById('designer-overlay');
    const iframe = document.getElementById('designer-overlay-iframe');
    const bottoneChiudi = document.getElementById('designer-overlay-chiudi');

    document.addEventListener('click', function (e) {
        const trigger = e.target.closest('[data-designer-overlay]');
        if (!trigger) return;
        e.preventDefault();
        iframe.src = trigger.href;
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
        document.body.style.overflow = 'hidden';
    });

    // Ricarica la pagina genitore alla chiusura: nessuno dei 4 designer
    // segnala "ho salvato" al genitore (niente postMessage), il modo più
    // semplice e robusto per mostrare lo stato aggiornato (badge "Pronto",
    // anteprime...) è lo stesso già in uso oggi con la navigazione a pagina
    // intera — "torna indietro" e la pagina è di nuovo fresca.
    function chiudiDesignerOverlay() {
        overlay.classList.add('hidden');
        overlay.classList.remove('flex');
        document.body.style.overflow = '';
        iframe.src = 'about:blank'; // ferma subito audio/video/timer nell'iframe
        window.location.reload();
    }

    bottoneChiudi.addEventListener('click', chiudiDesignerOverlay);

    // Solo Esc, non il click sul backdrop: un editor a canvas può avere
    // lavoro non salvato, chiudere per sbaglio cliccando fuori sarebbe
    // fastidioso quanto il fullscreen che si sta togliendo.
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && ! overlay.classList.contains('hidden')) {
            chiudiDesignerOverlay();
        }
    });
});
</script>
