<?php

namespace Modules\Catalog\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;

/**
 * I tre servizi che un'agenzia può aprire pagando in crediti invece che in
 * denaro: Funerale, Trigesimo, Anniversario. `crediti` negativo (consumo,
 * non accredito): stesso `CreaOrdine::da()` di `ServiziAgenziaSeeder`, che
 * genera già un `MovimentoCredito` col segno del prodotto senza bisogno di
 * codice diverso. `is_photo_printable=true`, a differenza del pacchetto
 * crediti: qui SERVE che l'ordine richieda lavorazione, per riusare intatto
 * il percorso dati-defunto → foto → Ricordino/Manifesto/Necrologio.
 *
 * Prezzo 0: il costo è tutto in crediti. Categoria fuori dai menu pubblici
 * (nessuno ce la aggiunge da /gestione), stesso trattamento di
 * `servizi-agenzia` — non serve un filtro nel codice, la vetrina è
 * data-driven da /gestione, non genera menu dalle categorie attive.
 *
 * Costo in crediti indicativo, da confermare col committente.
 */
class ServiziNecrologioSeeder extends Seeder
{
    public function run(): void
    {
        $categoria = Category::updateOrCreate(
            ['slug' => 'servizi-necrologio'],
            [
                'name' => 'Servizi necrologio',
                'description' => 'Apertura dei designer (manifesto, necrologio, ricordini) legata a un\'occasione, pagata in crediti.',
                'sort_order' => 91,
                'is_active' => true,
            ],
        );

        $servizi = [
            [
                'sku' => 'SRV-NECRO-FUNERALE',
                'slug' => 'servizio-necrologio-funerale',
                'name' => 'Servizio necrologio: Funerale',
                'short_description' => 'Apre la lavorazione per il funerale: dati del defunto, foto, manifesto e necrologio.',
                'crediti' => -20,
            ],
            [
                'sku' => 'SRV-NECRO-TRIGESIMO',
                'slug' => 'servizio-necrologio-trigesimo',
                'name' => 'Servizio necrologio: Trigesimo',
                'short_description' => 'Apre la lavorazione per il trigesimo: manifesto e necrologio con la data della cerimonia.',
                'crediti' => -15,
            ],
            [
                'sku' => 'SRV-NECRO-ANNIVERSARIO',
                'slug' => 'servizio-necrologio-anniversario',
                'name' => 'Servizio necrologio: Anniversario',
                'short_description' => 'Apre la lavorazione per un anniversario della scomparsa (1°, 2°...).',
                'crediti' => -10,
            ],
        ];

        foreach ($servizi as $s) {
            Product::updateOrCreate(
                ['sku' => $s['sku']],
                [
                    'category_id' => $categoria->id,
                    'slug' => $s['slug'],
                    'name' => $s['name'],
                    'short_description' => $s['short_description'],
                    'description' => $s['short_description'],
                    'price' => 0,
                    'crediti' => $s['crediti'],
                    'is_photo_printable' => true,
                    'is_configurable' => false,
                    'is_kit' => false,
                    'is_active' => true,
                    'stock' => 999999,
                    'sort_order' => 10,
                ],
            );
        }
    }
}
