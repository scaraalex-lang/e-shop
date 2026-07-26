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

    protected $fillable = ['nome', 'formato', 'agenzia_id', 'is_predefinito', 'sort_order', 'fronte', 'retro', 'anteprima'];

    protected $casts = [
        'fronte'         => 'array',
        'retro'          => 'array',
        'is_predefinito' => 'boolean',
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
     * Cosa vede chi chiama: i globali (`agenzia_id` null — predefiniti MemorAI
     * o creati da staff per tutti) sempre; i propri, se ha un'agenzia, si
     * aggiungono. Quelli di un'altra agenzia restano invisibili — sono
     * informazione commerciale loro, come per [[Necrologio]].
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
     * URL relativo dell'anteprima (stessa scelta di Santo::url(): evita il
     * problema di APP_URL=http://localhost).
     */
    public function anteprimaUrl(): ?string
    {
        return $this->anteprima ? '/storage/' . ltrim($this->anteprima, '/') : null;
    }
}
