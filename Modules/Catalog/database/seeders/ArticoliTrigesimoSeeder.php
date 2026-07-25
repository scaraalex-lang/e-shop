<?php

namespace Modules\Catalog\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductImage;

/**
 * Gli articoli che accompagnano il trigesimo, oltre ai ricordini.
 *
 * Sono tutti prodotti con foto: il cliente carica il ritratto e lo fa
 * elaborare, esattamente come per i ricordini. Il flag `is_photo_printable`
 * è quello che li manda nella lavorazione foto dopo l'ordine.
 *
 * Immagini fornite dal committente e normalizzate con `catalog:import-images`.
 */
class ArticoliTrigesimoSeeder extends Seeder
{
    private const ARTICOLI = [
        [
            'sku' => 'TRG-COF-BLU',
            'slug' => 'cofanetto-porta-ricordini',
            'name' => 'Cofanetto porta ricordini',
            'short_description' => 'Cofanetto rivestito in tessuto blu con angoli dorati: dentro, i ricordini e la preghiera; nel coperchio, il ritratto.',
            'description' => "Un cofanetto in cui raccogliere i ricordini del trigesimo e consegnarli alla famiglia con la cura che meritano.\n\nRivestimento in tessuto blu notte, angoli e finiture in oro, interno foderato. Nel coperchio trova posto il ritratto, sul fondo la cartoncino con la preghiera e il nastro in raso.\n\nLa fotografia viene elaborata dal nostro laboratorio prima della stampa: sfondo pulito, luce uniforme, volto rispettato.",
            'price' => 4900,
            'material' => 'Tessuto e cartone rigido',
            'color' => 'Blu notte',
            'attributes' => ['formato' => '13x18 cm', 'finitura' => 'Angoli dorati', 'contiene' => 'Ricordini e preghiera'],
            'sort_order' => 10,
        ],
        [
            'sku' => 'TRG-QUA-BLU',
            'slug' => 'quadretto-ricordo',
            'name' => 'Quadretto ricordo',
            'short_description' => 'Cornice da tavolo in blu e oro, con fregio d\'angolo: il ritratto da tenere in casa.',
            'description' => "La cornice che resta sul mobile del salotto quando tutto il resto è passato.\n\nStruttura rivestita in blu notte con doppio filetto dorato e fregio d'angolo in rilievo, cavalletto posteriore. Pensata per il ritratto elaborato dal nostro laboratorio, con lo sfondo schiarito che è la cifra dei nostri lavori.\n\nSi ordina anche in più esemplari, uno per ogni figlio.",
            'price' => 3200,
            'material' => 'Legno rivestito',
            'color' => 'Blu notte',
            'attributes' => ['formato' => '13x18 cm', 'finitura' => 'Fregio dorato', 'appoggio' => 'Da tavolo'],
            'sort_order' => 20,
        ],
        [
            'sku' => 'TRG-PCO-BLU',
            'slug' => 'portacoroncina',
            'name' => 'Portacoroncina con ritratto',
            'short_description' => 'Libretto apribile con croce dorata: la coroncina, la preghiera e il ritratto in un unico oggetto.',
            'description' => "Un libretto che si apre come un messale: da una parte la preghiera con la coroncina applicata, dall'altra il ritratto.\n\nCopertina rivestita in blu notte con croce dorata in rilievo. La coroncina è in grani di vetro con crocifisso in metallo.\n\nÈ l'oggetto che le famiglie tengono in mano durante la funzione e poi conservano nel cassetto buono.",
            'price' => 3800,
            'material' => 'Cartonato rivestito',
            'color' => 'Blu notte',
            'attributes' => ['formato' => '10x14 cm', 'finitura' => 'Croce dorata', 'coroncina' => 'Grani in vetro'],
            'sort_order' => 30,
        ],
        [
            'sku' => 'TRG-LIB-GRA',
            'slug' => 'libricino-ricordo-grande',
            'name' => 'Libricino ricordo grande',
            'short_description' => 'Libretto apribile nel formato grande: ritratto a sinistra, preghiera del trigesimo a destra.',
            'description' => "Il formato grande del libricino ricordo: due facciate, il ritratto da una parte e la preghiera del trigesimo dall'altra, dentro una cornice di filetti e fregi dorati.\n\nCopertina in blu notte, carta interna avorio. Il testo della preghiera si sceglie dal nostro repertorio oppure lo scrivete voi.\n\nÈ il pezzo che si consegna ai parenti stretti, quando il ricordino singolo sembra poco.",
            'price' => 2600,
            'material' => 'Cartonato rivestito',
            'color' => 'Blu notte',
            'attributes' => ['formato' => '15x20 cm aperto', 'finitura' => 'Fregi dorati', 'carta' => 'Avorio'],
            'sort_order' => 40,
        ],
    ];

    public function run(): void
    {
        $categoria = Category::firstOrCreate(
            ['slug' => 'articoli-trigesimali'],
            ['name' => 'Articoli trigesimali', 'is_active' => true],
        );

        foreach (self::ARTICOLI as $dati) {
            $immagine = $dati['slug'] === 'quadretto-ricordo'
                ? 'quadretto-ricordo'
                : ($dati['slug'] === 'portacoroncina' ? 'portacoroncina-libro' : $dati['slug']);

            $prodotto = Product::updateOrCreate(
                ['sku' => $dati['sku']],
                array_merge($dati, [
                    'category_id' => $categoria->id,
                    // Tutti articoli con foto: dopo l'ordine passano dalla
                    // lavorazione (Foto Manager + Designer).
                    'is_photo_printable' => true,
                    'is_configurable' => true,
                    'is_active' => true,
                    'stock' => 100,
                ]),
            );

            ProductImage::updateOrCreate(
                ['product_id' => $prodotto->id, 'path' => "products/{$immagine}.jpg"],
                ['alt' => $dati['name'], 'is_primary' => true, 'sort_order' => 0],
            );
        }
    }
}
