<?php

namespace Database\Seeders;

use App\Models\HomeSlide;
use Illuminate\Database\Seeder;

/**
 * Slide iniziali del carosello di home.
 *
 * Sono le porte d'ingresso ai flussi: prenotazione ricordini (che passa dai
 * dati del defunto, poi Foto Manager e Designer), photoceramiche, devozionali.
 * Da qui in poi si modificano dalla dashboard operativa (/gestione/slide):
 * il seeder serve solo a partire con qualcosa di sensato.
 */
class HomeSlideSeeder extends Seeder
{
    public function run(): void
    {
        $slide = [
            [
                'occhiello'      => 'Ricordini trigesimali',
                'titolo'         => 'Il ricordino, parola per',
                'titolo_corsivo' => 'parola',
                'testo'          => 'Inserisci i dati della persona da ricordare: passi al Foto Manager per '
                                  . 'sistemare la fotografia e poi al Designer per comporre il ricordino.',
                'immagine'       => 'photoprint-demo/test-sacro-cuore.jpg',
                'immagine_alt'   => 'Ricordino personalizzato MemorAI',
                'cta_label'      => 'Prenota i ricordini',
                'cta_href'       => '/prenota/ricordino',
                'cta2_label'     => 'Vedi i trigesimali',
                'cta2_href'      => '/categoria/articoli-trigesimali',
                'sort_order'     => 10,
            ],
            [
                'occhiello'      => 'Photoceramiche',
                'titolo'         => 'Un volto che resta nel',
                'titolo_corsivo' => 'tempo',
                'testo'          => 'Ceramica smaltata e cottura ad alta temperatura: il ritratto non teme '
                                  . 'sole, gelo e pioggia. Formati ovali, tondi e rettangolari.',
                // Nessuna foto di photoceramica in archivio: meglio il segnaposto
                // panna che un rosario spacciato per ceramica. Si carica da
                // /gestione/slide appena c'è lo scatto giusto.
                'immagine'       => null,
                'immagine_alt'   => 'Photoceramica memoriale',
                'cta_label'      => 'Prenota la photoceramica',
                'cta_href'       => '/categoria/photoceramiche',
                'cta2_label'     => null,
                'cta2_href'      => null,
                'sort_order'     => 20,
            ],
            [
                'occhiello'      => 'Rosari e corone',
                'titolo'         => 'Piccoli oggetti da tenere fra le',
                'titolo_corsivo' => 'mani',
                'testo'          => 'Materiali nobili, lavorazione a mano, finiture curate. Da tramandare, '
                                  . 'non da riporre in un cassetto.',
                'immagine'       => 'categories/rosari.jpg',
                'immagine_alt'   => 'Rosari e corone MemorAI',
                'cta_label'      => 'Scopri i devozionali',
                'cta_href'       => '/categoria/devozionali',
                'cta2_label'     => null,
                'cta2_href'      => null,
                'sort_order'     => 30,
            ],
        ];

        foreach ($slide as $dati) {
            HomeSlide::updateOrCreate(
                ['titolo' => $dati['titolo']],
                $dati + ['is_active' => true],
            );
        }
    }
}
