<?php

namespace Modules\Catalog\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Modules\Catalog\Models\Category;

/**
 * Imposta l'immagine di copertina di una categoria.
 *
 * Copia l'immagine indicata in storage/app/public/categories/, la normalizza
 * a 800x800 quadrata (sfondo panna dove serve) in JPG qualità 85, aggiorna il
 * campo `image` della categoria e conferma a schermo. Serve finché non esiste
 * il pannello di amministrazione.
 */
class SetCategoryImageCommand extends Command
{
    protected $signature = 'catalog:set-category-image
        {slug : Slug della categoria (es. rosari)}
        {path : Percorso del file immagine sorgente}';

    protected $description = 'Imposta la copertina di una categoria: normalizza 800x800 jpg e aggiorna Category->image';

    private string $panna = 'faf6ec';
    private int $box = 800;
    private int $quality = 85;

    public function handle(): int
    {
        $slug = $this->argument('slug');
        $path = $this->argument('path');

        $category = Category::where('slug', $slug)->first();
        if (! $category) {
            $this->error("Categoria non trovata per slug: {$slug}");
            return self::FAILURE;
        }

        if (! is_file($path)) {
            $this->error("File immagine non trovato: {$path}");
            return self::FAILURE;
        }

        $destDir = storage_path('app/public/categories');
        File::ensureDirectoryExists($destDir);

        $relative = "categories/{$slug}.jpg";
        $target   = storage_path("app/public/{$relative}");

        try {
            $manager = new ImageManager(new Driver());
            $img = $manager->decodePath($path);
            // 800x800 quadrata: contain scala mantenendo le proporzioni e
            // riempie di panna bordi ed eventuali zone trasparenti.
            $img->contain($this->box, $this->box, $this->panna);
            $img->save($target, quality: $this->quality);
        } catch (\Throwable $e) {
            $this->error("Errore nell'elaborazione dell'immagine: {$e->getMessage()}");
            return self::FAILURE;
        }

        $category->update(['image' => $relative]);

        $kb = round(filesize($target) / 1024);
        $this->info("Copertina impostata per «{$category->name}» ({$slug}).");
        $this->line("File:   storage/app/public/{$relative}  ({$kb} KB, {$this->box}x{$this->box})");
        $this->line("URL:    " . asset('storage/' . $relative));
        $this->line("Campo:  categories.image = {$relative}");

        return self::SUCCESS;
    }
}
