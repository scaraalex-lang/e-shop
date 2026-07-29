<?php

namespace Modules\Commerce\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Commerce\Models\ServizioEditor;

/**
 * Le tre righe fisse del catalogo servizi. Costi in crediti indicativi, da
 * confermare col committente — modificabili poi da /gestione/servizi senza
 * bisogno di un'altra migration.
 */
class ServiziEditorSeeder extends Seeder
{
    public function run(): void
    {
        $servizi = [
            ['codice' => 'ricordini', 'etichetta' => 'Ricordini', 'costo_crediti' => 15],
            ['codice' => 'manifesti', 'etichetta' => 'Manifesti', 'costo_crediti' => 20],
            ['codice' => 'necrologi', 'etichetta' => 'Necrologi', 'costo_crediti' => 10],
        ];

        foreach ($servizi as $s) {
            ServizioEditor::updateOrCreate(
                ['codice' => $s['codice']],
                ['etichetta' => $s['etichetta'], 'costo_crediti' => $s['costo_crediti'], 'attivo' => true],
            );
        }
    }
}
