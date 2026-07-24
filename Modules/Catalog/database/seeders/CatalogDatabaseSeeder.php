<?php

namespace Modules\Catalog\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\AttributeDefinition;

class CatalogDatabaseSeeder extends Seeder
{
    /**
     * Popola il catalogo con dati realistici per il mercato italiano:
     * categorie, definizioni attributi (con flag filtrabili per la vetrina)
     * e un primo assortimento di prodotti.
     *
     * Tono editoriale: bellezza e artigianato, mai lutto.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $categories = $this->seedCategories();
            $this->seedAttributeDefinitions($categories);
            $this->seedProducts($categories);
        });
    }

    /**
     * Categorie radice + sottocategorie dei Devozionali.
     *
     * @return array<string, Category>
     */
    private function seedCategories(): array
    {
        $trigesimali = Category::updateOrCreate(
            ['slug' => 'articoli-trigesimali'],
            [
                'name'        => 'Articoli trigesimali',
                'description' => 'Ricordini e allestimenti per il trigesimo: '
                    . 'composizioni curate nei dettagli, pensate per accompagnare '
                    . 'un momento di raccoglimento con eleganza e misura.',
                'sort_order'  => 10,
                'is_active'   => true,
            ]
        );

        $devozionali = Category::updateOrCreate(
            ['slug' => 'devozionali'],
            [
                'name'        => 'Devozionali',
                'description' => 'Rosari, corone e croci lavorati con cura '
                    . 'artigianale, in materiali nobili: piccoli oggetti di '
                    . 'devozione da tenere fra le mani e tramandare.',
                'sort_order'  => 20,
                'is_active'   => true,
            ]
        );

        $photoceramiche = Category::updateOrCreate(
            ['slug' => 'photoceramiche'],
            [
                'name'        => 'Photoceramiche',
                'description' => 'Ritratti su ceramica smaltata, stampati con '
                    . 'tecnica ad alta definizione e cotti per durare nel tempo. '
                    . 'Un ricordo luminoso, resistente alle intemperie.',
                'sort_order'  => 30,
                'is_active'   => true,
            ]
        );

        $rosari = Category::updateOrCreate(
            ['slug' => 'rosari'],
            [
                'parent_id'   => $devozionali->id,
                'name'        => 'Rosari',
                'description' => 'Rosari a cinque poste in perla, legno, vetro e '
                    . 'cristallo, con crocifisso e crociera rifiniti a mano.',
                'sort_order'  => 10,
                'is_active'   => true,
            ]
        );

        $corone = Category::updateOrCreate(
            ['slug' => 'corone'],
            [
                'parent_id'   => $devozionali->id,
                'name'        => 'Corone',
                'description' => 'Corone del Rosario complete a quindici poste, '
                    . 'grani corposi e catena robusta, per la preghiera quotidiana.',
                'sort_order'  => 20,
                'is_active'   => true,
            ]
        );

        $croci = Category::updateOrCreate(
            ['slug' => 'croci'],
            [
                'parent_id'   => $devozionali->id,
                'name'        => 'Croci',
                'description' => 'Croci e crocifissi da parete e da tavolo, in '
                    . 'legno d\'ulivo e metallo, di fattura artigianale.',
                'sort_order'  => 30,
                'is_active'   => true,
            ]
        );

        return compact(
            'trigesimali', 'devozionali', 'photoceramiche',
            'rosari', 'corone', 'croci'
        );
    }

    /**
     * Definizioni attributi per categoria. Solo i campi utili in vetrina
     * sono marcati is_filterable = true.
     *
     * @param array<string, Category> $c
     */
    private function seedAttributeDefinitions(array $c): void
    {
        $defs = [
            // --- Articoli trigesimali ---
            $c['trigesimali']->id => [
                ['key' => 'tipo_articolo', 'label' => 'Tipo articolo', 'type' => 'select',
                 'options' => ['Kit ricordini', 'Ricordino singolo', 'Segnalibro', 'Immagine sacra'],
                 'is_filterable' => true, 'is_required' => true, 'sort_order' => 10],
                ['key' => 'allestimento', 'label' => 'Allestimento', 'type' => 'select',
                 'options' => ['Classico', 'Floreale', 'Minimale', 'Vintage'],
                 'is_filterable' => true, 'is_required' => false, 'sort_order' => 20],
                ['key' => 'formato', 'label' => 'Formato', 'type' => 'select',
                 'options' => ['Tascabile', 'Cartolina', 'A5'],
                 'is_filterable' => true, 'is_required' => false, 'sort_order' => 30],
                ['key' => 'finitura', 'label' => 'Finitura carta', 'type' => 'select',
                 'options' => ['Opaca', 'Lucida', 'Perlata'],
                 'is_filterable' => false, 'is_required' => false, 'sort_order' => 40],
            ],

            // --- Devozionali (categoria padre: attributi comuni) ---
            $c['devozionali']->id => [
                ['key' => 'materiale_grani', 'label' => 'Materiale grani', 'type' => 'select',
                 'options' => ['Perla', 'Legno', 'Vetro', 'Cristallo'],
                 'is_filterable' => true, 'is_required' => true, 'sort_order' => 10],
                ['key' => 'colore', 'label' => 'Colore', 'type' => 'select',
                 'options' => ['Bianco', 'Avorio', 'Nero', 'Marrone', 'Ambra', 'Trasparente', 'Rosa'],
                 'is_filterable' => true, 'is_required' => true, 'sort_order' => 20],
                ['key' => 'tipo_crocifisso', 'label' => 'Tipo crocifisso', 'type' => 'select',
                 'options' => ['Metallo argentato', 'Metallo dorato', 'Legno d\'ulivo', 'Smaltato'],
                 'is_filterable' => true, 'is_required' => false, 'sort_order' => 30],
                ['key' => 'lunghezza', 'label' => 'Lunghezza', 'type' => 'select',
                 'options' => ['Corto (da tasca)', 'Medio', 'Lungo (da collo)'],
                 'is_filterable' => true, 'is_required' => false, 'sort_order' => 40],
                ['key' => 'diametro_grano_mm', 'label' => 'Diametro grano (mm)', 'type' => 'number',
                 'options' => null,
                 'is_filterable' => false, 'is_required' => false, 'sort_order' => 50],
            ],

            // --- Rosari ---
            $c['rosari']->id => [
                ['key' => 'materiale_grani', 'label' => 'Materiale grani', 'type' => 'select',
                 'options' => ['Perla', 'Legno', 'Vetro', 'Cristallo'],
                 'is_filterable' => true, 'is_required' => true, 'sort_order' => 10],
                ['key' => 'colore', 'label' => 'Colore', 'type' => 'select',
                 'options' => ['Bianco', 'Avorio', 'Nero', 'Marrone', 'Ambra', 'Trasparente', 'Rosa'],
                 'is_filterable' => true, 'is_required' => true, 'sort_order' => 20],
                ['key' => 'tipo_crocifisso', 'label' => 'Tipo crocifisso', 'type' => 'select',
                 'options' => ['Metallo argentato', 'Metallo dorato', 'Legno d\'ulivo', 'Smaltato'],
                 'is_filterable' => true, 'is_required' => false, 'sort_order' => 30],
                ['key' => 'lunghezza', 'label' => 'Lunghezza', 'type' => 'select',
                 'options' => ['Corto (da tasca)', 'Medio', 'Lungo (da collo)'],
                 'is_filterable' => true, 'is_required' => false, 'sort_order' => 40],
                ['key' => 'numero_poste', 'label' => 'Numero poste', 'type' => 'number',
                 'options' => null,
                 'is_filterable' => false, 'is_required' => false, 'sort_order' => 50],
            ],

            // --- Corone ---
            $c['corone']->id => [
                ['key' => 'materiale_grani', 'label' => 'Materiale grani', 'type' => 'select',
                 'options' => ['Perla', 'Legno', 'Vetro', 'Cristallo'],
                 'is_filterable' => true, 'is_required' => true, 'sort_order' => 10],
                ['key' => 'colore', 'label' => 'Colore', 'type' => 'select',
                 'options' => ['Bianco', 'Avorio', 'Nero', 'Marrone', 'Ambra', 'Trasparente', 'Rosa'],
                 'is_filterable' => true, 'is_required' => true, 'sort_order' => 20],
                ['key' => 'tipo_crocifisso', 'label' => 'Tipo crocifisso', 'type' => 'select',
                 'options' => ['Metallo argentato', 'Metallo dorato', 'Legno d\'ulivo', 'Smaltato'],
                 'is_filterable' => true, 'is_required' => false, 'sort_order' => 30],
                ['key' => 'lunghezza', 'label' => 'Lunghezza', 'type' => 'select',
                 'options' => ['Medio', 'Lungo (da collo)'],
                 'is_filterable' => true, 'is_required' => false, 'sort_order' => 40],
            ],

            // --- Croci ---
            $c['croci']->id => [
                ['key' => 'materiale', 'label' => 'Materiale', 'type' => 'select',
                 'options' => ['Legno d\'ulivo', 'Legno di noce', 'Metallo', 'Ottone'],
                 'is_filterable' => true, 'is_required' => true, 'sort_order' => 10],
                ['key' => 'colore', 'label' => 'Colore', 'type' => 'select',
                 'options' => ['Naturale', 'Noce', 'Argento', 'Oro'],
                 'is_filterable' => true, 'is_required' => false, 'sort_order' => 20],
                ['key' => 'posizionamento', 'label' => 'Posizionamento', 'type' => 'select',
                 'options' => ['Da parete', 'Da tavolo', 'Da collo'],
                 'is_filterable' => true, 'is_required' => false, 'sort_order' => 30],
                ['key' => 'altezza_cm', 'label' => 'Altezza (cm)', 'type' => 'number',
                 'options' => null,
                 'is_filterable' => false, 'is_required' => false, 'sort_order' => 40],
            ],

            // --- Photoceramiche ---
            $c['photoceramiche']->id => [
                ['key' => 'forma', 'label' => 'Forma', 'type' => 'select',
                 'options' => ['Ovale', 'Rettangolare', 'Tonda', 'A cuore', 'Ogivale'],
                 'is_filterable' => true, 'is_required' => true, 'sort_order' => 10],
                ['key' => 'dimensione', 'label' => 'Dimensione', 'type' => 'select',
                 'options' => ['9x12 cm', '11x15 cm', '13x18 cm', '18x24 cm', 'Ø 8 cm', 'Ø 10 cm'],
                 'is_filterable' => true, 'is_required' => true, 'sort_order' => 20],
                ['key' => 'cornice', 'label' => 'Cornice', 'type' => 'select',
                 'options' => ['Senza cornice', 'Bronzo', 'Ottone lucido', 'Acciaio', 'Alluminio'],
                 'is_filterable' => true, 'is_required' => false, 'sort_order' => 30],
                ['key' => 'fissaggio', 'label' => 'Fissaggio', 'type' => 'select',
                 'options' => ['Con perni', 'Adesivo', 'Magnetico'],
                 'is_filterable' => false, 'is_required' => false, 'sort_order' => 40],
            ],
        ];

        foreach ($defs as $categoryId => $rows) {
            foreach ($rows as $row) {
                AttributeDefinition::updateOrCreate(
                    ['category_id' => $categoryId, 'key' => $row['key']],
                    $row + ['category_id' => $categoryId]
                );
            }
        }
    }

    /**
     * Assortimento iniziale di prodotti distribuiti fra le categorie.
     *
     * @param array<string, Category> $c
     */
    private function seedProducts(array $c): void
    {
        $products = [
            // ============ ARTICOLI TRIGESIMALI ============
            [
                'category_id'        => $c['trigesimali']->id,
                'sku'                => 'TRG-KIT-50',
                'slug'               => 'kit-trigesimo-ricordini-50',
                'name'               => 'Kit Trigesimo — 50 ricordini',
                'short_description'  => 'Kit completo con 50 ricordini personalizzati con foto, '
                    . 'pronti da distribuire nel giorno del trigesimo.',
                'description'        => 'Il Kit Trigesimo raccoglie 50 ricordini rifiniti su carta '
                    . 'perlata, personalizzati con la fotografia e le parole che preferite. '
                    . 'La grafica è curata come una piccola opera editoriale: cornici sottili, '
                    . 'spazi ariosi e caratteri eleganti. Oltre i 50 pezzi inclusi potete '
                    . 'aggiungere ricordini extra al prezzo unitario dedicato.',
                'price'              => 9900,   // 99,00 € base (include 50 pezzi)
                'compare_at_price'   => null,
                'material'           => null,
                'color'              => null,
                'attributes'         => [
                    'tipo_articolo' => 'Kit ricordini',
                    'allestimento'  => 'Classico',
                    'formato'       => 'Tascabile',
                    'finitura'      => 'Perlata',
                ],
                'is_configurable'    => true,
                'is_photo_printable' => true,
                'has_qr_memorial'    => false,
                'is_kit'             => true,
                'included_units'     => 50,
                'extra_unit_price'   => 120,    // 1,20 € a ricordino extra
                'stock'              => 999,
                'sort_order'         => 10,
            ],
            [
                'category_id'        => $c['trigesimali']->id,
                'sku'                => 'TRG-RIC-FLO',
                'slug'               => 'ricordino-floreale-cartolina',
                'name'               => 'Ricordino Floreale — formato cartolina',
                'short_description'  => 'Ricordino singolo con allestimento floreale vintage, '
                    . 'stampato su carta opaca in formato cartolina.',
                'description'        => 'Un ricordino dalla grafica floreale delicata, ispirata '
                    . 'alle illustrazioni botaniche d\'epoca. Personalizzabile con foto e testo, '
                    . 'è pensato per chi cerca un dettaglio raffinato e senza tempo.',
                'price'              => 180,    // 1,80 € al pezzo
                'compare_at_price'   => null,
                'material'           => null,
                'color'              => null,
                'attributes'         => [
                    'tipo_articolo' => 'Ricordino singolo',
                    'allestimento'  => 'Floreale',
                    'formato'       => 'Cartolina',
                    'finitura'      => 'Opaca',
                ],
                'is_configurable'    => true,
                'is_photo_printable' => true,
                'has_qr_memorial'    => false,
                'is_kit'             => false,
                'included_units'     => null,
                'extra_unit_price'   => null,
                'stock'              => 999,
                'sort_order'         => 20,
            ],

            // ============ ROSARI ============
            [
                'category_id'        => $c['rosari']->id,
                'sku'                => 'ROS-PRL-BIA',
                'slug'               => 'rosario-perla-bianco',
                'name'               => 'Rosario in Perla — bianco avorio',
                'short_description'  => 'Rosario a cinque poste con grani in perla di vetro, '
                    . 'crocifisso e crociera in metallo argentato.',
                'description'        => 'Grani tondi in perla di vetro dalla luce calda, montati '
                    . 'su catena argentata resistente. Il crocifisso, cesellato a mano, e la '
                    . 'crociera completano un rosario luminoso e delicato, da tenere sempre con sé.',
                'price'              => 2400,   // 24,00 €
                'compare_at_price'   => 2900,
                'material'           => 'Perla',
                'color'              => 'Bianco',
                'attributes'         => [
                    'materiale_grani'   => 'Perla',
                    'colore'            => 'Bianco',
                    'tipo_crocifisso'   => 'Metallo argentato',
                    'lunghezza'         => 'Medio',
                    'diametro_grano_mm' => 6,
                    'numero_poste'      => 5,
                ],
                'is_configurable'    => false,
                'is_photo_printable' => false,
                'has_qr_memorial'    => false,
                'is_kit'             => false,
                'included_units'     => null,
                'extra_unit_price'   => null,
                'stock'              => 40,
                'sort_order'         => 10,
            ],
            [
                'category_id'        => $c['rosari']->id,
                'sku'                => 'ROS-LEG-MAR',
                'slug'               => 'rosario-legno-ulivo',
                'name'               => 'Rosario in Legno d\'Ulivo',
                'short_description'  => 'Rosario a cinque poste con grani in legno d\'ulivo '
                    . 'e crocifisso in legno intagliato.',
                'description'        => 'Grani in autentico legno d\'ulivo, ognuno con venature '
                    . 'uniche, lavorati e levigati a mano. Un rosario dal tocco caldo e naturale, '
                    . 'leggero da portare, che profuma di artigianato mediterraneo.',
                'price'              => 1800,   // 18,00 €
                'compare_at_price'   => null,
                'material'           => 'Legno',
                'color'              => 'Marrone',
                'attributes'         => [
                    'materiale_grani'   => 'Legno',
                    'colore'            => 'Marrone',
                    'tipo_crocifisso'   => 'Legno d\'ulivo',
                    'lunghezza'         => 'Medio',
                    'diametro_grano_mm' => 8,
                    'numero_poste'      => 5,
                ],
                'is_configurable'    => false,
                'is_photo_printable' => false,
                'has_qr_memorial'    => false,
                'is_kit'             => false,
                'included_units'     => null,
                'extra_unit_price'   => null,
                'stock'              => 60,
                'sort_order'         => 20,
            ],
            [
                'category_id'        => $c['rosari']->id,
                'sku'                => 'ROS-CRI-TRA',
                'slug'               => 'rosario-cristallo-trasparente',
                'name'               => 'Rosario in Cristallo — trasparente',
                'short_description'  => 'Rosario a cinque poste con grani sfaccettati in '
                    . 'cristallo e finiture dorate.',
                'description'        => 'Grani sfaccettati in cristallo che catturano la luce a '
                    . 'ogni movimento, montati su catena dorata. Crocifisso e crociera in metallo '
                    . 'dorato completano un rosario prezioso, dal riflesso vivo e cristallino.',
                'price'              => 3600,   // 36,00 €
                'compare_at_price'   => null,
                'material'           => 'Cristallo',
                'color'              => 'Trasparente',
                'attributes'         => [
                    'materiale_grani'   => 'Cristallo',
                    'colore'            => 'Trasparente',
                    'tipo_crocifisso'   => 'Metallo dorato',
                    'lunghezza'         => 'Corto (da tasca)',
                    'diametro_grano_mm' => 6,
                    'numero_poste'      => 5,
                ],
                'is_configurable'    => false,
                'is_photo_printable' => false,
                'has_qr_memorial'    => false,
                'is_kit'             => false,
                'included_units'     => null,
                'extra_unit_price'   => null,
                'stock'              => 30,
                'sort_order'         => 30,
            ],

            // ============ CORONE ============
            [
                'category_id'        => $c['corone']->id,
                'sku'                => 'COR-PRL-AVO',
                'slug'               => 'corone-perla-avorio-quindici-poste',
                'name'               => 'Corona del Rosario in Perla — avorio',
                'short_description'  => 'Corona completa a quindici poste con grani in perla '
                    . 'avorio, catena argentata robusta.',
                'description'        => 'Una corona completa a quindici poste, con grani corposi '
                    . 'in perla dal colore avorio caldo. Pensata per la preghiera quotidiana, '
                    . 'unisce presenza e leggerezza grazie a una catena solida ma sottile.',
                'price'              => 4200,   // 42,00 €
                'compare_at_price'   => null,
                'material'           => 'Perla',
                'color'              => 'Avorio',
                'attributes'         => [
                    'materiale_grani' => 'Perla',
                    'colore'          => 'Avorio',
                    'tipo_crocifisso' => 'Metallo argentato',
                    'lunghezza'       => 'Lungo (da collo)',
                ],
                'is_configurable'    => false,
                'is_photo_printable' => false,
                'has_qr_memorial'    => false,
                'is_kit'             => false,
                'included_units'     => null,
                'extra_unit_price'   => null,
                'stock'              => 25,
                'sort_order'         => 10,
            ],
            [
                'category_id'        => $c['corone']->id,
                'sku'                => 'COR-LEG-NOC',
                'slug'               => 'corone-legno-noce-quindici-poste',
                'name'               => 'Corona del Rosario in Legno di Noce',
                'short_description'  => 'Corona a quindici poste con grani in legno di noce '
                    . 'e crocifisso in legno d\'ulivo.',
                'description'        => 'Grani generosi in legno di noce dalle tonalità profonde, '
                    . 'levigati a mano e infilati su catena resistente. Una corona calda e '
                    . 'sobria, che invecchia con grazia acquistando carattere nel tempo.',
                'price'              => 3200,   // 32,00 €
                'compare_at_price'   => null,
                'material'           => 'Legno',
                'color'              => 'Marrone',
                'attributes'         => [
                    'materiale_grani' => 'Legno',
                    'colore'          => 'Marrone',
                    'tipo_crocifisso' => 'Legno d\'ulivo',
                    'lunghezza'       => 'Lungo (da collo)',
                ],
                'is_configurable'    => false,
                'is_photo_printable' => false,
                'has_qr_memorial'    => false,
                'is_kit'             => false,
                'included_units'     => null,
                'extra_unit_price'   => null,
                'stock'              => 35,
                'sort_order'         => 20,
            ],

            // ============ CROCI ============
            [
                'category_id'        => $c['croci']->id,
                'sku'                => 'CRO-ULV-PAR',
                'slug'               => 'croce-ulivo-da-parete',
                'name'               => 'Croce in Legno d\'Ulivo — da parete',
                'short_description'  => 'Croce da parete in legno d\'ulivo massello, '
                    . 'lavorata e lucidata a mano.',
                'description'        => 'Una croce essenziale in legno d\'ulivo massello, dalle '
                    . 'venature vive e uniche. Le proporzioni pulite e la finitura satinata la '
                    . 'rendono un elemento sobrio ed elegante per la parete di casa.',
                'price'              => 2600,   // 26,00 €
                'compare_at_price'   => null,
                'material'           => 'Legno',
                'color'              => 'Naturale',
                'attributes'         => [
                    'materiale'      => 'Legno d\'ulivo',
                    'colore'         => 'Naturale',
                    'posizionamento' => 'Da parete',
                    'altezza_cm'     => 25,
                ],
                'is_configurable'    => false,
                'is_photo_printable' => false,
                'has_qr_memorial'    => false,
                'is_kit'             => false,
                'included_units'     => null,
                'extra_unit_price'   => null,
                'stock'              => 20,
                'sort_order'         => 10,
            ],

            // ============ PHOTOCERAMICHE ============
            [
                'category_id'        => $c['photoceramiche']->id,
                'sku'                => 'PHC-OVA-1318-QR',
                'slug'               => 'photoceramica-ovale-13x18-qr',
                'name'               => 'Photoceramica Ovale 13x18 — con QR Memorial',
                'short_description'  => 'Ritratto su ceramica ovale 13x18 cm con cornice in '
                    . 'bronzo e QR Memorial per la galleria dei ricordi.',
                'description'        => 'Ritratto stampato su ceramica smaltata in formato ovale '
                    . '13x18 cm, cotto ad alta temperatura per resistere a sole e intemperie. '
                    . 'La cornice in bronzo dona calore all\'immagine. Include il QR Memorial: '
                    . 'un piccolo codice che apre una galleria online di foto e parole, '
                    . 'da arricchire nel tempo.',
                'price'              => 5900,   // 59,00 €
                'compare_at_price'   => null,
                'material'           => 'Ceramica',
                'color'              => null,
                'attributes'         => [
                    'forma'      => 'Ovale',
                    'dimensione' => '13x18 cm',
                    'cornice'    => 'Bronzo',
                    'fissaggio'  => 'Con perni',
                ],
                'is_configurable'    => false,
                'is_photo_printable' => true,
                'has_qr_memorial'    => true,
                'is_kit'             => false,
                'included_units'     => null,
                'extra_unit_price'   => null,
                'stock'              => 100,
                'sort_order'         => 10,
            ],
            [
                'category_id'        => $c['photoceramiche']->id,
                'sku'                => 'PHC-RET-1824',
                'slug'               => 'photoceramica-rettangolare-18x24',
                'name'               => 'Photoceramica Rettangolare 18x24',
                'short_description'  => 'Ritratto su ceramica rettangolare 18x24 cm con '
                    . 'cornice in ottone lucido.',
                'description'        => 'Formato rettangolare generoso, 18x24 cm, per un ritratto '
                    . 'di grande presenza. La stampa ad alta definizione su ceramica smaltata '
                    . 'restituisce dettagli e sfumature naturali; la cornice in ottone lucido '
                    . 'incornicia l\'immagine con calore.',
                'price'              => 7400,   // 74,00 €
                'compare_at_price'   => null,
                'material'           => 'Ceramica',
                'color'              => null,
                'attributes'         => [
                    'forma'      => 'Rettangolare',
                    'dimensione' => '18x24 cm',
                    'cornice'    => 'Ottone lucido',
                    'fissaggio'  => 'Con perni',
                ],
                'is_configurable'    => false,
                'is_photo_printable' => true,
                'has_qr_memorial'    => false,
                'is_kit'             => false,
                'included_units'     => null,
                'extra_unit_price'   => null,
                'stock'              => 100,
                'sort_order'         => 20,
            ],
        ];

        foreach ($products as $data) {
            Product::updateOrCreate(
                ['sku' => $data['sku']],
                $data + ['is_active' => true]
            );
        }
    }
}
