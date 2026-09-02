<?php

namespace Modules\VideoBook\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\VideoBook\Models\PaginaTemplate;
use Modules\VideoBook\Servizi\GeneratoreAnteprimaTemplate;

/**
 * Layout di pagina predefiniti MemorAI: le modulazioni di partenza
 * dell'impaginatore, così chi compone il libro sceglie da una griglia
 * invece di partire dal foglio bianco.
 *
 * Coordinate RELATIVE (frazioni di larghezza/altezza della pagina): lo
 * stesso layout vale per qualunque formato fisico scelto per il libro,
 * senza duplicare i numeri per formato (a differenza di RicordinoTemplate,
 * qui non c'è un fronte/retro fisso da adattare a 7x10/6x9).
 *
 * Ogni riquadro lascia un margine sotto per la didascalia: l'altezza dello
 * slot è quella della FOTO, il testo si scrive nello spazio libero verso il
 * riquadro successivo — non serve una geometria propria per la didascalia.
 *
 * Rilanciabile: aggiorna i predefiniti esistenti (per nome) invece di
 * duplicarli.
 */
class PaginaTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $generatore = new GeneratoreAnteprimaTemplate();

        foreach ($this->layouts() as $layout) {
            $template = PaginaTemplate::updateOrCreate(
                ['nome' => $layout['nome'], 'is_predefinito' => true],
                [
                    'numero_foto' => count($layout['slots']),
                    'slots'       => $layout['slots'],
                    'sort_order'  => $layout['ordine'],
                ],
            );

            // Rigenerata ogni volta: se i coefficienti di un layout cambiano,
            // l'anteprima non deve restare disallineata dalla geometria vera.
            $generatore->salva($template);
        }
    }

    /**
     * I layout di partenza. `ordine` dentro ogni slot è la chiave che lega
     * il riquadro alla foto caricata (FotoPagina::slot), non solo l'ordine
     * visivo.
     *
     * I secondi otto (da "Tre foto in fila" in poi) prendono ispirazione
     * dal foglio di riferimento caricato dall'utente (griglie, fasce
     * orizzontali, mosaici) — non una copia: coordinate ridisegnate da zero,
     * il foglio era un pacchetto di template commerciale (PSD/InDesign), non
     * codice riusabile.
     */
    private function layouts(): array
    {
        return [
            [
                'nome'   => 'Foto intera',
                'ordine' => 10,
                'slots'  => [
                    ['ordine' => 1, 'x' => 0.05, 'y' => 0.05, 'w' => 0.90, 'h' => 0.78],
                ],
            ],
            [
                'nome'   => 'Due foto affiancate',
                'ordine' => 20,
                'slots'  => [
                    ['ordine' => 1, 'x' => 0.05, 'y' => 0.08, 'w' => 0.43, 'h' => 0.72],
                    ['ordine' => 2, 'x' => 0.52, 'y' => 0.08, 'w' => 0.43, 'h' => 0.72],
                ],
            ],
            [
                'nome'   => 'Due foto in colonna',
                'ordine' => 21,
                'slots'  => [
                    ['ordine' => 1, 'x' => 0.08, 'y' => 0.06, 'w' => 0.84, 'h' => 0.40],
                    ['ordine' => 2, 'x' => 0.08, 'y' => 0.50, 'w' => 0.84, 'h' => 0.40],
                ],
            ],
            [
                'nome'   => 'Una grande e due piccole',
                'ordine' => 30,
                'slots'  => [
                    ['ordine' => 1, 'x' => 0.05, 'y' => 0.06, 'w' => 0.55, 'h' => 0.84],
                    ['ordine' => 2, 'x' => 0.64, 'y' => 0.06, 'w' => 0.31, 'h' => 0.40],
                    ['ordine' => 3, 'x' => 0.64, 'y' => 0.50, 'w' => 0.31, 'h' => 0.40],
                ],
            ],
            [
                'nome'   => 'Griglia quattro foto',
                'ordine' => 40,
                'slots'  => [
                    ['ordine' => 1, 'x' => 0.05, 'y' => 0.06, 'w' => 0.43, 'h' => 0.40],
                    ['ordine' => 2, 'x' => 0.52, 'y' => 0.06, 'w' => 0.43, 'h' => 0.40],
                    ['ordine' => 3, 'x' => 0.05, 'y' => 0.50, 'w' => 0.43, 'h' => 0.40],
                    ['ordine' => 4, 'x' => 0.52, 'y' => 0.50, 'w' => 0.43, 'h' => 0.40],
                ],
            ],
            [
                'nome'   => 'Mosaico cinque foto',
                'ordine' => 50,
                'slots'  => [
                    ['ordine' => 1, 'x' => 0.05, 'y' => 0.06, 'w' => 0.43, 'h' => 0.84],
                    ['ordine' => 2, 'x' => 0.52, 'y' => 0.06, 'w' => 0.20, 'h' => 0.405],
                    ['ordine' => 3, 'x' => 0.75, 'y' => 0.06, 'w' => 0.20, 'h' => 0.405],
                    ['ordine' => 4, 'x' => 0.52, 'y' => 0.495, 'w' => 0.20, 'h' => 0.405],
                    ['ordine' => 5, 'x' => 0.75, 'y' => 0.495, 'w' => 0.20, 'h' => 0.405],
                ],
            ],
            [
                'nome'   => 'Tre foto in fila',
                'ordine' => 31,
                'slots'  => [
                    ['ordine' => 1, 'x' => 0.05, 'y' => 0.08, 'w' => 0.28, 'h' => 0.72],
                    ['ordine' => 2, 'x' => 0.36, 'y' => 0.08, 'w' => 0.28, 'h' => 0.72],
                    ['ordine' => 3, 'x' => 0.67, 'y' => 0.08, 'w' => 0.28, 'h' => 0.72],
                ],
            ],
            [
                // Fascia orizzontale al centro pagina, non a piena altezza:
                // per foto panoramiche/di gruppo, diversa dalla griglia 2x2.
                'nome'   => 'Striscia di quattro',
                'ordine' => 41,
                'slots'  => [
                    ['ordine' => 1, 'x' => 0.05,  'y' => 0.30, 'w' => 0.206, 'h' => 0.40],
                    ['ordine' => 2, 'x' => 0.281, 'y' => 0.30, 'w' => 0.206, 'h' => 0.40],
                    ['ordine' => 3, 'x' => 0.512, 'y' => 0.30, 'w' => 0.206, 'h' => 0.40],
                    ['ordine' => 4, 'x' => 0.743, 'y' => 0.30, 'w' => 0.206, 'h' => 0.40],
                ],
            ],
            [
                'nome'   => 'Fascia larga e tre strette',
                'ordine' => 42,
                'slots'  => [
                    ['ordine' => 1, 'x' => 0.05, 'y' => 0.06, 'w' => 0.90, 'h' => 0.40],
                    ['ordine' => 2, 'x' => 0.05, 'y' => 0.50, 'w' => 0.28, 'h' => 0.30],
                    ['ordine' => 3, 'x' => 0.36, 'y' => 0.50, 'w' => 0.28, 'h' => 0.30],
                    ['ordine' => 4, 'x' => 0.67, 'y' => 0.50, 'w' => 0.28, 'h' => 0.30],
                ],
            ],
            [
                'nome'   => 'Una grande e tre strette laterali',
                'ordine' => 43,
                'slots'  => [
                    ['ordine' => 1, 'x' => 0.05, 'y' => 0.06, 'w' => 0.55, 'h' => 0.84],
                    ['ordine' => 2, 'x' => 0.64, 'y' => 0.06, 'w' => 0.31, 'h' => 0.26],
                    ['ordine' => 3, 'x' => 0.64, 'y' => 0.35, 'w' => 0.31, 'h' => 0.26],
                    ['ordine' => 4, 'x' => 0.64, 'y' => 0.64, 'w' => 0.31, 'h' => 0.26],
                ],
            ],
            [
                'nome'   => 'Griglia sei foto',
                'ordine' => 60,
                'slots'  => [
                    ['ordine' => 1, 'x' => 0.05, 'y' => 0.06,  'w' => 0.28, 'h' => 0.405],
                    ['ordine' => 2, 'x' => 0.36, 'y' => 0.06,  'w' => 0.28, 'h' => 0.405],
                    ['ordine' => 3, 'x' => 0.67, 'y' => 0.06,  'w' => 0.28, 'h' => 0.405],
                    ['ordine' => 4, 'x' => 0.05, 'y' => 0.495, 'w' => 0.28, 'h' => 0.405],
                    ['ordine' => 5, 'x' => 0.36, 'y' => 0.495, 'w' => 0.28, 'h' => 0.405],
                    ['ordine' => 6, 'x' => 0.67, 'y' => 0.495, 'w' => 0.28, 'h' => 0.405],
                ],
            ],
            [
                'nome'   => 'Due grandi e quattro piccole',
                'ordine' => 61,
                'slots'  => [
                    ['ordine' => 1, 'x' => 0.05,  'y' => 0.06, 'w' => 0.43,  'h' => 0.42],
                    ['ordine' => 2, 'x' => 0.52,  'y' => 0.06, 'w' => 0.43,  'h' => 0.42],
                    ['ordine' => 3, 'x' => 0.05,  'y' => 0.51, 'w' => 0.206, 'h' => 0.30],
                    ['ordine' => 4, 'x' => 0.281, 'y' => 0.51, 'w' => 0.206, 'h' => 0.30],
                    ['ordine' => 5, 'x' => 0.512, 'y' => 0.51, 'w' => 0.206, 'h' => 0.30],
                    ['ordine' => 6, 'x' => 0.743, 'y' => 0.51, 'w' => 0.206, 'h' => 0.30],
                ],
            ],
            [
                'nome'   => 'Fascia con griglia sotto',
                'ordine' => 70,
                'slots'  => [
                    ['ordine' => 1, 'x' => 0.05, 'y' => 0.06, 'w' => 0.90, 'h' => 0.30],
                    ['ordine' => 2, 'x' => 0.05, 'y' => 0.39, 'w' => 0.28, 'h' => 0.24],
                    ['ordine' => 3, 'x' => 0.36, 'y' => 0.39, 'w' => 0.28, 'h' => 0.24],
                    ['ordine' => 4, 'x' => 0.67, 'y' => 0.39, 'w' => 0.28, 'h' => 0.24],
                    ['ordine' => 5, 'x' => 0.05, 'y' => 0.66, 'w' => 0.28, 'h' => 0.24],
                    ['ordine' => 6, 'x' => 0.36, 'y' => 0.66, 'w' => 0.28, 'h' => 0.24],
                    ['ordine' => 7, 'x' => 0.67, 'y' => 0.66, 'w' => 0.28, 'h' => 0.24],
                ],
            ],
            [
                'nome'   => 'Mosaico otto foto',
                'ordine' => 80,
                'slots'  => [
                    ['ordine' => 1, 'x' => 0.05,  'y' => 0.06,  'w' => 0.206, 'h' => 0.407],
                    ['ordine' => 2, 'x' => 0.281, 'y' => 0.06,  'w' => 0.206, 'h' => 0.407],
                    ['ordine' => 3, 'x' => 0.512, 'y' => 0.06,  'w' => 0.206, 'h' => 0.407],
                    ['ordine' => 4, 'x' => 0.743, 'y' => 0.06,  'w' => 0.206, 'h' => 0.407],
                    ['ordine' => 5, 'x' => 0.05,  'y' => 0.492, 'w' => 0.206, 'h' => 0.407],
                    ['ordine' => 6, 'x' => 0.281, 'y' => 0.492, 'w' => 0.206, 'h' => 0.407],
                    ['ordine' => 7, 'x' => 0.512, 'y' => 0.492, 'w' => 0.206, 'h' => 0.407],
                    ['ordine' => 8, 'x' => 0.743, 'y' => 0.492, 'w' => 0.206, 'h' => 0.407],
                ],
            ],
        ];
    }
}
