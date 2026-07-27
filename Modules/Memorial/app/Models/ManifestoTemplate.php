<?php

namespace Modules\Memorial\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Layout di manifesto riusabile, in coordinate relative del canvas.
 *
 * Stessa logica di [[RicordinoTemplate]]: `agenzia_id` null è un layout
 * globale (oggi solo creabile da staff, come lì), un id è l'archivio della
 * singola agenzia.
 */
class ManifestoTemplate extends Model
{
    protected $table = 'manifesto_templates';

    protected $fillable = ['nome', 'formato', 'agenzia_id', 'fronte', 'anteprima'];

    protected $casts = [
        'fronte' => 'array',
    ];

    /**
     * I globali (`agenzia_id` null) sempre; i propri, se c'è un'agenzia, in
     * più. Quelli di un'altra agenzia restano invisibili.
     */
    public function scopeVisibiliPer($query, ?int $agenziaId)
    {
        return $query->when(
            $agenziaId,
            fn ($q) => $q->where(fn ($q2) => $q2->whereNull('agenzia_id')->orWhere('agenzia_id', $agenziaId)),
            fn ($q) => $q->whereNull('agenzia_id'),
        );
    }

    /**
     * URL relativo (stessa scelta di [[Santo::url]]/[[RicordinoTemplate::anteprimaUrl]]):
     * evita il problema di APP_URL=http://localhost.
     */
    public function anteprimaUrl(): ?string
    {
        return $this->anteprima ? '/storage/'.ltrim($this->anteprima, '/') : null;
    }
}
