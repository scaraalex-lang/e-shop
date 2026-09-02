<?php

namespace Modules\VideoBook\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Modules\Commerce\Enums\StatoPagamento;
use Modules\Commerce\Models\Ordine;
use Modules\VideoBook\Models\Libro;

/**
 * Chi può aprire/lavorare un libro e quando i suoi file sono scaricabili —
 * due domande diverse, non una sola.
 *
 * "Posso lavorarci" (aprire l'editor, caricare foto, generare
 * un'anteprima) vale già dal momento in cui l'ordine esiste: il video book
 * non è legato a una pratica funebre come Foto Manager/Ricordino Designer,
 * è un percorso a sé — vedi CLAUDE.md, flusso foto. "Posso scaricare il
 * risultato" vale solo a pagamento avvenuto per il privato; l'agenzia non
 * ha mai questo secondo gate (fattura posticipata, fase 1 export dati).
 *
 * Prima di questo trait i due criteri erano confusi in uno solo
 * (`videoBookPagato`), duplicato uguale in quattro controller diversi
 * (Editor, PaginaApi, Video, Pdf): bloccava l'editor stesso finché l'ordine
 * non risultava pagato, impedendo la sola cosa che invece deve restare
 * sempre aperta — comporre e vedere l'anteprima prima di pagare.
 */
trait ControllaAccessoLibro
{
    /**
     * Dall'ordine (EditorController::apriDalOrdine, prima che il libro
     * esista): proprio e con un Video Book fra le righe, nient'altro.
     */
    private function assicuraOrdineHaVideoBook(Request $request, Ordine $ordine): void
    {
        $user = $request->user();
        abort_unless($ordine->diChi($user), 404);
        abort_unless(
            $user->eStaff() || $user->eAgenziaApprovata() || $this->contieneVideoBook($ordine),
            403,
            'Questo ordine non contiene un Video Book.',
        );
    }

    /**
     * Dal libro (gli altri controller): stesso criterio, letto dall'ordine
     * a cui il libro è agganciato. 404 e non 403 se non è suo — non si
     * conferma a chi indovina un id che quel libro esiste.
     */
    private function assicuraProprio(Request $request, Libro $libro): void
    {
        $user = $request->user();
        if ($user->eStaff() || $user->eAgenziaApprovata()) {
            return;
        }

        $ordine = $this->ordineDelLibro($libro);
        abort_unless($ordine && $ordine->diChi($user) && $this->contieneVideoBook($ordine), 404);
    }

    /** I file (video, PDF) sono scaricabili: staff/agenzia sempre, il privato solo a ordine pagato. */
    private function libroScaricabile(Request $request, Libro $libro): bool
    {
        $user = $request->user();
        if ($user->eStaff() || $user->eAgenziaApprovata()) {
            return true;
        }

        $ordine = $this->ordineDelLibro($libro);

        return $ordine !== null
            && $ordine->stato_pagamento === StatoPagamento::Pagato
            && $this->contieneVideoBook($ordine);
    }

    private function ordineDelLibro(Libro $libro): ?Ordine
    {
        return $libro->ordine_id ? Ordine::with('righe.product')->find($libro->ordine_id) : null;
    }

    private function contieneVideoBook(Ordine $ordine): bool
    {
        $ordine->loadMissing('righe.product');

        return $ordine->righe->contains(fn ($riga) => $riga->product?->has_video_book === true);
    }
}
