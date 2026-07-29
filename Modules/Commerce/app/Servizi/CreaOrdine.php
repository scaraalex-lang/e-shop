<?php

namespace Modules\Commerce\Servizi;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Commerce\Enums\MetodoPagamento;
use Modules\Commerce\Enums\Occasione;
use Modules\Commerce\Models\Carrello;
use Modules\Commerce\Models\MovimentoCredito;
use Modules\Commerce\Models\Ordine;
use Modules\Commerce\Prezzi\Listino;
use Modules\Commerce\Prezzi\VoceConto;

/**
 * Trasforma un carrello in un ordine.
 *
 * È il punto in cui i prezzi smettono di essere ricalcolati e diventano
 * storia: da qui in poi l'ordine racconta quello che è stato pattuito, anche
 * se domani cambia il listino o il prodotto sparisce dal catalogo.
 */
class CreaOrdine
{
    public function __construct(
        private Listino $listino,
        private GestoreCarrello $carrelli,
    ) {}

    /**
     * @param  array<string, mixed>  $consegna
     */
    public function da(Carrello $carrello, User $utente, array $consegna, MetodoPagamento $metodo): Ordine
    {
        $conto = $carrello->conto($this->listino, $utente);
        $agenzia = $utente->eAgenziaApprovata() ? $utente->agenzia : null;

        return DB::transaction(function () use ($carrello, $utente, $consegna, $metodo, $conto, $agenzia) {
            $spedizione = $this->spedizione($conto->totale(), $agenzia !== null);

            // Il servizio (funerale/trigesimo/anniversario), se c'è: al
            // massimo uno, per costruzione (CarrelloController rifiuta di
            // aggiungerne un secondo di occasione diversa).
            $vocaServizio = $conto->voci->first(
                fn (VoceConto $v) => Occasione::daSku($v->riga->product?->sku ?? '') !== null
            );
            $occasione = $vocaServizio ? Occasione::daSku($vocaServizio->riga->product->sku) : null;

            $ordine = new Ordine([
                'numero' => Ordine::prossimoNumero(),
                'user_id' => $utente->id,
                'agenzia_id' => $agenzia?->id,
                'metodo_pagamento' => $metodo,
                'totale_pieno' => $conto->totalePieno(),
                'totale_merce' => $conto->totale(),
                'spedizione' => $spedizione,
                'totale' => $conto->totale() + $spedizione,
                'consegna_nome' => $consegna['nome'],
                'consegna_telefono' => $consegna['telefono'],
                'consegna_indirizzo' => $consegna['indirizzo'],
                'consegna_cap' => $consegna['cap'],
                'consegna_citta' => $consegna['citta'],
                'consegna_provincia' => $consegna['provincia'],
                'note' => $consegna['note'] ?? null,
                'richiede_lavorazione' => $conto->voci->contains(
                    fn (VoceConto $v) => (bool) $v->riga->product?->is_photo_printable
                ),
                'occasione' => $occasione,
                'numero_anniversario' => $vocaServizio?->riga->numero_anniversario,
            ]);
            $ordine->save();

            foreach ($conto->voci as $voce) {
                $prodotto = $voce->riga->product;

                $ordine->righe()->create([
                    'product_id' => $prodotto->id,
                    'sku' => $prodotto->sku,
                    'nome' => $prodotto->name,
                    'quantita' => $voce->riga->quantita,
                    'numero_anniversario' => $voce->riga->numero_anniversario,
                    'prezzo_pieno' => $voce->prezzo->pieno,
                    'prezzo' => $voce->prezzo->scontato,
                    'sconto_percentuale' => $voce->prezzo->fonteSconto?->sconto_percentuale,
                    'richiede_foto' => (bool) $prodotto->is_photo_printable,
                ]);

                // Pacchetto crediti (accredito, `crediti` positivo) o servizio
                // necrologio (consumo, `crediti` negativo): il segno del
                // prodotto è già quello giusto per il movimento, subito e non
                // dopo un incasso — sull'agenzia è un rapporto di fiducia per
                // tutto il resto del checkout. Un privato che comprasse questi
                // SKU (mai linkati fuori dall'area agenzia) non ha un'agenzia:
                // niente movimento da registrare.
                if ($agenzia && $prodotto->crediti) {
                    MovimentoCredito::create([
                        'agenzia_id' => $agenzia->id,
                        'ordine_id' => $ordine->id,
                        'quantita' => $prodotto->crediti * $voce->riga->quantita,
                        'causale' => "Acquisto: {$prodotto->name}",
                    ]);
                }
            }

            // Il carrello ha finito il suo compito: se restasse pieno, il
            // cliente ricomprerebbe le stesse cose senza accorgersene.
            $this->carrelli->svuota($carrello);

            return $ordine->load('righe');
        });
    }

    /**
     * Spese di spedizione, in centesimi.
     *
     * Regola di partenza, da concordare: gratis sopra i 79 €, gratis per le
     * agenzie (che ordinano a volume), altrimenti 7,90 €.
     */
    private function spedizione(int $totaleMerce, bool $agenzia): int
    {
        if ($agenzia || $totaleMerce >= 7_900) {
            return 0;
        }

        return 790;
    }
}
