<?php

namespace Modules\TributeVideo\Servizi;

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Common\Version;
use chillerlan\QRCode\Data\Byte;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

/**
 * QR pubblico del video memoriale: stile editoriale coerente con l'identità
 * MemorAI (oro-panna) invece del bianco/nero di endroid/qr-code (usato altrove
 * nel progetto, ma senza supporto nativo a moduli tondi/logo — vedi la libreria
 * scelta qui sotto).
 *
 * Punti fermi per restare scansionabile nonostante lo stile:
 * - ECC livello H (30%), obbligatorio per riservare spazio al logo centrale;
 * - i tre "occhi" di posizionamento restano ben distinguibili come quadrati
 *   (con angoli arrotondati per eleganza, mai un cerchio pieno: uno scanner
 *   li riconosce dal rapporto 1:1:3:1:1, un cerchio lo confonderebbe);
 * - solo i moduli dati diventano punti tondi.
 *
 * Verificato con uno scanner reale (zbarimg) prima e dopo l'arrotondamento
 * degli occhi, anche su un render ridotto a 300px.
 */
class GeneratoreQrVideo
{
    private const PANNA = [250, 246, 236];      // #faf6ec — sfondo, identico al logo

    private const ORO_SCURO = [165, 134, 63];   // #a5863f — punti dei dati

    private const CAFFE = [58, 46, 34];         // #3a2e22 — occhi e pattern strutturali

    private const RAGGIO_MODULI = 0.42;         // arrotondamento dei punti dati (0–0.5)

    private const SOVRACAMPIONAMENTO = 8;       // per angoli lisci sugli occhi arrotondati

    public function __construct(
        private readonly string $logoPath = '',
    ) {
    }

    /**
     * Genera il PNG del QR (stringa binaria) per l'URL dato.
     */
    public function png(string $url): string
    {
        $eccLevel = new EccLevel(EccLevel::H);

        // Passo 1: scopro quanti moduli servirà la matrice per questo URL,
        // per dimensionare lo spazio del logo in proporzione (un URL più
        // lungo/corto sceglie da solo una versione QR diversa).
        $probe = new QRCode(new QROptions(['eccLevel' => EccLevel::H]));
        $probe->addSegment(new Byte($url));
        $dimensione = $probe->getQRMatrix()->getVersion()->getDimension();

        $logoModuli = (int) floor($dimensione * 0.22);
        if ($logoModuli % 2 === 0) {
            $logoModuli++;
        }

        $quietzone = 4;

        $options = new QROptions([
            'version' => Version::AUTO,
            'eccLevel' => EccLevel::H,
            'outputType' => QROutputInterface::GDIMAGE_PNG,
            'scale' => 12,
            'quietzoneSize' => $quietzone,
            'returnResource' => true,
            'bgColor' => self::PANNA,
            'drawCircularModules' => true,
            'circleRadius' => self::RAGGIO_MODULI,
            'keepAsSquare' => [
                QRMatrix::M_FINDER,
                QRMatrix::M_FINDER_DARK,
                QRMatrix::M_FINDER_DOT,
                QRMatrix::M_FINDER_DOT_LIGHT,
                QRMatrix::M_ALIGNMENT,
                QRMatrix::M_ALIGNMENT_DARK,
            ],
            'moduleValues' => [
                QRMatrix::M_DATA_DARK => self::ORO_SCURO,
                QRMatrix::M_FINDER_DARK => self::CAFFE,
                QRMatrix::M_FINDER_DOT => self::CAFFE,
                QRMatrix::M_ALIGNMENT_DARK => self::CAFFE,
                QRMatrix::M_TIMING_DARK => self::CAFFE,
                QRMatrix::M_FORMAT_DARK => self::CAFFE,
                QRMatrix::M_VERSION_DARK => self::CAFFE,
                QRMatrix::M_DARKMODULE => self::CAFFE,
                QRMatrix::M_SEPARATOR_DARK => self::CAFFE,
                QRMatrix::M_QUIETZONE_DARK => self::CAFFE,
            ],
            'addLogoSpace' => true,
            'logoSpaceWidth' => $logoModuli,
            'logoSpaceHeight' => $logoModuli,
        ]);

        /** @var \GdImage $immagine */
        $immagine = (new QRCode($options))->render($url);

        $moduliTotali = $dimensione + (2 * $quietzone);
        $moduloPx = (int) round(imagesx($immagine) / $moduliTotali);

        $this->arrotondaOcchi($immagine, $dimensione, $quietzone, $moduloPx);
        $this->incollaLogo($immagine, $dimensione, $logoModuli, $moduloPx, $quietzone);

        ob_start();
        imagepng($immagine);
        $png = ob_get_clean();
        imagedestroy($immagine);

        return $png;
    }

    /**
     * Ridisegna i tre occhi di posizionamento (7x7 moduli, in alto a
     * sinistra/destra e in basso a sinistra) con angoli arrotondati, in
     * sovracampionamento per ottenere lo stesso livello di morbidezza dei
     * punti dati (chillerlan disegna già a bordi netti + resize, qui
     * ripetiamo la stessa tecnica solo sui tre occhi).
     */
    private function arrotondaOcchi($immagine, int $dimensione, int $quietzone, int $moduloPx): void
    {
        $latoOcchio = 7 * $moduloPx;

        $posizioni = [
            [$quietzone, $quietzone],                                   // alto sinistra
            [$quietzone + $dimensione - 7, $quietzone],                 // alto destra
            [$quietzone, $quietzone + $dimensione - 7],                 // basso sinistra
        ];

        foreach ($posizioni as [$moduloX, $moduloY]) {
            $occhio = $this->disegnaOcchioArrotondato($latoOcchio, $moduloPx);
            imagecopy($immagine, $occhio, $moduloX * $moduloPx, $moduloY * $moduloPx, 0, 0, $latoOcchio, $latoOcchio);
            imagedestroy($occhio);
        }
    }

    /**
     * Un occhio isolato (sfondo panna + bordo 7x7 + anello 5x5 + pupilla
     * 3x3), disegnato a scala 8x e poi ridotto per angoli lisci.
     */
    private function disegnaOcchioArrotondato(int $latoFinale, int $moduloPx)
    {
        $scala = self::SOVRACAMPIONAMENTO;
        $lato = $latoFinale * $scala;
        $modulo = $moduloPx * $scala;

        $super = imagecreatetruecolor($lato, $lato);
        $panna = imagecolorallocate($super, ...self::PANNA);
        imagefilledrectangle($super, 0, 0, $lato, $lato, $panna);

        $this->rettangoloArrotondato($super, 0, 0, $lato - 1, $lato - 1, (int) round($lato * 0.22), self::CAFFE);
        $this->rettangoloArrotondato($super, $modulo, $modulo, $lato - 1 - $modulo, $lato - 1 - $modulo, (int) round(($lato - 2 * $modulo) * 0.26), self::PANNA);
        $this->rettangoloArrotondato($super, 2 * $modulo, 2 * $modulo, $lato - 1 - 2 * $modulo, $lato - 1 - 2 * $modulo, (int) round(($lato - 4 * $modulo) * 0.30), self::CAFFE);

        $finale = imagecreatetruecolor($latoFinale, $latoFinale);
        imagecopyresampled($finale, $super, 0, 0, 0, 0, $latoFinale, $latoFinale, $lato, $lato);
        imagedestroy($super);

        return $finale;
    }

    /**
     * Rettangolo pieno con i quattro angoli arrotondati (croce + 4 cerchi
     * d'angolo — GD non ha un primitivo nativo per i rounded-rect).
     */
    private function rettangoloArrotondato($immagine, int $x0, int $y0, int $x1, int $y1, int $raggio, array $rgb): void
    {
        $colore = imagecolorallocate($immagine, ...$rgb);
        $raggio = max(0, min($raggio, (int) floor(min($x1 - $x0, $y1 - $y0) / 2)));

        imagefilledrectangle($immagine, $x0 + $raggio, $y0, $x1 - $raggio, $y1, $colore);
        imagefilledrectangle($immagine, $x0, $y0 + $raggio, $x1, $y1 - $raggio, $colore);

        imagefilledellipse($immagine, $x0 + $raggio, $y0 + $raggio, $raggio * 2, $raggio * 2, $colore);
        imagefilledellipse($immagine, $x1 - $raggio, $y0 + $raggio, $raggio * 2, $raggio * 2, $colore);
        imagefilledellipse($immagine, $x0 + $raggio, $y1 - $raggio, $raggio * 2, $raggio * 2, $colore);
        imagefilledellipse($immagine, $x1 - $raggio, $y1 - $raggio, $raggio * 2, $raggio * 2, $colore);
    }

    /**
     * Incolla l'emblema MemorAI (PNG trasparente) al centro dello spazio
     * riservato dalla matrice. Passa da una tela intermedia con alpha
     * preservato: imagecopyresampled diretto da un PNG trasparente su una
     * truecolor senza alpha lo trasforma in un riquadro nero (gotcha noto
     * di GD), va prima ridimensionato su una tela che mantiene la
     * trasparenza e solo poi incollato con imagecopy.
     */
    private function incollaLogo($immagine, int $dimensione, int $logoModuli, int $moduloPx, int $quietzone): void
    {
        $percorso = $this->logoPath !== '' ? $this->logoPath : public_path('img/memorai-emblem-qr.png');

        if (! is_file($percorso)) {
            return;
        }

        $logo = imagecreatefrompng($percorso);
        imagealphablending($logo, false);
        imagesavealpha($logo, true);

        $spazioPx = (int) round($logoModuli * $moduloPx * 0.92);
        $logoW = imagesx($logo);
        $logoH = imagesy($logo);
        $scala = min($spazioPx / $logoW, $spazioPx / $logoH);
        $destW = (int) round($logoW * $scala);
        $destH = (int) round($logoH * $scala);

        $ridimensionato = imagecreatetruecolor($destW, $destH);
        imagealphablending($ridimensionato, false);
        imagesavealpha($ridimensionato, true);
        $trasparente = imagecolorallocatealpha($ridimensionato, 0, 0, 0, 127);
        imagefill($ridimensionato, 0, 0, $trasparente);
        imagecopyresampled($ridimensionato, $logo, 0, 0, 0, 0, $destW, $destH, $logoW, $logoH);
        imagedestroy($logo);

        $canvas = imagesx($immagine);
        $destX = (int) round(($canvas - $destW) / 2);
        $destY = (int) round(($canvas - $destH) / 2);

        imagealphablending($immagine, true);
        imagecopy($immagine, $ridimensionato, $destX, $destY, 0, 0, $destW, $destH);
        imagedestroy($ridimensionato);
    }
}
