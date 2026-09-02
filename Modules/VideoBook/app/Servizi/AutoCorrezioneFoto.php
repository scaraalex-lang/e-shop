<?php

namespace Modules\VideoBook\Servizi;

/**
 * Auto-correzione classica (non IA) di una foto: analizza l'istogramma di
 * luminanza di un campione ridotto dell'immagine e suggerisce
 * luminosita/contrasto/saturazione — le stesse tre chiavi di StileTesto,
 * applicate con gli stessi filtri già in uso a schermo e in stampa (CSS/canvas
 * brightness-contrast-saturate, vedi filtroFotoCss() in editor.blade.php).
 * Non è un ritocco distruttivo sul file: il file originale non viene mai
 * riscritto, solo un punto di partenza migliore per gli stessi slider che
 * l'utente può comunque toccare a mano dopo — e sempre ripristinare (vedi
 * PaginaApiController::autoCorreggiFoto() e il bottone "Ripristina" del
 * pannello Strumenti → Foto).
 *
 * Perché un algoritmo e non un modello: per esposizione/contrasto uno
 * stretch dell'istogramma è deterministico, gratuito, istantaneo — un
 * modello aggiungerebbe costo/latenza/rete senza un vantaggio dimostrabile
 * per questo compito, tanto più su foto di persone care dove un risultato
 * imprevedibile pesa più di un errore prevedibile.
 */
class AutoCorrezioneFoto
{
    /** Lato del downscale usato solo per l'analisi: la risoluzione piena non serve a leggere un istogramma. */
    private const CAMPIONE = 120;

    /** Grigio medio di riferimento (0-255) verso cui portare la luminanza media. */
    private const LUMINANZA_TARGET = 128;

    /** @return array{luminosita:int,contrasto:int,saturazione:int} */
    public function analizza(string $percorsoFile): array
    {
        $default = ['luminosita' => 100, 'contrasto' => 100, 'saturazione' => 100];

        $tipo = @getimagesize($percorsoFile)[2] ?? null;
        $immagine = match ($tipo) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($percorsoFile),
            IMAGETYPE_PNG  => @imagecreatefrompng($percorsoFile),
            IMAGETYPE_WEBP => @imagecreatefromwebp($percorsoFile),
            default => null,
        };

        if (! $immagine) {
            return $default;
        }

        $campione = imagescale($immagine, self::CAMPIONE, self::CAMPIONE);
        imagedestroy($immagine);
        if (! $campione) {
            return $default;
        }
        imagepalettetotruecolor($campione);

        [$luminanze, $saturazioneMedia] = $this->campiona($campione);
        imagedestroy($campione);

        if (! $luminanze) {
            return $default;
        }

        sort($luminanze);
        $n = count($luminanze);
        $media = array_sum($luminanze) / $n;
        $p5 = $luminanze[(int) floor($n * 0.05)];
        $p95 = $luminanze[(int) floor($n * 0.95)];

        return [
            // Esposizione: porta la media verso il grigio di riferimento, mai oltre i limiti dello slider manuale (50-150).
            'luminosita' => $this->clamp((int) round(self::LUMINANZA_TARGET / max(1, $media) * 100)),
            // Contrasto: un istogramma stretto (foto piatta, es. una vecchia scansione sbiadita) chiede più contrasto.
            'contrasto' => $this->clamp((int) round(180 / max(1, $p95 - $p5) * 100)),
            // Saturazione: solo se davvero spenta (es. foto ingiallita) — un boost prudente, mai oltre, per non alterare gli incarnati.
            'saturazione' => $saturazioneMedia < 0.12 ? 130 : 100,
        ];
    }

    /** @return array{0: float[], 1: float} [luminanze per pixel, saturazione media 0-1] */
    private function campiona(\GdImage $campione): array
    {
        $w = imagesx($campione);
        $h = imagesy($campione);

        $luminanze = [];
        $saturazioneTot = 0.0;
        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $rgb = imagecolorat($campione, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $luminanze[] = 0.299 * $r + 0.587 * $g + 0.114 * $b;
                $max = max($r, $g, $b);
                $min = min($r, $g, $b);
                $saturazioneTot += $max > 0 ? ($max - $min) / $max : 0;
            }
        }

        return [$luminanze, $luminanze ? $saturazioneTot / count($luminanze) : 0];
    }

    private function clamp(int $valore): int
    {
        return max(50, min(150, $valore));
    }
}
