<?php

namespace Modules\Commerce\Servizi;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Commerce\Enums\MetodoPagamento;
use Modules\Commerce\Enums\Occasione;
use Modules\Commerce\Models\Agenzia;
use Modules\Commerce\Models\MovimentoCredito;
use Modules\Commerce\Models\Ordine;
use Modules\Commerce\Models\ServizioEditor;

/**
 * Attiva uno o più servizi editor (ricordini/manifesti/necrologi) su un
 * ordine nuovo, pagati in crediti. Non passa da Carrello/CreaOrdine: un
 * servizio non è un prodotto Catalog, non c'è niente da spedire.
 */
class AttivaServiziOrdine
{
    /**
     * @param  array<int, string>  $codiciServizio
     * @return Ordine|array{richiesti:int,saldo:int,mancano:int} l'ordine creato, o l'esito "crediti insufficienti"
     */
    public function da(
        User $utente,
        Agenzia $agenzia,
        array $codiciServizio,
        Occasione $occasione,
        ?int $numeroAnniversario,
    ): Ordine|array {
        return DB::transaction(function () use ($utente, $agenzia, $codiciServizio, $occasione, $numeroAnniversario) {
            $servizi = ServizioEditor::attivi()->whereIn('codice', $codiciServizio)->get();
            $costoTotale = (int) $servizi->sum('costo_crediti');

            // Lock sui movimenti dell'agenzia: due conferme quasi simultanee
            // non devono poter portare il saldo sotto zero insieme.
            $saldo = (int) $agenzia->movimentiCredito()->lockForUpdate()->sum('quantita');

            if ($saldo < $costoTotale) {
                return ['richiesti' => $costoTotale, 'saldo' => $saldo, 'mancano' => $costoTotale - $saldo];
            }

            $ordine = Ordine::create([
                'numero' => Ordine::prossimoNumero(),
                'user_id' => $utente->id,
                'agenzia_id' => $agenzia->id,
                'metodo_pagamento' => MetodoPagamento::Fattura,
                'totale_pieno' => 0,
                'totale_merce' => 0,
                'spedizione' => 0,
                'totale' => 0,
                // Niente da spedire: l'indirizzo è quello dell'agenzia stessa,
                // non va richiesto di nuovo in un form.
                'consegna_nome' => $agenzia->ragione_sociale,
                'consegna_telefono' => $agenzia->telefono,
                'consegna_indirizzo' => $agenzia->indirizzo,
                'consegna_cap' => $agenzia->cap,
                'consegna_citta' => $agenzia->citta,
                'consegna_provincia' => $agenzia->provincia,
                'richiede_lavorazione' => true,
                'occasione' => $occasione,
                'numero_anniversario' => $numeroAnniversario,
            ]);

            foreach ($servizi as $servizio) {
                $ordine->servizi()->create([
                    'servizio_editor_id' => $servizio->id,
                    'costo_crediti' => $servizio->costo_crediti,
                ]);
            }

            MovimentoCredito::create([
                'agenzia_id' => $agenzia->id,
                'ordine_id' => $ordine->id,
                'quantita' => -$costoTotale,
                'causale' => 'Servizi attivati: '.$servizi->pluck('etichetta')->implode(', '),
            ]);

            $ordine->avvia();

            return $ordine->fresh(['servizi.servizioEditor']);
        });
    }
}
