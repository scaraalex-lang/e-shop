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
 * checkbox dell'ordine.
 */
class ServiziEditorSeeder extends Seeder
{
    public function run(): void
    {
        $servizi = [
            ['codice' => 'ricordini', 'etichetta' => 'Ricordini', 'costo_crediti' => 15],
            ['codice' => 'manifesti', 'etichetta' => 'Manifesti', 'costo_crediti' => 20],
            ['codice' => 'necrologi', 'etichetta' => 'Necrologi', 'costo_crediti' => 10],
            ['codice' => 'embed', 'etichetta' => 'Necrologio integrabile nel vostro sito', 'costo_crediti' => 25],
        ];

        foreach ($servizi as $s) {
            ServizioEditor::updateOrCreate(
                ['codice' => $s['codice']],
                ['etichetta' => $s['etichetta'], 'costo_crediti' => $s['costo_crediti'], 'attivo' => true],
            );
        }
    }
}
