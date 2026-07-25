<?php

namespace Modules\Memorial\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Layout di ricordino riusabile su qualsiasi defunto.
 *
 * A differenza di [[Ricordino]], che è la bozza di UNA persona, il template
 * contiene solo l'impaginazione: i blocchi personali hanno il testo segnaposto
 * e la foto del defunto non c'è. Il designer li riempie al momento in cui il
 * template viene applicato.
 */
class RicordinoTemplate extends Model
{
    protected $table = 'ricordino_templates';

    protected $fillable = ['nome', 'formato', 'is_predefinito', 'is_smart_default', 'sort_order', 'fronte', 'retro', 'anteprima'];

    protected $casts = [
        'fronte'         => 'array',
        'retro'          => 'array',
        'is_predefinito'   => 'boolean',
        'is_smart_default' => 'boolean',
    ];

    /**
     * Ordine di presentazione: prima i predefiniti MemorAI (nel loro ordine),
     * poi i template dell'utente dal più recente.
     */
    public function scopeInOrdineDiElenco($query)
    {
        return $query->orderByDesc('is_predefinito')->orderBy('sort_order')->orderByDesc('id');
    }

    /**
     * Impaginazione che il Designer Smart usa per questo formato.
     * Se la dashboard non ne ha ancora scelta una, ripiega sul primo
     * predefinito MemorAI: lo Smart deve funzionare comunque.
     */
    public static function perSmart(string $formato): ?self
    {
        return static::where('formato', $formato)
            ->orderByDesc('is_smart_default')
            ->orderByDesc('is_predefinito')
            ->orderBy('sort_order')
            ->first();
    }

    /**
     * URL relativo dell'anteprima (stessa scelta di Santo::url(): evita il
     * problema di APP_URL=http://localhost).
     */
    public function anteprimaUrl(): ?string
    {
        return $this->anteprima ? '/storage/' . ltrim($this->anteprima, '/') : null;
    }
}
