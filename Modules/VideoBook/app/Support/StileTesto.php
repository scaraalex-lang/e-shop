<?php

namespace Modules\VideoBook\Support;

/**
 * Default e chiavi ammesse per la colonna JSON `stile` di FotoPagina
 * (didascalia + bordino/regolazione/viraggio della foto) e TestoPagina (box
 * di testo liberi) — un solo posto per non disallineare le due tabelle e la
 * validazione in PaginaApiController.
 *
 * Solo le chiavi diverse dal default sono salvate nella colonna (vedi
 * PaginaApiController::aggiornaStileFoto/aggiornaStileTesto): `effettivo()`
 * fa il merge con questi default a lettura, sia lato PHP (FotoPagina/
 * TestoPagina::stileEffettivo()) sia lato JS (stileEffettivo() in
 * editor.blade.php, stessa forma).
 */
class StileTesto
{
    public static function default(): array
    {
        return [
            // Testo (didascalia o contenuto del box)
            'font'          => 'Cormorant Garamond',
            'dimensione'    => 100,   // percentuale della dimensione base
            'allineamento'  => 'center',
            'grassetto'     => false,
            'sottolineato'  => false,
            'corsivo'       => true,
            'colore'        => '#1a1a2e',
            // Foto (ignorati su TestoPagina)
            'bordo'         => null, // null | bianco-sottile | oro-sottile | nero-sottile | bianco-spesso
            'luminosita'    => 100,  // percentuale, 50-150
            'contrasto'     => 100,
            'saturazione'   => 100,
            'viraggio'      => null, // null | seppia | bn | vintage | freddo
            // Box di testo (ignorati su FotoPagina)
            'sfondo_colore'  => '#1a1a2e',
            'sfondo_opacita' => 55,   // percentuale
        ];
    }

    /** Le sole chiavi valide: tutto il resto è scartato in fase di validazione/merge. */
    public static function chiavi(): array
    {
        return array_keys(self::default());
    }

    /**
     * I font selezionabili nel pannello Strumenti: tutti quelli già
     * self-hosted in /vendor/fonts/editor-fonts.css (Google Fonts, licenza
     * libera — nessuna chiamata a CDN esterni, stesso vincolo GDPR del
     * resto del progetto). Non il catalogo di Canva: molti dei font che
     * Canva propone nel piano gratuito sono comunque suoi asset con licenza
     * legata alla piattaforma, non file scaricabili e ridistribuibili da
     * un altro servizio — qui restiamo sui Google Fonts, che sono davvero
     * liberi da auto-ospitare.
     */
    public static function fontDisponibili(): array
    {
        return [
            'Cormorant Garamond', 'EB Garamond', 'Playfair Display', 'Lora',
            'Spectral', 'Crimson Text', 'Merriweather', 'Libre Baskerville',
            'Cinzel', 'Philosopher', 'GFS Didot', 'Goudy Bookletter 1911', 'UnifrakturMaguntia',
            'DM Sans', 'Inter', 'Dancing Script', 'Pinyon Script',
        ];
    }

    /** Gli stili di bordino selezionabili per una foto (null = nessuno). */
    public static function bordiDisponibili(): array
    {
        return ['bianco-sottile', 'oro-sottile', 'nero-sottile', 'bianco-spesso'];
    }

    /** I preset di viraggio selezionabili per una foto (null = nessuno). */
    public static function viraggiDisponibili(): array
    {
        return ['seppia', 'bn', 'vintage', 'freddo'];
    }

    /** Merge dei default con le sole chiavi valorizzate salvate sul record. */
    public static function effettivo(?array $stile): array
    {
        return array_merge(self::default(), array_intersect_key($stile ?? [], self::default()));
    }
}
