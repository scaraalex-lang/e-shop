<?php

namespace Modules\Commerce\Contabilita;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\Commerce\Enums\MetodoPagamento;
use Modules\Commerce\Enums\StatoPagamento;
use Modules\Commerce\Models\Agenzia;
use Modules\Commerce\Models\MovimentoCredito;
use Modules\Commerce\Models\Ordine;

/**
 * L'estratto conto di un'agenzia: un evento per riga (fattura emessa,
 * pagamento ricevuto, crediti usati), non un ordine con lo stato attuale —
 * lo stesso ordine può generare più righe, in periodi diversi, se gli è
 * successo più di una cosa (fatturato ad agosto, saldato a settembre).
 *
 * Un ordine di solo servizio (pagato in crediti, totale sempre 0 — vedi
 * AttivaServiziOrdine) non genera mai un evento "pagamento": valoreInDenaro()
 * è 0, niente da mostrare come movimento di denaro. Genera solo l'evento
 * "crediti usati", esattamente come un ordine di catalogo pagato in crediti.
 */
class EstrattoConto
{
    /**
     * Il mese dell'estratto conto: dal querystring (?anno=&mese=), il mese
     * corrente se non specificato. Un solo posto, riusato identico da
     * GestioneAgenzieController::movimenti e OrdiniController::fatture —
     * stesso URL (?anno=&mese=) in entrambe le pagine.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function periodo(Request $request): array
    {
        $anno = (int) $request->query('anno', now()->year);
        $mese = (int) $request->query('mese', now()->month);
        $inizio = Carbon::create($anno, $mese, 1)->startOfMonth();

        return [$inizio, $inizio->copy()->endOfMonth()];
    }

    /** @return Collection<int, MovimentoConto> dal più recente al più vecchio */
    public function perPeriodo(Agenzia $agenzia, Carbon $inizio, Carbon $fine): Collection
    {
        $eventi = collect();

        $ordini = Ordine::where('agenzia_id', $agenzia->id)
            ->where(function ($query) use ($inizio, $fine) {
                $query->whereBetween('fattura_emessa_at', [$inizio, $fine])
                    ->orWhereBetween('pagato_at', [$inizio, $fine]);
            })
            ->get();

        foreach ($ordini as $ordine) {
            if ($ordine->fattura_emessa_at?->between($inizio, $fine)) {
                $eventi->push(new MovimentoConto(
                    data: $ordine->fattura_emessa_at,
                    tipo: 'fattura_emessa',
                    etichetta: "Fattura {$ordine->fattura_numero} emessa",
                    ordine: $ordine,
                    importoDenaro: $ordine->valoreInDenaro(),
                ));
            }

            // Un ordine coperto interamente dai crediti non ha mai un
            // pagamento in denaro da registrare qui: lo racconta già
            // l'evento "crediti usati" sotto.
            if ($ordine->pagato_at?->between($inizio, $fine) && $ordine->valoreInDenaro() > 0) {
                $eventi->push(new MovimentoConto(
                    data: $ordine->pagato_at,
                    tipo: 'pagamento',
                    etichetta: $ordine->metodo_pagamento === MetodoPagamento::Fattura
                        ? "Fattura {$ordine->fattura_numero} saldata"
                        : 'Pagamento a carta',
                    ordine: $ordine,
                    importoDenaro: $ordine->valoreInDenaro(),
                    riferimento: $ordine->riferimento_pagamento,
                ));
            }
        }

        MovimentoCredito::where('agenzia_id', $agenzia->id)
            ->where('quantita', '<', 0)
            ->whereNotNull('ordine_id')
            ->whereBetween('created_at', [$inizio, $fine])
            ->with('ordine')
            ->get()
            // Un ordine cancellato non deve lasciare un riferimento rotto.
            ->filter(fn (MovimentoCredito $m) => $m->ordine !== null)
            ->each(function (MovimentoCredito $movimento) use ($eventi) {
                $eventi->push(new MovimentoConto(
                    data: $movimento->created_at,
                    tipo: 'crediti_usati',
                    etichetta: 'Crediti usati',
                    ordine: $movimento->ordine,
                    importoCrediti: -$movimento->quantita,
                    riferimento: $movimento->causale,
                ));
            });

        return $eventi->sortByDesc(fn (MovimentoConto $e) => $e->data->timestamp)->values();
    }

    /**
     * Le tre cifre "a oggi" — quanto resta da fatturare/saldare in assoluto,
     * quanto incassato a carta in assoluto — non filtrate per periodo come
     * `perPeriodo()`. Riusata identica da GestioneAgenzieController::movimenti
     * e OrdiniController::fatture.
     *
     * @return array{daFatturare: int, daSaldare: int, incassatoCarta: int}
     */
    public function riepilogoOggi(Agenzia $agenzia): array
    {
        return [
            'daFatturare' => $agenzia->ordini()
                ->where('metodo_pagamento', MetodoPagamento::Fattura)
                ->whereNull('fattura_emessa_at')
                ->get(['totale', 'crediti_usati'])
                ->sum(fn (Ordine $o) => $o->valoreInDenaro()),
            'daSaldare' => $agenzia->ordini()
                ->where('metodo_pagamento', MetodoPagamento::Fattura)
                ->whereNotNull('fattura_emessa_at')
                ->where('stato_pagamento', '!=', StatoPagamento::Pagato)
                ->get(['totale', 'crediti_usati'])
                ->sum(fn (Ordine $o) => $o->valoreInDenaro()),
            'incassatoCarta' => $agenzia->ordini()
                ->where('metodo_pagamento', MetodoPagamento::Carta)
                ->where('stato_pagamento', StatoPagamento::Pagato)
                ->get(['totale', 'crediti_usati'])
                ->sum(fn (Ordine $o) => $o->valoreInDenaro()),
        ];
    }
}
