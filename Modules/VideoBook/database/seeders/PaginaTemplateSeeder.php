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
 * Ogni layout è un ALBERO ('colonna'/'riga'/'foto', vedi Support\GrigliaPagina)
 * dentro un `area` di partenza — non più rettangoli x/y/w/h già pronti: è
 * così che le foto riempiono l'intera area assegnata (nessun margine
 * "sprecato" per una didascalia, eliminata) e il distacco tra due foto
 * affiancate resta GrigliaPagina::GAP_MM veri in stampa su qualunque
 * formato fisico scelto per il libro (15x15 come 38x36), invece di una
 * frazione congelata su un solo formato.
 *
 * `area` di default è l'intera pagina, [0,0,1,1] — un template riempie
 * bordo a bordo. Fa eccezione "Striscia di quattro": una fascia a metà
 * pagina è la sua composizione voluta (foto panoramiche/di gruppo, non una
 * griglia), quindi la sua `area` resta quella originale invece di
 * espandersi a piena pagina — solo il distacco fra le sue 4 foto diventa
 * comunque GAP_MM esatti.
 *
 * `ordine` dentro ogni foglia 'foto' è la chiave che lega il riquadro alla
 * foto caricata (FotoPagina::slot), non solo l'ordine visivo.
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
                    'numero_foto' => $this->contaFoto($layout['griglia']),
                    'slots'       => ['area' => $layout['area'] ?? [0, 0, 1, 1], 'nodo' => $layout['griglia']],
                    'sort_order'  => $layout['ordine'],
                ],
            );

            // Rigenerata ogni volta: se i pesi di un layout cambiano,
            // l'anteprima non deve restare disallineata dalla geometria vera.
            $generatore->salva($template);
        }
    }

    // ---- Costruttori dell'albero (vedi Support\GrigliaPagina) --------------

    private function foto(int $ordine): array
    {
        return ['tipo' => 'foto', 'ordine' => $ordine];
    }

    /** Figli affiancati in orizzontale: si dividono la larghezza, gap orizzontale tra loro. */
    private function colonna(array $figli): array
    {
        return ['tipo' => 'colonna', 'figli' => $figli];
    }

    /** Figli impilati in verticale: si dividono l'altezza, gap verticale tra loro. */
    private function riga(array $figli): array
    {
        return ['tipo' => 'riga', 'figli' => $figli];
    }

    /** Un figlio di 'colonna'/'riga': `peso` è la sua quota (relativa agli altri fratelli, non una frazione assoluta). */
    private function f(int $peso, array $nodo): array
    {
        return ['peso' => $peso, 'nodo' => $nodo];
    }

    private function contaFoto(array $nodo): int
    {
        if ($nodo['tipo'] === 'foto') {
            return 1;
        }

        return array_sum(array_map(fn (array $figlio) => $this->contaFoto($figlio['nodo']), $nodo['figli']));
    }

    /**
     * I layout di partenza. I secondi otto (da "Tre foto in fila" in poi)
     * prendono ispirazione dal foglio di riferimento caricato dall'utente
     * (griglie, fasce orizzontali, mosaici) — non una copia: geometria
     * ridisegnata da zero, il foglio era un pacchetto di template
     * commerciale (PSD/InDesign), non codice riusabile.
     */
    private function layouts(): array
    {
        return [
            [
                'nome'    => 'Foto intera',
                'ordine'  => 10,
                'griglia' => $this->foto(1),
            ],
            [
                'nome'    => 'Due foto affiancate',
                'ordine'  => 20,
                'griglia' => $this->colonna([
                    $this->f(1, $this->foto(1)),
                    $this->f(1, $this->foto(2)),
                ]),
            ],
            [
                'nome'    => 'Due foto in colonna',
                'ordine'  => 21,
                'griglia' => $this->riga([
                    $this->f(1, $this->foto(1)),
                    $this->f(1, $this->foto(2)),
                ]),
            ],
            [
                'nome'    => 'Una grande e due piccole',
                'ordine'  => 30,
                'griglia' => $this->colonna([
                    $this->f(64, $this->foto(1)),
                    $this->f(36, $this->riga([
                        $this->f(1, $this->foto(2)),
                        $this->f(1, $this->foto(3)),
                    ])),
                ]),
            ],
            [
                'nome'    => 'Griglia quattro foto',
                'ordine'  => 40,
                'griglia' => $this->riga([
                    $this->f(1, $this->colonna([$this->f(1, $this->foto(1)), $this->f(1, $this->foto(2))])),
                    $this->f(1, $this->colonna([$this->f(1, $this->foto(3)), $this->f(1, $this->foto(4))])),
                ]),
            ],
            [
                'nome'    => 'Mosaico cinque foto',
                'ordine'  => 50,
                'griglia' => $this->colonna([
                    $this->f(1, $this->foto(1)),
                    $this->f(1, $this->riga([
                        $this->f(1, $this->colonna([$this->f(1, $this->foto(2)), $this->f(1, $this->foto(3))])),
                        $this->f(1, $this->colonna([$this->f(1, $this->foto(4)), $this->f(1, $this->foto(5))])),
                    ])),
                ]),
            ],
            [
                'nome'    => 'Tre foto in fila',
                'ordine'  => 31,
                'griglia' => $this->colonna([
                    $this->f(1, $this->foto(1)),
                    $this->f(1, $this->foto(2)),
                    $this->f(1, $this->foto(3)),
                ]),
            ],
            [
                // Fascia orizzontale al centro pagina, non a piena altezza:
                // per foto panoramiche/di gruppo, diversa dalla griglia 2x2 —
                // l'unico layout la cui area resta apposta più piccola della
                // pagina intera, vedi il commento in testa al file.
                'nome'    => 'Striscia di quattro',
                'ordine'  => 41,
                'area'    => [0.05, 0.30, 0.949, 0.70],
                'griglia' => $this->colonna([
                    $this->f(1, $this->foto(1)),
                    $this->f(1, $this->foto(2)),
                    $this->f(1, $this->foto(3)),
                    $this->f(1, $this->foto(4)),
                ]),
            ],
            [
                'nome'    => 'Fascia larga e tre strette',
                'ordine'  => 42,
                'griglia' => $this->riga([
                    $this->f(4, $this->foto(1)),
                    $this->f(3, $this->colonna([
                        $this->f(1, $this->foto(2)),
                        $this->f(1, $this->foto(3)),
                        $this->f(1, $this->foto(4)),
                    ])),
                ]),
            ],
            [
                'nome'    => 'Una grande e tre strette laterali',
                'ordine'  => 43,
                'griglia' => $this->colonna([
                    $this->f(64, $this->foto(1)),
                    $this->f(36, $this->riga([
                        $this->f(1, $this->foto(2)),
                        $this->f(1, $this->foto(3)),
                        $this->f(1, $this->foto(4)),
                    ])),
                ]),
            ],
            [
                'nome'    => 'Griglia sei foto',
                'ordine'  => 60,
                'griglia' => $this->riga([
                    $this->f(1, $this->colonna([$this->f(1, $this->foto(1)), $this->f(1, $this->foto(2)), $this->f(1, $this->foto(3))])),
                    $this->f(1, $this->colonna([$this->f(1, $this->foto(4)), $this->f(1, $this->foto(5)), $this->f(1, $this->foto(6))])),
                ]),
            ],
            [
                'nome'    => 'Due grandi e quattro piccole',
                'ordine'  => 61,
                'griglia' => $this->riga([
                    $this->f(7, $this->colonna([$this->f(1, $this->foto(1)), $this->f(1, $this->foto(2))])),
                    $this->f(5, $this->colonna([
                        $this->f(1, $this->foto(3)),
                        $this->f(1, $this->foto(4)),
                        $this->f(1, $this->foto(5)),
                        $this->f(1, $this->foto(6)),
                    ])),
                ]),
            ],
            [
                'nome'    => 'Fascia con griglia sotto',
                'ordine'  => 70,
                'griglia' => $this->riga([
                    $this->f(5, $this->foto(1)),
                    $this->f(4, $this->colonna([$this->f(1, $this->foto(2)), $this->f(1, $this->foto(3)), $this->f(1, $this->foto(4))])),
                    $this->f(4, $this->colonna([$this->f(1, $this->foto(5)), $this->f(1, $this->foto(6)), $this->f(1, $this->foto(7))])),
                ]),
            ],
            [
                'nome'    => 'Mosaico otto foto',
                'ordine'  => 80,
                'griglia' => $this->riga([
                    $this->f(1, $this->colonna([
                        $this->f(1, $this->foto(1)), $this->f(1, $this->foto(2)), $this->f(1, $this->foto(3)), $this->f(1, $this->foto(4)),
                    ])),
                    $this->f(1, $this->colonna([
                        $this->f(1, $this->foto(5)), $this->f(1, $this->foto(6)), $this->f(1, $this->foto(7)), $this->f(1, $this->foto(8)),
                    ])),
                ]),
            ],
        ];
    }
}
