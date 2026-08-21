<?php

namespace Modules\SocialStory\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Layout di storia social riusabile. Stessa logica di
 * `Modules\Memorial\Models\ManifestoTemplate`/`RicordinoTemplate`:
 * `agenzia_id` null è un layout globale (curato da staff, visibile a tutti),
 * un id è l'archivio della singola agenzia.
 */
class StoriaTemplate extends Model
{
    protected $table = 'storia_templates';

    protected $fillable = ['nome', 'formato', 'agenzia_id', 'is_predefinito', 'sort_order', 'layout', 'anteprima'];

    protected $casts = [
        'layout' => 'array',
        'is_predefinito' => 'boolean',
    ];

    /** Prima i predefiniti (curati), poi i propri dal più recente. */
    public function scopeInOrdineDiElenco($query)
    {
        return $query->orderByDesc('is_predefinito')->orderBy('sort_order')->orderByDesc('id');
    }

    /** I globali sempre; i propri, se c'è un'agenzia, in più. */
    public function scopeVisibiliPer($query, ?int $agenziaId)
    {
        return $query->when(
            $agenziaId,
            fn ($q) => $q->where(fn ($q2) => $q2->whereNull('agenzia_id')->orWhere('agenzia_id', $agenziaId)),
            fn ($q) => $q->whereNull('agenzia_id'),
        );
    }

    public function anteprimaUrl(): ?string
    {
        return $this->anteprima ? '/storage/'.ltrim($this->anteprima, '/') : null;
    }
}
