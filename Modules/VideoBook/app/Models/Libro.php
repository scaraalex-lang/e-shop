<?php

namespace Modules\VideoBook\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Modules\VideoBook\Enums\StatoLibro;

/**
 * Un libro/video tributo di un ordine: le sue pagine, in ordine.
 *
 * `ordine_id`/`defunto_id` senza vincolo — stesso pattern debole di
 * `foto_pratica.ordine_id` e di [[\Modules\TributeVideo\Models\VideoMemoriale]]:
 * VideoBook resta un modulo a sé, il legame nasce quando la lavorazione
 * dell'ordine lo aggancia.
 */
class Libro extends Model
{
    protected $table = 'videobook_progetti';

    protected $fillable = ['ordine_id', 'defunto_id', 'titolo', 'formato', 'stato', 'pdf_path'];

    protected $casts = [
        'stato' => StatoLibro::class,
    ];

    public function pagine(): HasMany
    {
        return $this->hasMany(Pagina::class, 'videobook_progetto_id')->orderBy('ordine');
    }

    public function video(): HasOne
    {
        return $this->hasOne(Video::class, 'libro_id');
    }

    /**
     * Le pagine da mettere nel video: quelle con almeno una foto, in ordine
     * — le pagine create dal passo "quante pagine" ma mai riempite non
     * devono comparire come scene vuote (decisione presa: "genera il video
     * delle sole pagine popolate").
     */
    public function paginePopolate(): Collection
    {
        return $this->pagine->filter(fn (Pagina $p) => $p->foto->isNotEmpty())->values();
    }

    /** Path relativo sul disco public: mai un URL assoluto, stesso schema di FotoPagina::url(). */
    public function pdfUrl(): ?string
    {
        return $this->pdf_path ? '/storage/'.ltrim($this->pdf_path, '/') : null;
    }

    public function completato(): bool
    {
        return $this->stato === StatoLibro::Completato;
    }

    public function segnaCompletato(): void
    {
        $this->forceFill(['stato' => StatoLibro::Completato])->save();
    }

    public function riportaInBozza(): void
    {
        $this->forceFill(['stato' => StatoLibro::Bozza])->save();
    }
}
