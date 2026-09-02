<?php

namespace Modules\VideoBook\Servizi;

use Illuminate\Support\Facades\Storage;
use Modules\VideoBook\Models\PaginaTemplate;

/**
 * Anteprima SVG di un layout: i riquadri del template disegnati come
 * segnaposto numerati, nessuna foto vera dentro — un template condiviso da
 * tutti non deve contenere l'immagine di nessuno in particolare (stesso
 * principio dei predefiniti di RicordinoTemplateSeeder, che salvano solo
 * segnaposto).
 *
 * SVG e non un raster: per un disegno di soli rettangoli resta nitido a
 * qualunque dimensione mostrata nella card, pesa pochi byte, ed è
 * self-hosted come il resto degli asset del progetto (nessuna chiamata
 * esterna, stesso vincolo GDPR di GeneratoreQrVideo).
 */
class GeneratoreAnteprimaTemplate
{
    private const LARGHEZZA = 400;

    private const ALTEZZA = 300;

    private const PANNA = '#faf6ec';

    private const RIQUADRO = '#efe6d0';

    private const ORO = '#c2a35a';

    private const ORO_SCURO = '#a5863f';

    private const CARTELLA = 'videobook/template-anteprime';

    /** Il markup SVG del layout, pronto per essere salvato o servito inline. */
    public function svg(PaginaTemplate $template): string
    {
        $w = self::LARGHEZZA;
        $h = self::ALTEZZA;
        $bordo = 4;
        $innerW = $w - 2 * $bordo;
        $innerH = $h - 2 * $bordo;
        $panna = self::PANNA;
        $oroScuro = self::ORO_SCURO;

        $riquadri = collect($template->slots)
            ->sortBy('ordine')
            ->map(fn (array $slot) => $this->riquadro($slot, $w, $h))
            ->implode("\n");

        return <<<SVG
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$w} {$h}" width="{$w}" height="{$h}">
                <rect x="0" y="0" width="{$w}" height="{$h}" fill="{$panna}" />
                <rect x="{$bordo}" y="{$bordo}" width="{$innerW}" height="{$innerH}"
                      fill="none" stroke="{$oroScuro}" stroke-width="1" opacity="0.35" />
                {$riquadri}
            </svg>
            SVG;
    }

    private function riquadro(array $slot, int $w, int $h): string
    {
        $x = round($slot['x'] * $w, 1);
        $y = round($slot['y'] * $h, 1);
        $rw = round($slot['w'] * $w, 1);
        $rh = round($slot['h'] * $h, 1);
        $cx = round($x + $rw / 2, 1);
        $cy = round($y + $rh / 2, 1);
        $numero = $slot['ordine'];
        $riquadroColore = self::RIQUADRO;
        $oro = self::ORO;
        $oroScuro = self::ORO_SCURO;

        return <<<SVG
            <rect x="{$x}" y="{$y}" width="{$rw}" height="{$rh}" rx="4"
                  fill="{$riquadroColore}" stroke="{$oro}" stroke-width="1.5" stroke-dasharray="6 4" />
            <text x="{$cx}" y="{$cy}" text-anchor="middle" dominant-baseline="middle"
                  font-family="sans-serif" font-size="16" fill="{$oroScuro}">{$numero}</text>
            SVG;
    }

    /** Genera e salva l'anteprima sul disco public, aggiornando il template. */
    public function salva(PaginaTemplate $template): string
    {
        $path = self::CARTELLA.'/'.$template->id.'.svg';

        Storage::disk('public')->put($path, $this->svg($template));
        $template->forceFill(['anteprima' => $path])->save();

        return $path;
    }
}
