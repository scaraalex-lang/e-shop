<?php

namespace Modules\Catalog\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

/**
 * Normalizza le immagini del catalogo per la vetrina.
 *
 * Legge una cartella sorgente (senza modificarne i file), produce per ogni
 * immagine una versione quadrata 1200x1200 su sfondo panna (#faf6ec) in JPG
 * qualità 85, più una miniatura 400x400. I file di destinazione hanno un nome
 * prevedibile derivato (slug) dal nome originale.
 */
class ImportImagesCommand extends Command
{
    protected $signature = 'catalog:import-images
        {source? : Cartella sorgente (default: "/Immagini catalogo")}
        {--fresh : Svuota products/ e thumbnails/ prima di importare}';

    protected $description = 'Normalizza le immagini del catalogo (1200x1200 + thumb 400x400, sfondo panna) in storage/app/public/products';

    /** Sfondo panna del design system MemorAI. */
    private string $panna = 'faf6ec';

    private int $box = 1200;
    private int $thumb = 400;
    private int $quality = 85;

    public function handle(): int
    {
        $source = rtrim($this->argument('source') ?: '/Immagini catalogo', '/');

        if (! is_dir($source)) {
            $this->error("Cartella sorgente non trovata: {$source}");
            return self::FAILURE;
        }

        $destDir  = storage_path('app/public/products');
        $thumbDir = $destDir . '/thumbnails';
        File::ensureDirectoryExists($destDir);
        File::ensureDirectoryExists($thumbDir);

        if ($this->option('fresh')) {
            foreach (array_merge(File::glob("{$destDir}/*.jpg"), File::glob("{$thumbDir}/*.jpg")) as $old) {
                File::delete($old);
            }
            $this->warn('Cartella di destinazione svuotata (--fresh).');
        }

        // Solo formati immagine supportati; ordine stabile per nome file.
        $files = collect(File::files($source))
            ->filter(fn ($f) => in_array(strtolower($f->getExtension()), ['png', 'jpg', 'jpeg', 'webp']))
            ->sortBy(fn ($f) => $f->getFilename(), SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        if ($files->isEmpty()) {
            $this->error('Nessuna immagine trovata nella cartella sorgente.');
            return self::FAILURE;
        }

        $this->info("Trovate {$files->count()} immagini in: {$source}");
        $this->line("Destinazione: {$destDir}");
        $this->newLine();

        $manager = new ImageManager(new Driver());
        $usati = [];       // slug -> conteggio, per evitare collisioni
        $righe = [];
        $errori = 0;

        $bar = $this->output->createProgressBar($files->count());
        $bar->start();

        foreach ($files as $file) {
            $slugBase = Str::slug(pathinfo($file->getFilename(), PATHINFO_FILENAME));
            $slugBase = $slugBase !== '' ? $slugBase : 'immagine';

            // garantisce univocità del nome di destinazione
            $slug = $slugBase;
            if (isset($usati[$slugBase])) {
                $slug = $slugBase . '-' . (++$usati[$slugBase]);
            } else {
                $usati[$slugBase] = 1;
            }

            try {
                $img = $manager->decodePath($file->getPathname());

                // 1200x1200 su sfondo panna: contain scala mantenendo le proporzioni
                // e riempie di panna sia i bordi sia eventuali zone trasparenti.
                $img->contain($this->box, $this->box, $this->panna);
                $img->save("{$destDir}/{$slug}.jpg", quality: $this->quality);

                // miniatura 400x400 (dalla versione già normalizzata)
                $img->contain($this->thumb, $this->thumb, $this->panna);
                $img->save("{$thumbDir}/{$slug}.jpg", quality: $this->quality);

                $kb = round(filesize("{$destDir}/{$slug}.jpg") / 1024);
                $righe[] = [$file->getFilename(), "{$slug}.jpg", "{$kb} KB"];
            } catch (\Throwable $e) {
                $errori++;
                $righe[] = [$file->getFilename(), '— ERRORE —', $e->getMessage()];
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(['Originale', 'Normalizzata', 'Peso / Nota'], $righe);

        $ok = $files->count() - $errori;
        $this->newLine();
        $this->info("Elaborate {$ok} immagini · {$errori} errori.");
        $this->line("JPG 1200x1200  → storage/app/public/products/");
        $this->line("Thumb 400x400  → storage/app/public/products/thumbnails/");

        return $errori > 0 ? self::FAILURE : self::SUCCESS;
    }
}
