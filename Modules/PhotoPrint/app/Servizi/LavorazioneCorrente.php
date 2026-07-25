<?php

namespace Modules\PhotoPrint\Servizi;

use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Auth;
use Modules\Commerce\Models\Ordine;

/**
 * Su quale ordine sta lavorando il cliente in questo momento.
 *
 * Gli editor sono due pagine sole, importate quasi as-is da memoraiengine: non
 * prendono l'ordine dall'indirizzo. Il ponte è qui — si apre la lavorazione da
 * un ordine, e da quel momento gli editor e i loro endpoint sanno di quale
 * pratica si tratta.
 *
 * L'ordine viene riletto dal database a ogni richiesta e ricontrollato:
 * l'id in sessione è un promemoria, non un permesso.
 */
class LavorazioneCorrente
{
    private const CHIAVE = 'lavorazione_ordine_id';

    public function __construct(private Session $sessione) {}

    public function imposta(Ordine $ordine): void
    {
        $this->sessione->put(self::CHIAVE, $ordine->id);
    }

    public function dimentica(): void
    {
        $this->sessione->forget(self::CHIAVE);
    }

    /**
     * L'ordine in lavorazione, solo se è davvero di chi sta chiedendo e se è
     * ancora aperto. In tutti gli altri casi: niente.
     */
    public function ordine(): ?Ordine
    {
        $id = $this->sessione->get(self::CHIAVE);
        $utente = Auth::user();

        if (! $id || ! $utente) {
            return null;
        }

        $ordine = Ordine::find($id);

        if (! $ordine || $ordine->user_id !== $utente->id || ! $ordine->lavorazioneApribile()) {
            return null;
        }

        return $ordine;
    }
}
