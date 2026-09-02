<?php

namespace Modules\VideoBook\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\VideoBook\Support\StileTesto;

/**
 * Cosa c'è in un riquadro di una pagina: una foto + la sua didascalia.
 *
 * `slot` combacia con l'`ordine` dentro `PaginaTemplate::slots`: è così che
 * si sa quale (x, y, w, h) del layout usare per questa foto, sia in stampa
 * (jsPDF) sia nel video — una scena per foto, non per pagina intera (Ken
 * Burns per singola foto dentro la pagina, come deciso per il render).
 *
 * `stile` (font/allineamento/bordino/regolazione/viraggio, pannello
 * "Strumenti" in editor.blade.php) vive qui e non su PaginaTemplate: è una
 * scelta della singola foto caricata, non del layout — vedi StileTesto per
 * le chiavi ammesse e i default.
 */
class FotoPagina extends Model
{
    protected $table = 'videobook_foto';

    protected $fillable = ['pagina_id', 'slot', 'path', 'scala', 'pos_x', 'pos_y', 'didascalia', 'durata_secondi', 'stile'];

    protected $casts = [
        'scala' => 'float',
        'pos_x' => 'float',
        'pos_y' => 'float',
        'stile' => 'array',
    ];

    // Uguali ai default di colonna: senza, un foto appena create() (prima di
    // un refresh dal DB) avrebbe questi campi a null in PHP, e l'anteprima
    // dell'editor partirebbe da un ritaglio indefinito invece che centrato.
    protected $attributes = [
        'scala' => 1,
        'pos_x' => 0.5,
        'pos_y' => 0.5,
    ];

    public function pagina(): BelongsTo
    {
        return $this->belongsTo(Pagina::class, 'pagina_id');
    }

    /** Path relativo sul disco public: mai un URL assoluto. */
    public function url(): string
    {
        return '/storage/'.ltrim($this->path, '/');
    }

    public function haDidascalia(): bool
    {
        return filled($this->didascalia);
    }

    /** `stile` con i default applicati alle chiavi mancanti — mai null/parziale verso il frontend. */
    public function stileEffettivo(): array
    {
        return StileTesto::effettivo($this->stile);
    }
}
