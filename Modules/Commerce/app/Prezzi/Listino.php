<?php

namespace Modules\Commerce\Prezzi;

use App\Models\User;
use Illuminate\Support\Collection;
use Modules\Catalog\Models\Product;
use Modules\Commerce\Models\ScaglionePrezzo;
use Modules\Commerce\Prezzi\Contracts\FonteSconto;

/**
 * Quanto costa un prodotto, a questa quantità, per questo account.
 *
 * Un unico posto in cui si decide un prezzo. Vetrina, carrello e (a breve)
 * ordine passano tutti di qui: se la regola cambia, cambia in un punto solo.
 *
 * Tre regole di business, in quest'ordine:
 *  1. il prezzo pieno lo calcola il prodotto — è lui a sapere se è un kit e
 *     quanti pezzi comprende (Product::priceForQuantity);
 *  2. se l'agenzia ha una percentuale personale, è quella a valere — SOSTITUISCE
 *     gli scaglioni generici, non si somma (deciso col committente);
 *  3. altrimenti si applica lo sconto a scaglioni del prodotto, e SOLO a
 *     un'agenzia approvata.
 *
 * Tutti i conti sono in aritmetica intera sui centesimi: la percentuale entra
 * come centesimi di punto, così il denaro non passa mai da un float.
 */
class Listino
{
    /** Scaglioni già letti in questa richiesta, per id prodotto. */
    private array $scaglioni = [];

    public function perRiga(Product $prodotto, int $quantita, ?User $utente = null): Prezzo
    {
        $quantita = max($quantita, 0);
        $pieno = $prodotto->priceForQuantity($quantita);

        $fonte = $this->scontoApplicabile($prodotto, $quantita, $utente);

        if (! $fonte) {
            return Prezzo::senzaSconto($pieno);
        }

        $scontato = intdiv($pieno * (10_000 - $fonte->scontoInCentesimiDiPunto()), 10_000);

        return new Prezzo($pieno, $scontato, $fonte);
    }

    /**
     * La fonte di sconto che vale per questa riga: prima lo sconto personale
     * dell'agenzia (se impostato), altrimenti lo scaglione generico più
     * vantaggioso. Null se l'account non ha diritto alle condizioni riservate.
     */
    private function scontoApplicabile(Product $prodotto, int $quantita, ?User $utente): ?FonteSconto
    {
        if (! $this->condizioniRiservate($utente)) {
            return null;
        }

        if ($percentuale = $utente?->agenzia?->sconto_percentuale) {
            return new ScontoAgenzia($percentuale);
        }

        return $this->scaglioneApplicabile($prodotto, $quantita, $utente);
    }

    /**
     * Lo scaglione più vantaggioso fra quelli raggiunti dalla quantità.
     * Null se l'account non ha diritto alle condizioni riservate.
     */
    public function scaglioneApplicabile(Product $prodotto, int $quantita, ?User $utente): ?ScaglionePrezzo
    {
        if (! $this->condizioniRiservate($utente)) {
            return null;
        }

        return $this->scaglioniDi($prodotto)
            ->filter(fn (ScaglionePrezzo $s) => $quantita >= $s->quantita_minima)
            ->sortByDesc('sconto_percentuale')
            ->first();
    }

    /**
     * Gli scaglioni di un prodotto, dal più basso al più alto.
     * Si mostrano solo a chi può usarli: per tutti gli altri non esistono.
     */
    public function scaglioniDi(Product $prodotto): Collection
    {
        return $this->scaglioni[$prodotto->id] ??= ScaglionePrezzo::query()
            ->where('product_id', $prodotto->id)
            ->orderBy('quantita_minima')
            ->get();
    }

    /**
     * Legge in un colpo solo gli scaglioni di più prodotti: serve al carrello,
     * che altrimenti farebbe una query per riga.
     */
    public function precarica(iterable $prodotti): void
    {
        $mancanti = collect($prodotti)
            ->pluck('id')
            ->reject(fn ($id) => array_key_exists($id, $this->scaglioni))
            ->unique();

        if ($mancanti->isEmpty()) {
            return;
        }

        $perProdotto = ScaglionePrezzo::query()
            ->whereIn('product_id', $mancanti)
            ->orderBy('quantita_minima')
            ->get()
            ->groupBy('product_id');

        foreach ($mancanti as $id) {
            $this->scaglioni[$id] = $perProdotto->get($id, collect());
        }
    }

    /**
     * Chi vede gli sconti: solo il referente di un'agenzia approvata.
     * Un'agenzia in attesa, sospesa o non approvata paga il prezzo pubblico.
     */
    public function condizioniRiservate(?User $utente): bool
    {
        return $utente?->eAgenziaApprovata() === true;
    }
}
