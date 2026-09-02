<?php

namespace Modules\VideoBook\Support;

/**
 * Risolve l'albero colonna/riga/foto di un PaginaTemplate (vedi
 * PaginaTemplateSeeder) in rettangoli concreti — {ordine, x, y, w, h},
 * frazioni 0-1 della pagina — per un formato fisico preciso.
 *
 * Il layout resta un unico albero valido per qualunque formato (15x15 come
 * 38x36): il distacco reale tra due foto affiancate (GAP_MM) è convertito
 * in frazione qui, in base al formato passato, invece di essere congelato
 * nei numeri del template. Così GAP_MM resta esatto in stampa su ogni
 * taglia, invece di un'approssimazione calibrata su un solo formato.
 *
 * Stessa identica logica in JS — vedi risolviSlot()/visitaNodoGriglia() in
 * editor.blade.php, che la usa per lo schermo e per il canvas del PDF; qui
 * serve solo lato server, per l'anteprima SVG del template
 * (GeneratoreAnteprimaTemplate).
 *
 * Un nodo è uno di:
 *   ['tipo' => 'foto', 'ordine' => int]
 *   ['tipo' => 'colonna'|'riga', 'figli' => [['peso' => number, 'nodo' => nodo], ...]]
 * 'colonna' affianca i figli in orizzontale (si dividono la larghezza, gap
 * orizzontale); 'riga' li impila in verticale (si dividono l'altezza, gap
 * verticale). `area` è il rettangolo di partenza (default l'intera pagina,
 * [0,0,1,1]) — un template "a fascia" che non deve riempire tutta la pagina
 * (vedi "Striscia di quattro" nel seeder) parte da un'area più piccola.
 */
class GrigliaPagina
{
    public const GAP_MM = 4;

    /** @return array<int, array{ordine:int,x:float,y:float,w:float,h:float}> */
    public static function risolvi(array $template, float $larghezzaMm, float $altezzaMm): array
    {
        $area = $template['area'] ?? [0, 0, 1, 1];
        $gapX = self::GAP_MM / $larghezzaMm;
        $gapY = self::GAP_MM / $altezzaMm;

        $slots = [];
        self::visita($template['nodo'], $area, $gapX, $gapY, $slots);

        return $slots;
    }

    /** @param array{0:float,1:float,2:float,3:float} $box [x0, y0, x1, y1] */
    private static function visita(array $nodo, array $box, float $gapX, float $gapY, array &$slots): void
    {
        [$x0, $y0, $x1, $y1] = $box;

        if ($nodo['tipo'] === 'foto') {
            $slots[] = ['ordine' => $nodo['ordine'], 'x' => $x0, 'y' => $y0, 'w' => $x1 - $x0, 'h' => $y1 - $y0];

            return;
        }

        $figli = $nodo['figli'];
        $pesoTotale = array_sum(array_column($figli, 'peso'));
        $orizzontale = $nodo['tipo'] === 'colonna';
        $gap = $orizzontale ? $gapX : $gapY;
        $disponibile = ($orizzontale ? $x1 - $x0 : $y1 - $y0) - $gap * (count($figli) - 1);

        $cursore = $orizzontale ? $x0 : $y0;
        foreach ($figli as $figlio) {
            $misura = $disponibile * ($figlio['peso'] / $pesoTotale);
            $subBox = $orizzontale
                ? [$cursore, $y0, $cursore + $misura, $y1]
                : [$x0, $cursore, $x1, $cursore + $misura];
            self::visita($figlio['nodo'], $subBox, $gapX, $gapY, $slots);
            $cursore += $misura + $gap;
        }
    }
}
