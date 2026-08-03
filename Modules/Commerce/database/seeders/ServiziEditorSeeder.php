<?php

namespace Modules\Commerce\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Commerce\Models\ServizioEditor;

/**
 * Le righe fisse del catalogo servizi. Costi in crediti indicativi, da
 * confermare col committente — modificabili poi da /gestione/servizi senza
 * bisogno di un'altra migration.
 *
 * `embed` è un caso a parte rispetto agli altri tre: quelli si attivano su un
 * ordine nuovo (checkbox in "Acquisto servizio digitale", vedi
 * OrdiniController::servizi()), `embed` si acquista direttamente dalla
 * pagina di un necrologio già esistente (vedi
 * NecrologiController::acquistaEmbed()) — sta in questa stessa tabella solo
 * per riusare il costo in crediti modificabile da staff, non compare fra le
 * checkbox dell'ordine. `embed` è anche l'unico con `costo_crediti_a_termine`
 * (prezzo scontato per la scadenza a 6 mesi, invece del perpetuo garantito
 * fino a 3 anni): gli altri tre non hanno un concetto di durata, restano
 * senza.
 */
class ServiziEditorSeeder extends Seeder
{
    public function run(): void
    {
        $servizi = [
            ['codice' => 'ricordini', 'etichetta' => 'Ricordini', 'costo_crediti' => 15, 'costo_crediti_a_termine' => null],
            ['codice' => 'manifesti', 'etichetta' => 'Manifesti', 'costo_crediti' => 20, 'costo_crediti_a_termine' => null],
            ['codice' => 'necrologi', 'etichetta' => 'Necrologi', 'costo_crediti' => 10, 'costo_crediti_a_termine' => null],
            ['codice' => 'embed', 'etichetta' => 'Necrologio integrabile nel vostro sito', 'costo_crediti' => 25, 'costo_crediti_a_termine' => 15],
        ];

        foreach ($servizi as $s) {
            ServizioEditor::updateOrCreate(
                ['codice' => $s['codice']],
                [
                    'etichetta' => $s['etichetta'],
                    'costo_crediti' => $s['costo_crediti'],
                    'costo_crediti_a_termine' => $s['costo_crediti_a_termine'],
                    'attivo' => true,
                ],
            );
        }
    }
}
