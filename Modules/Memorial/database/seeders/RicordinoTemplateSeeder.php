<?php

namespace Modules\Memorial\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Memorial\Models\RicordinoTemplate;

/**
 * Template predefiniti MemorAI: le impaginazioni di partenza del Ricordino
 * Designer, così chi apre l'editor non parte dal foglio bianco.
 *
 * I layout sono descritti in coordinate RELATIVE (frazioni di larghezza e
 * altezza del foglio) e generati per ogni formato: la stessa impaginazione
 * vale per 7x10 e 6x9 senza duplicare i numeri.
 *
 * Come i template salvati dall'utente, contengono solo segnaposto: i testi
 * personali vengono riempiti dal designer col defunto corrente
 * (vedi testoPersonale / riempiConDefunto in ricordino-designer.blade.php).
 * Rilanciabile: aggiorna i predefiniti esistenti invece di duplicarli.
 */
class RicordinoTemplateSeeder extends Seeder
{
    /** Canvas interno = misure fisiche × SCALA (vedi FORMATI nel blade). */
    private const SCALA = 3.5;

    /** Formato => [larghezza, altezza] in px fisici a 96dpi. */
    private const FORMATI = [
        '7x10' => [265, 378],
        '6x9'  => [227, 340],
    ];

    private const ORO  = '#c2a35a';
    private const INK  = '#1a1a2e';
    private const FONT = 'Cormorant Garamond';

    public function run(): void
    {
        foreach ($this->layouts() as $layout) {
            // Le chiavi di FORMATI sono in ordine di presentazione: prima il
            // 7x10 standard, poi il 6x9. Lo scarto sul sort_order lo mantiene.
            foreach (array_keys(self::FORMATI) as $i => $formato) {
                RicordinoTemplate::updateOrCreate(
                    [
                        'nome'           => $layout['nome'],
                        'formato'        => $formato,
                        'is_predefinito' => true,
                    ],
                    [
                        'sort_order' => $layout['ordine'] + $i,
                        'fronte'     => $this->canvas($layout['fronte'], $formato),
                        'retro'      => $this->canvas($layout['retro'], $formato),
                    ],
                );
            }
        }

        // Quale layout usa lo Smart lo decide la dashboard. Qui ci limitiamo a
        // garantire che una scelta esista: se per un formato nessuno è ancora
        // segnato, vince il layout "Smart". Una scelta già fatta non si tocca.
        foreach (array_keys(self::FORMATI) as $formato) {
            $giaScelto = RicordinoTemplate::where('formato', $formato)
                ->where('is_smart_default', true)->exists();

            if (! $giaScelto) {
                RicordinoTemplate::where('formato', $formato)
                    ->where('nome', 'Smart')
                    ->update(['is_smart_default' => true]);
            }
        }
    }

    /**
     * I tre layout di partenza.
     *
     * y = distanza dal bordo alto in frazione di altezza; x e w in frazione di
     * larghezza; size = corpo del font nelle stesse unità usate da addBlock().
     */
    private function layouts(): array
    {
        return [
            [
                // Impaginazione del Designer Smart: sede della foto in alto
                // (riquadro invisibile, customType photo-slot), dati sotto.
                'nome'   => 'Smart',
                'ordine' => 5,
                'fronte' => [
                    ['foto',   'y' => 0.06, 'h' => 0.34, 'w' => 0.46, 'maschera' => 'ovale'],
                    ['blocco', 'nome',  'y' => 0.46, 'size' => 15, 'peso' => 'bold'],
                    ['blocco', 'date',  'y' => 0.63, 'size' => 9],
                    ['blocco', 'eta',   'y' => 0.69, 'size' => 10, 'stile' => 'italic'],
                    ['linea',  'y' => 0.76],
                    ['blocco', 'frase', 'y' => 0.80, 'size' => 9, 'stile' => 'italic'],
                ],
                'retro' => [
                    ['simbolo', 'y' => 0.10, 'char' => '✝', 'size' => 16],
                    ['blocco',  'preghiera', 'y' => 0.22, 'size' => 8, 'stile' => 'italic'],
                    ['simbolo', 'y' => 0.88, 'char' => '— ✦ —', 'size' => 10],
                ],
            ],
            [
                'nome'   => 'Classico',
                'ordine' => 10,
                // Croce in alto, nome in evidenza, date e frase sotto una filetta dorata.
                'fronte' => [
                    ['simbolo', 'y' => 0.09, 'char' => '✝', 'size' => 22],
                    ['blocco', 'nome',  'y' => 0.17, 'size' => 16, 'peso' => 'bold'],
                    ['blocco', 'date',  'y' => 0.35, 'size' => 9],
                    ['blocco', 'eta',   'y' => 0.41, 'size' => 11, 'stile' => 'italic'],
                    ['linea',  'y' => 0.49],
                    ['blocco', 'frase', 'y' => 0.53, 'size' => 9, 'stile' => 'italic'],
                ],
                'retro' => [
                    ['blocco',  'preghiera', 'y' => 0.20, 'size' => 8, 'stile' => 'italic'],
                    ['simbolo', 'y' => 0.82, 'char' => '— ✦ —', 'size' => 12],
                ],
            ],
            [
                'nome'   => 'Con foto',
                'ordine' => 20,
                // Metà superiore lasciata libera per la foto del defunto.
                'fronte' => [
                    ['blocco', 'nome',  'y' => 0.52, 'size' => 15, 'peso' => 'bold'],
                    ['blocco', 'date',  'y' => 0.68, 'size' => 9],
                    ['linea',  'y' => 0.75],
                    ['blocco', 'frase', 'y' => 0.79, 'size' => 9, 'stile' => 'italic'],
                ],
                'retro' => [
                    ['blocco',  'preghiera', 'y' => 0.17, 'size' => 8, 'stile' => 'italic'],
                    ['simbolo', 'y' => 0.84, 'char' => '✝', 'size' => 18],
                ],
            ],
            [
                'nome'   => 'Sobrio',
                'ordine' => 30,
                // Nessun simbolo: solo testo fra due filette.
                'fronte' => [
                    ['blocco', 'nome',  'y' => 0.24, 'size' => 16, 'peso' => 'bold'],
                    ['linea',  'y' => 0.40],
                    ['blocco', 'date',  'y' => 0.45, 'size' => 9],
                    ['blocco', 'eta',   'y' => 0.51, 'size' => 11, 'stile' => 'italic'],
                    ['linea',  'y' => 0.59],
                    ['blocco', 'frase', 'y' => 0.63, 'size' => 9, 'stile' => 'italic'],
                ],
                'retro' => [
                    ['blocco', 'preghiera', 'y' => 0.22, 'size' => 8, 'stile' => 'italic'],
                ],
            ],
        ];
    }

    /** Stato canvas Fabric per un lato, nel formato richiesto. */
    private function canvas(array $elementi, string $formato): array
    {
        [$larghezza, $altezza] = self::FORMATI[$formato];
        $w = $larghezza * self::SCALA;
        $h = $altezza * self::SCALA;

        return [
            'version' => '5.3.1',
            'objects' => array_map(fn (array $e) => $this->oggetto($e, $w, $h), $elementi),
        ];
    }

    private function oggetto(array $e, float $w, float $h): array
    {
        $top = round($e['y'] * $h, 2);

        // Filetta orizzontale decorativa.
        if ($e[0] === 'linea') {
            $lunghezza = round($w * 0.5, 2);

            return [
                'type'        => 'line',
                'version'     => '5.3.1',
                'left'        => round(($w - $lunghezza) / 2, 2),
                'top'         => $top,
                'width'       => $lunghezza,
                'height'      => 0,
                'x1'          => -$lunghezza / 2,
                'y1'          => 0,
                'x2'          => $lunghezza / 2,
                'y2'          => 0,
                'stroke'      => self::ORO,
                'strokeWidth' => 1,
            ];
        }

        // Sede della foto: riquadro invisibile che il Designer Smart legge per
        // sapere dove mettere lo scatto. Non selezionabile e trasparente, così
        // nel designer completo non dà fastidio a nessuno.
        if ($e[0] === 'foto') {
            $larghezza = round($w * $e['w'], 2);
            $altezza   = round($h * $e['h'], 2);

            return [
                'type'       => 'rect',
                'version'    => '5.3.1',
                'left'       => round(($w - $larghezza) / 2, 2),
                'top'        => $top,
                'width'      => $larghezza,
                'height'     => $altezza,
                'fill'       => 'rgba(0,0,0,0)',
                'opacity'    => 0,
                'selectable' => false,
                'evented'    => false,
                'customType' => 'photo-slot',
                'maschera'   => $e['maschera'] ?? null,
            ];
        }

        // Simbolo religioso: centrato orizzontalmente, come insertSimbolo().
        if ($e[0] === 'simbolo') {
            return [
                'type'       => 'text',
                'version'    => '5.3.1',
                'text'       => $e['char'],
                'left'       => round($w / 2, 2),
                'top'        => $top,
                'originX'    => 'center',
                'originY'    => 'center',
                'fontSize'   => round($e['size'] * self::SCALA, 2),
                'fontFamily' => 'serif',
                'fill'       => self::ORO,
                'editable'   => false,
                // Fabric sovrascrive il default con quello che trova nel JSON:
                // senza questa chiave i testi arrivano con styles undefined e
                // la successiva toJSON() del designer va in errore.
                'styles'     => [],
            ];
        }

        // Blocco di testo personale (nome, date, età, frase, preghiera).
        $tipo = $e[1];

        return [
            'type'       => 'textbox',
            'version'    => '5.3.1',
            'text'       => $this->segnaposto($tipo),
            'left'       => round($w * 0.06, 2),
            'top'        => $top,
            'width'      => round($w * 0.88, 2),
            'fontSize'   => round($e['size'] * self::SCALA, 2),
            'fontFamily' => self::FONT,
            'fontStyle'  => $e['stile'] ?? 'normal',
            'fontWeight' => $e['peso'] ?? 'normal',
            'textAlign'  => 'center',
            'fill'       => self::INK,
            'editable'   => true,
            'customType' => $tipo,
            'styles'     => [],
        ];
    }

    /**
     * Testo segnaposto di un blocco personale.
     * Deve restare allineato a testoPersonale(tipo, null) nel blade.
     */
    private function segnaposto(string $tipo): string
    {
        return match ($tipo) {
            'nome'      => "COGNOME\nNome",
            'eta'       => 'di anni ___',
            'date'      => '__/__/____ — __/__/____',
            'frase'     => "E mancato all'affetto dei suoi cari",
            'preghiera' => "Signore, accogli nella tua pace\nl'anima del tuo servo.\nAmen.",
            default     => '',
        };
    }
}
