<?php

namespace Modules\Catalog\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;

/**
 * Categoria e articoli di partenza per VideoBook: due prodotti FISICI
 * distinti (device video a libro / fotoalbum stampato) che sbloccano lo
 * stesso impaginatore (Modules\VideoBook) — `has_video_book` è il flag
 * comune, letto da EditorController::videoBookPagato() esattamente come
 * `has_qr_memorial` sblocca il Video Memoriale.
 *
 * Ogni combinazione materiale/colore è una riga prodotto a sé (niente
 * selettore di varianti in vetrina, il progetto non ne ha ancora uno):
 * "diversa copertina" = SKU diverso, non un'opzione scelta in pagina.
 *
 * Prezzi SEGNAPOSTO (centesimi), da correggere dal pannello staff prima di
 * andare live — non è un listino reale, serve solo a non lasciare 0€ in
 * vetrina. Nessuna immagine: le due foto di riferimento ricevute sono un
 * dispositivo/album di un fornitore terzo, non pubblicabili come nostre;
 * la fotografia vera dei nostri articoli va caricata dal pannello staff
 * (Modules/Catalog/app/Http/Controllers/GestioneProdottiController).
 *
 * Rilanciabile: aggiorna per sku invece di duplicare.
 */
class VideoBookProductSeeder extends Seeder
{
    public function run(): void
    {
        $categoria = Category::updateOrCreate(['slug' => 'video-book'], [
            'name'        => 'Video Book',
            'description' => 'Il ricordo composto pagina per pagina: un dispositivo video a libro o un fotoalbum stampato, entrambi nati dallo stesso impaginatore.',
            'sort_order'  => 35,
            'is_active'   => true,
        ]);

        foreach ($this->prodotti($categoria->id) as $dati) {
            Product::updateOrCreate(['sku' => $dati['sku']], $dati);
        }
    }

    private function prodotti(int $categoriaId): array
    {
        $comune = [
            'category_id'        => $categoriaId,
            'is_configurable'    => false,
            'is_photo_printable' => false,
            'has_video_book'     => true,
            'is_active'          => true,
            'stock'              => 50,
        ];

        return [
            // ---- Device video a libro (schermo incorporato) ----
            $comune + [
                'sku'                => 'VDB-DIG-BLU',
                'slug'               => 'video-book-digitale-blu-notte',
                'name'               => 'Video Book Digitale — Blu Notte',
                'short_description'  => 'Un libro che si apre e racconta: schermo incorporato, il video del ricordo pronto a ogni apertura.',
                'price'              => 12900,
                'material'           => 'Velluto',
                'color'              => 'Blu Notte',
                'attributes'         => ['schermo' => '7 pollici', 'formato_video' => 'widescreen', 'ricarica' => 'USB-C'],
                'sort_order'         => 10,
            ],
            $comune + [
                'sku'                => 'VDB-DIG-BOR',
                'slug'               => 'video-book-digitale-bordeaux',
                'name'               => 'Video Book Digitale — Bordeaux',
                'short_description'  => 'Un libro che si apre e racconta: schermo incorporato, il video del ricordo pronto a ogni apertura.',
                'price'              => 12900,
                'material'           => 'Velluto',
                'color'              => 'Bordeaux',
                'attributes'         => ['schermo' => '7 pollici', 'formato_video' => 'widescreen', 'ricarica' => 'USB-C'],
                'sort_order'         => 20,
            ],
            $comune + [
                'sku'                => 'VDB-DIG-TOR',
                'slug'               => 'video-book-digitale-grigio-tortora',
                'name'               => 'Video Book Digitale — Grigio Tortora',
                'short_description'  => 'Un libro che si apre e racconta: schermo incorporato, il video del ricordo pronto a ogni apertura.',
                'price'              => 12900,
                'material'           => 'Velluto',
                'color'              => 'Grigio Tortora',
                'attributes'         => ['schermo' => '7 pollici', 'formato_video' => 'widescreen', 'ricarica' => 'USB-C'],
                'sort_order'         => 30,
            ],

            // ---- Fotoalbum stampato ----
            $comune + [
                'sku'                => 'VDB-ALB-LEG',
                'slug'               => 'fotoalbum-videobook-legno-plexiglass',
                'name'               => 'Fotoalbum VideoBook — Legno e Plexiglass',
                'short_description'  => 'Le pagine composte nell\'impaginatore, stampate e rilegate: copertina in legno con finestra in plexiglass.',
                'price'              => 15900,
                'material'           => 'Legno e plexiglass',
                'color'              => 'Naturale',
                'attributes'         => ['formato' => '20x20 cm', 'pagine' => '20 (40 facciate)'],
                'sort_order'         => 40,
            ],
            $comune + [
                'sku'                => 'VDB-ALB-LIN',
                'slug'               => 'fotoalbum-videobook-tessuto-lino',
                'name'               => 'Fotoalbum VideoBook — Tessuto Lino',
                'short_description'  => 'Le pagine composte nell\'impaginatore, stampate e rilegate: copertina in lino, sobria ed elegante.',
                'price'              => 13900,
                'material'           => 'Tessuto lino',
                'color'              => 'Panna',
                'attributes'         => ['formato' => '20x20 cm', 'pagine' => '20 (40 facciate)'],
                'sort_order'         => 50,
            ],
            $comune + [
                'sku'                => 'VDB-ALB-PEL',
                'slug'               => 'fotoalbum-videobook-pelle',
                'name'               => 'Fotoalbum VideoBook — Pelle',
                'short_description'  => 'Le pagine composte nell\'impaginatore, stampate e rilegate: copertina in pelle, la scelta più curata.',
                'price'              => 17900,
                'material'           => 'Pelle',
                'color'              => 'Testa di moro',
                'attributes'         => ['formato' => '20x20 cm', 'pagine' => '20 (40 facciate)'],
                'sort_order'         => 60,
            ],
        ];
    }
}
