<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Slide del carosello di home page (vedi migration create_home_slides_table).
 */
class HomeSlide extends Model
{
    protected $fillable = [
        'occhiello', 'titolo', 'titolo_corsivo', 'testo',
        'immagine', 'immagine_alt',
        'cta_label', 'cta_href', 'cta2_label', 'cta2_href',
        'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    /** Slide pubblicate, nell'ordine deciso dalla dashboard. */
    public function scopeAttive(Builder $q): Builder
    {
        return $q->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * URL dell'immagine. Path RELATIVO /storage/... di proposito: APP_URL non è
     * affidabile in questo ambiente (vedi skill studio-editor, regola 5).
     */
    public function immagineUrl(): ?string
    {
        if (! $this->immagine) {
            return null;
        }

        // già un URL o un path assoluto: lascialo com'è
        if (str_starts_with($this->immagine, 'http') || str_starts_with($this->immagine, '/')) {
            return $this->immagine;
        }

        return '/storage/' . ltrim($this->immagine, '/');
    }
}
