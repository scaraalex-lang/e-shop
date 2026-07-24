<?php

namespace Modules\Catalog\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductImage;
use Modules\Catalog\Models\AttributeDefinition;

/**
 * Popola il catalogo a partire dalle immagini normalizzate in
 * storage/app/public/products (importate con `php artisan catalog:import-images`).
 *
 * Le 72 immagini si riducono a 32 uniche e, accorpando le quasi-identiche,
 * a 17 articoli reali: 9 bracciali-rosario (sotto Rosari) e 8 rosari completi
 * a 5 poste (sotto Corone). Materiale e colore sono dedotti dall'ispezione
 * visiva delle immagini. Si aggiunge il kit trigesimo (senza immagine).
 *
 * Tono editoriale: bellezza e artigianato, mai lutto.
 */
class CatalogImageSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Il catalogo prodotti riflette esattamente questo set: azzero
            // prodotti e immagini (le categorie/attributi restano idempotenti).
            DB::table('product_images')->delete();
            Product::withTrashed()->forceDelete();

            $cat = $this->seedCategories();
            $this->seedAttributeDefinitions($cat);
            $this->seedProducts($cat);
        });
    }

    /**
     * @return array<string, Category>
     */
    private function seedCategories(): array
    {
        $trigesimali = Category::updateOrCreate(['slug' => 'articoli-trigesimali'], [
            'name' => 'Articoli trigesimali',
            'description' => 'Ricordini e allestimenti per il trigesimo: composizioni curate '
                . 'nei dettagli, per accompagnare un momento di raccoglimento con eleganza.',
            'sort_order' => 10, 'is_active' => true,
        ]);

        $devozionali = Category::updateOrCreate(['slug' => 'devozionali'], [
            'name' => 'Devozionali',
            'description' => 'Rosari, corone e croci lavorati con cura artigianale, in materiali '
                . 'nobili: piccoli oggetti di devozione da tenere fra le mani e tramandare.',
            'sort_order' => 20, 'is_active' => true,
        ]);

        $photoceramiche = Category::updateOrCreate(['slug' => 'photoceramiche'], [
            'name' => 'Photoceramiche',
            'description' => 'Ritratti su ceramica smaltata, cotti ad alta temperatura per durare '
                . 'nel tempo. Un ricordo luminoso, resistente alle intemperie.',
            'sort_order' => 30, 'is_active' => true,
        ]);

        $rosari = Category::updateOrCreate(['slug' => 'rosari'], [
            'parent_id' => $devozionali->id,
            'name' => 'Rosari',
            'description' => 'Bracciali-rosario e decine da polso in perla, cristallo, vetro e '
                . 'metallo, con crocifisso e medaglia rifiniti a mano.',
            'sort_order' => 10, 'is_active' => true,
        ]);

        $corone = Category::updateOrCreate(['slug' => 'corone'], [
            'parent_id' => $devozionali->id,
            'name' => 'Corone',
            'description' => 'Rosari completi a cinque poste, grani corposi e catena robusta, '
                . 'per la preghiera quotidiana. Perla, legno, vetro e cristallo.',
            'sort_order' => 20, 'is_active' => true,
        ]);

        $croci = Category::updateOrCreate(['slug' => 'croci'], [
            'parent_id' => $devozionali->id,
            'name' => 'Croci',
            'description' => 'Croci e crocifissi da parete e da tavolo, in legno d\'ulivo e metallo, '
                . 'di fattura artigianale.',
            'sort_order' => 30, 'is_active' => true,
        ]);

        return compact('trigesimali', 'devozionali', 'photoceramiche', 'rosari', 'corone', 'croci');
    }

    /**
     * @param array<string, Category> $c
     */
    private function seedAttributeDefinitions(array $c): void
    {
        // Set condiviso da Rosari e Corone (devozionali con grani).
        $grani = [
            ['key' => 'materiale_grani', 'label' => 'Materiale grani', 'type' => 'select',
             'options' => ['Perla', 'Legno', 'Vetro', 'Cristallo', 'Metallo'],
             'is_filterable' => true, 'is_required' => true, 'sort_order' => 10],
            ['key' => 'colore', 'label' => 'Colore', 'type' => 'select',
             'options' => ['Bianco', 'Avorio', 'Oro', 'Argento', 'Nero', 'Marrone', 'Ambra', 'Rosa', 'Rosso', 'Blu', 'Trasparente'],
             'is_filterable' => true, 'is_required' => true, 'sort_order' => 20],
            ['key' => 'tipo_crocifisso', 'label' => 'Tipo crocifisso', 'type' => 'select',
             'options' => ['Metallo argentato', 'Metallo dorato', 'Legno d\'ulivo', 'Smaltato'],
             'is_filterable' => true, 'is_required' => false, 'sort_order' => 30],
            ['key' => 'lunghezza', 'label' => 'Lunghezza', 'type' => 'select',
             'options' => ['Da polso', 'Corto (da tasca)', 'Medio', 'Lungo (da collo)'],
             'is_filterable' => true, 'is_required' => false, 'sort_order' => 40],
            ['key' => 'numero_poste', 'label' => 'Numero poste', 'type' => 'number',
             'options' => null,
             'is_filterable' => false, 'is_required' => false, 'sort_order' => 50],
        ];

        $defs = [
            $c['devozionali']->id => $grani,
            $c['rosari']->id      => $grani,
            $c['corone']->id      => $grani,

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
            ],

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
            ],

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
     * @param array<string, Category> $c
     */
    private function seedProducts(array $c): void
    {
        $rosari = $c['rosari']->id;
        $corone = $c['corone']->id;

        // Ogni voce: [immagine rappresentante] + dati prodotto.
        // 'img' = basename in storage/app/public/products (senza estensione).
        $products = [
            // ===================== BRACCIALI-ROSARIO → ROSARI =====================
            [
                'category_id' => $rosari, 'sku' => 'ROS-BRC-ARG-CUB',
                'slug' => 'bracciale-rosario-argento-grani-cubici',
                'name' => 'Bracciale rosario in metallo — grani cubici',
                'short_description' => 'Bracciale-rosario con grani cubici in metallo argentato e piccolo crocifisso.',
                'description' => 'Un bracciale-rosario dal disegno geometrico: grani cubici in metallo '
                    . 'argentato, sfaccettati per catturare la luce, uniti da una catena leggera. '
                    . 'Il piccolo crocifisso lo rende un oggetto di devozione discreto ed elegante, '
                    . 'da portare al polso ogni giorno.',
                'price' => 1490, 'material' => 'Metallo', 'color' => 'Argento',
                'attributes' => ['materiale_grani' => 'Metallo', 'colore' => 'Argento',
                    'tipo_crocifisso' => 'Metallo argentato', 'lunghezza' => 'Da polso', 'numero_poste' => 1],
                'img' => 'chatgpt-image-3-lug-2026-18-54-50-1', 'sort_order' => 10, 'stock' => 60,
            ],
            [
                'category_id' => $rosari, 'sku' => 'ROS-BRC-ARG-OLI',
                'slug' => 'bracciale-rosario-argento-grani-oliva',
                'name' => 'Bracciale rosario in metallo — grani a oliva',
                'short_description' => 'Bracciale-rosario con grani a oliva in metallo argentato e crocifisso.',
                'description' => 'Grani a forma di oliva in metallo argentato, morbidi e affusolati, '
                    . 'scorrono su una catena sottile. Un bracciale-rosario dalla linea classica e '
                    . 'senza tempo, rifinito con crocifisso a mano.',
                'price' => 1390, 'material' => 'Metallo', 'color' => 'Argento',
                'attributes' => ['materiale_grani' => 'Metallo', 'colore' => 'Argento',
                    'tipo_crocifisso' => 'Metallo argentato', 'lunghezza' => 'Da polso', 'numero_poste' => 1],
                'img' => 'chatgpt-image-3-lug-2026-18-54-51-2', 'sort_order' => 20, 'stock' => 60,
            ],
            [
                'category_id' => $rosari, 'sku' => 'ROS-BRC-ARG-TON',
                'slug' => 'bracciale-rosario-argento-grani-tondi',
                'name' => 'Bracciale rosario in metallo — grani tondi',
                'short_description' => 'Bracciale-rosario con grani tondi in metallo argentato e crocifisso.',
                'description' => 'Grani tondi e luminosi in metallo argentato, essenziali e discreti, '
                    . 'per un bracciale-rosario dal fascino sobrio. Leggero al polso, accompagna la '
                    . 'preghiera con semplicità.',
                'price' => 1290, 'material' => 'Metallo', 'color' => 'Argento',
                'attributes' => ['materiale_grani' => 'Metallo', 'colore' => 'Argento',
                    'tipo_crocifisso' => 'Metallo argentato', 'lunghezza' => 'Da polso', 'numero_poste' => 1],
                'img' => 'chatgpt-image-3-lug-2026-18-54-52-3', 'sort_order' => 30, 'stock' => 60,
            ],
            [
                'category_id' => $rosari, 'sku' => 'ROS-BRC-CRI-BLU',
                'slug' => 'bracciale-rosario-cristallo-blu',
                'name' => 'Bracciale rosario in cristallo blu',
                'short_description' => 'Bracciale-rosario con grani sfaccettati in cristallo blu e medaglia a rosa.',
                'description' => 'Grani sfaccettati in cristallo blu cobalto, vivi e brillanti, montati '
                    . 'su catena argentata. Una piccola medaglia a forma di rosa impreziosisce questo '
                    . 'bracciale-rosario dal colore intenso e prezioso.',
                'price' => 1890, 'material' => 'Cristallo', 'color' => 'Blu',
                'attributes' => ['materiale_grani' => 'Cristallo', 'colore' => 'Blu',
                    'tipo_crocifisso' => 'Metallo argentato', 'lunghezza' => 'Da polso', 'numero_poste' => 1],
                'img' => 'chatgpt-image-3-lug-2026-18-54-52-4', 'sort_order' => 40, 'stock' => 45,
            ],
            [
                'category_id' => $rosari, 'sku' => 'ROS-BRC-VTR-NER',
                'slug' => 'bracciale-rosario-grani-neri',
                'name' => 'Bracciale rosario in vetro nero',
                'short_description' => 'Bracciale-rosario con grani neri in vetro, medaglia miracolosa e crocifisso.',
                'description' => 'Grani in vetro nero dalla superficie lucida, sobri ed eleganti, '
                    . 'arricchiti da una medaglia miracolosa e da un crocifisso in metallo argentato. '
                    . 'Un bracciale-rosario dal carattere deciso e raffinato.',
                'price' => 1690, 'material' => 'Vetro', 'color' => 'Nero',
                'attributes' => ['materiale_grani' => 'Vetro', 'colore' => 'Nero',
                    'tipo_crocifisso' => 'Metallo argentato', 'lunghezza' => 'Da polso', 'numero_poste' => 1],
                'img' => 'chatgpt-image-3-lug-2026-18-54-53-5', 'sort_order' => 50, 'stock' => 50,
            ],
            [
                'category_id' => $rosari, 'sku' => 'ROS-BRC-CRI-TRA',
                'slug' => 'bracciale-rosario-cristallo-trasparente',
                'name' => 'Bracciale rosario in cristallo trasparente',
                'short_description' => 'Bracciale-rosario con grani in cristallo trasparente e crocifisso.',
                'description' => 'Grani in cristallo trasparente che giocano con la luce a ogni '
                    . 'movimento, montati su catena argentata. Un bracciale-rosario luminoso e '
                    . 'delicato, dal riflesso limpido e cristallino.',
                'price' => 1890, 'material' => 'Cristallo', 'color' => 'Trasparente',
                'attributes' => ['materiale_grani' => 'Cristallo', 'colore' => 'Trasparente',
                    'tipo_crocifisso' => 'Metallo argentato', 'lunghezza' => 'Da polso', 'numero_poste' => 1],
                'img' => 'chatgpt-image-3-lug-2026-18-54-53-6', 'sort_order' => 60, 'stock' => 45,
            ],
            [
                'category_id' => $rosari, 'sku' => 'ROS-DEC-AMB',
                'slug' => 'decina-da-polso-ambra',
                'name' => 'Decina da polso in vetro ambra',
                'short_description' => 'Decina in grani di vetro color ambra con medaglia a rosa.',
                'description' => 'Una decina dai grani in vetro color ambra, caldi e dorati come miele, '
                    . 'chiusa da una piccola rosa in metallo. Compatta e preziosa, sta in una tasca o '
                    . 'si porta al polso, sempre a portata di mano.',
                'price' => 1590, 'material' => 'Vetro', 'color' => 'Ambra',
                'attributes' => ['materiale_grani' => 'Vetro', 'colore' => 'Ambra',
                    'tipo_crocifisso' => 'Smaltato', 'lunghezza' => 'Corto (da tasca)', 'numero_poste' => 1],
                'img' => 'chatgpt-image-3-lug-2026-18-54-53-7', 'sort_order' => 70, 'stock' => 55,
            ],
            [
                'category_id' => $rosari, 'sku' => 'ROS-DEC-ROS',
                'slug' => 'decina-da-polso-rosa',
                'name' => 'Decina da polso in cristallo rosa',
                'short_description' => 'Decina in grani di cristallo rosa con medaglia a rosa.',
                'description' => 'Grani in cristallo rosa dai riflessi tenui e luminosi, riuniti in una '
                    . 'decina delicata e femminile, con una piccola rosa a chiusura. Un dettaglio '
                    . 'grazioso da tenere sempre con sé.',
                'price' => 1590, 'material' => 'Cristallo', 'color' => 'Rosa',
                'attributes' => ['materiale_grani' => 'Cristallo', 'colore' => 'Rosa',
                    'tipo_crocifisso' => 'Smaltato', 'lunghezza' => 'Corto (da tasca)', 'numero_poste' => 1],
                'img' => 'chatgpt-image-3-lug-2026-18-54-54-8', 'sort_order' => 80, 'stock' => 55,
            ],
            [
                'category_id' => $rosari, 'sku' => 'ROS-BRC-PRL-BIA',
                'slug' => 'bracciale-rosario-perla-bianca',
                'name' => 'Bracciale rosario in perla bianca',
                'short_description' => 'Bracciale-rosario con grani in perla bianca e crocifisso argentato.',
                'description' => 'Grani tondi in perla dalla luce bianca e calda, montati su catena '
                    . 'argentata con crocifisso rifinito a mano. Un bracciale-rosario luminoso e '
                    . 'sobrio, adatto a ogni occasione.',
                'price' => 1790, 'material' => 'Perla', 'color' => 'Bianco',
                'attributes' => ['materiale_grani' => 'Perla', 'colore' => 'Bianco',
                    'tipo_crocifisso' => 'Metallo argentato', 'lunghezza' => 'Da polso', 'numero_poste' => 1],
                'img' => 'chatgpt-image-3-lug-2026-19-20-48-1', 'sort_order' => 90, 'stock' => 60,
            ],

            // ===================== ROSARI COMPLETI (5 POSTE) → CORONE =====================
            [
                'category_id' => $corone, 'sku' => 'COR-MET-ORO',
                'slug' => 'rosario-metallo-dorato-roselline',
                'name' => 'Rosario in metallo dorato con roselline',
                'short_description' => 'Rosario completo a cinque poste in metallo dorato con roselline e crocifisso.',
                'description' => 'Un rosario completo interamente in metallo dorato: i grani sono '
                    . 'delicate roselline cesellate, uniti da una catena luminosa che culmina in un '
                    . 'crocifisso finemente lavorato. Un pezzo prezioso, da tramandare con orgoglio.',
                'price' => 3400, 'material' => 'Metallo', 'color' => 'Oro',
                'attributes' => ['materiale_grani' => 'Metallo', 'colore' => 'Oro',
                    'tipo_crocifisso' => 'Metallo dorato', 'lunghezza' => 'Lungo (da collo)', 'numero_poste' => 5],
                'img' => 'chatgpt-image-3-lug-2026-19-20-49-2', 'sort_order' => 10, 'stock' => 25,
            ],
            [
                'category_id' => $corone, 'sku' => 'COR-PRL-CHA',
                'slug' => 'rosario-perla-champagne-crocifisso-oro',
                'name' => 'Rosario in perla champagne con crocifisso dorato',
                'short_description' => 'Rosario completo a cinque poste in perla color champagne con finiture dorate.',
                'description' => 'Grani in perla dai riflessi champagne, caldi e dorati, montati con '
                    . 'crocifisso e crociera in metallo dorato. Un rosario dall\'eleganza sontuosa e '
                    . 'avvolgente, che unisce la morbidezza della perla al calore dell\'oro.',
                'price' => 3200, 'material' => 'Perla', 'color' => 'Oro',
                'attributes' => ['materiale_grani' => 'Perla', 'colore' => 'Oro',
                    'tipo_crocifisso' => 'Metallo dorato', 'lunghezza' => 'Lungo (da collo)', 'numero_poste' => 5],
                'img' => 'chatgpt-image-3-lug-2026-19-20-49-3', 'sort_order' => 20, 'stock' => 30,
            ],
            [
                'category_id' => $corone, 'sku' => 'COR-PRL-AVO-ORO',
                'slug' => 'rosario-perla-avorio-dettagli-oro',
                'name' => 'Rosario in perla avorio con dettagli dorati',
                'short_description' => 'Rosario completo a cinque poste in perla avorio con roselline e crocifisso dorati.',
                'description' => 'Grani in perla dal colore avorio caldo, scanditi da roselline dorate '
                    . 'e completati da un crocifisso in metallo dorato. Un rosario raffinato, dove il '
                    . 'candore della perla incontra il calore dei dettagli in oro.',
                'price' => 3300, 'material' => 'Perla', 'color' => 'Avorio',
                'attributes' => ['materiale_grani' => 'Perla', 'colore' => 'Avorio',
                    'tipo_crocifisso' => 'Metallo dorato', 'lunghezza' => 'Lungo (da collo)', 'numero_poste' => 5],
                'img' => 'chatgpt-image-3-lug-2026-19-26-16-2', 'sort_order' => 30, 'stock' => 30,
            ],
            [
                'category_id' => $corone, 'sku' => 'COR-VTR-ROS',
                'slug' => 'rosario-vetro-rosso-granato',
                'name' => 'Rosario in vetro rosso granato',
                'short_description' => 'Rosario completo a cinque poste in vetro rosso granato con crocifisso argentato.',
                'description' => 'Grani sfaccettati in vetro rosso granato, profondi e brillanti come '
                    . 'piccole gemme, montati su catena argentata con medaglia miracolosa e crocifisso. '
                    . 'Un rosario dal colore caldo e intenso, di grande carattere.',
                'price' => 2600, 'material' => 'Vetro', 'color' => 'Rosso',
                'attributes' => ['materiale_grani' => 'Vetro', 'colore' => 'Rosso',
                    'tipo_crocifisso' => 'Metallo argentato', 'lunghezza' => 'Lungo (da collo)', 'numero_poste' => 5],
                'img' => 'chatgpt-image-3-lug-2026-19-20-49-4', 'sort_order' => 40, 'stock' => 35,
            ],
            [
                'category_id' => $corone, 'sku' => 'COR-LEG-MAR',
                'slug' => 'rosario-legno-ulivo-marrone',
                'name' => 'Rosario in legno d\'ulivo',
                'short_description' => 'Rosario completo a cinque poste in legno d\'ulivo con crocifisso argentato.',
                'description' => 'Grani in autentico legno d\'ulivo, ognuno con venature uniche, '
                    . 'levigati a mano fino a diventare morbidi al tatto. Un rosario caldo e naturale, '
                    . 'leggero da portare, che profuma di artigianato mediterraneo.',
                'price' => 2200, 'material' => 'Legno', 'color' => 'Marrone',
                'attributes' => ['materiale_grani' => 'Legno', 'colore' => 'Marrone',
                    'tipo_crocifisso' => 'Metallo argentato', 'lunghezza' => 'Lungo (da collo)', 'numero_poste' => 5],
                'img' => 'chatgpt-image-3-lug-2026-19-20-50-5', 'sort_order' => 50, 'stock' => 40,
            ],
            [
                'category_id' => $corone, 'sku' => 'COR-PRL-BIA',
                'slug' => 'rosario-perla-bianca',
                'name' => 'Rosario in perla bianca',
                'short_description' => 'Rosario completo a cinque poste in perla bianca con crocifisso argentato.',
                'description' => 'Grani tondi in perla dalla luce bianca e delicata, montati su catena '
                    . 'argentata con crociera e crocifisso rifiniti a mano. Un rosario classico e '
                    . 'luminoso, di quelli che si custodiscono per la vita.',
                'price' => 2800, 'material' => 'Perla', 'color' => 'Bianco',
                'attributes' => ['materiale_grani' => 'Perla', 'colore' => 'Bianco',
                    'tipo_crocifisso' => 'Metallo argentato', 'lunghezza' => 'Lungo (da collo)', 'numero_poste' => 5],
                'img' => 'chatgpt-image-3-lug-2026-19-20-50-6', 'sort_order' => 60, 'stock' => 45,
            ],
            [
                'category_id' => $corone, 'sku' => 'COR-VTR-BLU',
                'slug' => 'rosario-vetro-blu-notte',
                'name' => 'Rosario in vetro blu notte',
                'short_description' => 'Rosario completo a cinque poste in vetro blu notte con crocifisso argentato.',
                'description' => 'Grani in vetro blu notte dai riflessi profondi, screziati di luce, '
                    . 'montati su catena argentata con medaglia e crocifisso. Un rosario dal colore '
                    . 'sereno e avvolgente, come un cielo di sera.',
                'price' => 2600, 'material' => 'Vetro', 'color' => 'Blu',
                'attributes' => ['materiale_grani' => 'Vetro', 'colore' => 'Blu',
                    'tipo_crocifisso' => 'Metallo argentato', 'lunghezza' => 'Lungo (da collo)', 'numero_poste' => 5],
                'img' => 'chatgpt-image-3-lug-2026-19-20-50-8', 'sort_order' => 70, 'stock' => 35,
            ],
            [
                'category_id' => $corone, 'sku' => 'COR-PRL-AVO-ARG',
                'slug' => 'rosario-perla-avorio-argento',
                'name' => 'Rosario in perla avorio con crocifisso argentato',
                'short_description' => 'Rosario completo a cinque poste in perla avorio con crocifisso argentato.',
                'description' => 'Grani in perla dal caldo colore avorio, montati su catena argentata '
                    . 'con crociera lavorata e crocifisso rifinito a mano. Un rosario dal tono morbido '
                    . 'e signorile, sobrio in ogni dettaglio.',
                'price' => 2800, 'material' => 'Perla', 'color' => 'Avorio',
                'attributes' => ['materiale_grani' => 'Perla', 'colore' => 'Avorio',
                    'tipo_crocifisso' => 'Metallo argentato', 'lunghezza' => 'Lungo (da collo)', 'numero_poste' => 5],
                'img' => 'chatgpt-image-3-lug-2026-19-26-18-7', 'sort_order' => 80, 'stock' => 45,
            ],

            // ===================== KIT TRIGESIMO (senza immagine) → TRIGESIMALI =====================
            [
                'category_id' => $c['trigesimali']->id, 'sku' => 'TRG-KIT-50',
                'slug' => 'kit-trigesimo-ricordini-50',
                'name' => 'Kit Trigesimo — 50 ricordini',
                'short_description' => 'Kit completo con 50 ricordini personalizzati con foto, pronti per il trigesimo.',
                'description' => 'Il Kit Trigesimo raccoglie 50 ricordini rifiniti su carta perlata, '
                    . 'personalizzati con la fotografia e le parole che preferite. La grafica è curata '
                    . 'come una piccola opera editoriale: cornici sottili, spazi ariosi e caratteri '
                    . 'eleganti. Oltre i 50 pezzi inclusi potete aggiungere ricordini extra al prezzo '
                    . 'unitario dedicato.',
                'price' => 9900, 'material' => null, 'color' => null,
                'attributes' => ['tipo_articolo' => 'Kit ricordini', 'allestimento' => 'Classico', 'formato' => 'Tascabile'],
                'is_kit' => true, 'included_units' => 50, 'extra_unit_price' => 120,
                'is_photo_printable' => true,
                'img' => null, 'sort_order' => 10, 'stock' => 999,
            ],
        ];

        foreach ($products as $data) {
            $img = $data['img'] ?? null;
            unset($data['img']);

            $product = Product::create($data + [
                'is_active' => true,
                'is_kit' => $data['is_kit'] ?? false,
                'is_photo_printable' => $data['is_photo_printable'] ?? false,
                'has_qr_memorial' => false,
                'is_configurable' => $data['is_kit'] ?? false,
            ]);

            if ($img) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'path'       => "products/{$img}.jpg",
                    'alt'        => $product->name,
                    'is_primary' => true,
                    'sort_order' => 0,
                ]);
            }
        }
    }
}
