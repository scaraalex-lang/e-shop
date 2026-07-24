<?php

namespace Modules\Memorial\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Memorial\Models\Defunto;
use Modules\Memorial\Models\Santo;

class MemorialDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Defunto d'esempio per far girare il ricordino-designer con dati reali.
        // Consenso GDPR volutamente NON dato: si prova il flusso di autorizzazione.
        Defunto::updateOrCreate(
            ['nome' => 'Maria', 'cognome' => 'Rossi'],
            [
                'data_nascita' => '1943-03-14',
                'data_morte'   => '2026-07-02',
                'anni'         => 82,
                'frase'        => "È mancata all'affetto dei suoi cari",
                'preghiera'    => "Signore, accogli nella tua pace\nl'anima della tua serva Maria.\nAmen.",
                'gdpr_consenso' => false,
            ],
        );

        // Galleria santi: il Sacro Cuore già presente nello storage.
        Santo::updateOrCreate(
            ['nome' => 'Sacro Cuore di Gesù'],
            ['path' => 'photoprint-demo/test-sacro-cuore.jpg'],
        );
    }
}
