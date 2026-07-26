<?php

namespace Modules\Catalog\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;

/**
 * Il pacchetto crediti per le agenzie: accesso ai servizi editor (Ricordino
 * Designer, necrologio) senza comprare un kit fisico da stampare.
 *
 * `is_photo_printable` è deliberatamente false: comprare crediti non deve
 * mettere l'ordine in lavorazione (niente pratica da questo acquisto). Il
 * consumo dei crediti per aprire un servizio senza un ordine dietro è un
 * pezzo ancora da costruire — qui nasce solo l'accredito.
 *
 * Prezzo e quantità di crediti sono un punto di partenza, non una decisione
 * di prezzo definitiva: si aggiustano qui, senza toccare schema o codice.
 */
class ServiziAgenziaSeeder extends Seeder
{
    public function run(): void
    {
        $categoria = Category::updateOrCreate(
            ['slug' => 'servizi-agenzia'],
            [
                'name' => 'Servizi per agenzie',
                'description' => 'Accesso agli strumenti di lavorazione (ricordini, necrologio) senza un kit fisico da stampare.',
                'sort_order' => 90,
                'is_active' => true,
            ],
        );

        Product::updateOrCreate(
            ['sku' => 'SRV-CREDITI-100'],
            [
                'category_id' => $categoria->id,
                'slug' => 'pacchetto-100-crediti',
                'name' => 'Pacchetto 100 crediti',
                'short_description' => 'Crediti per usare il Ricordino Designer e il necrologio senza comprare un kit.',
                'description' => "Un pacchetto di crediti spendibili sui servizi editor — Ricordino Designer e necrologio — senza legarli all'acquisto di un kit trigesimo fisico.\n\nOgni servizio ha il suo costo in crediti, deciso caso per caso: il pacchetto è la ricarica, non un accesso a tempo.",
                'price' => 10000,
                'crediti' => 100,
                'is_photo_printable' => false,
                'is_configurable' => false,
                'is_kit' => false,
                'is_active' => true,
                'stock' => 999999,
                'sort_order' => 10,
            ],
        );
    }
}
