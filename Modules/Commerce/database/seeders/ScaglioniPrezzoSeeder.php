<?php

namespace Modules\Commerce\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Catalog\Models\Product;
use Modules\Commerce\Models\ScaglionePrezzo;

/**
 * Condizioni riservate di partenza per le agenzie approvate.
 *
 * Sono valori di lavoro, non un listino deciso col committente: servono a far
 * vedere il meccanismo funzionante. Quando le percentuali saranno quelle vere
 * si cambiano qui, oppure si gestiscono da /gestione.
 */
class ScaglioniPrezzoSeeder extends Seeder
{
    /** Il kit trigesimo: la quantità è il numero di ricordini. */
    private const SCAGLIONI_KIT = [
        100 => 8,
        200 => 12,
        500 => 18,
    ];

    /** Rosari, corone e bracciali: l'agenzia li prende a scatole. */
    private const SCAGLIONI_DEVOZIONALI = [
        25 => 10,
        50 => 15,
        100 => 20,
    ];

    public function run(): void
    {
        Product::query()
            ->select('id', 'sku', 'is_kit')
            ->get()
            ->each(function (Product $prodotto) {
                $scaglioni = match (true) {
                    (bool) $prodotto->is_kit => self::SCAGLIONI_KIT,
                    str_starts_with($prodotto->sku, 'COR-'),
                    str_starts_with($prodotto->sku, 'ROS-') => self::SCAGLIONI_DEVOZIONALI,
                    default => [],
                };

                foreach ($scaglioni as $quantita => $sconto) {
                    ScaglionePrezzo::updateOrCreate(
                        ['product_id' => $prodotto->id, 'quantita_minima' => $quantita],
                        ['sconto_percentuale' => $sconto],
                    );
                }
            });
    }
}
