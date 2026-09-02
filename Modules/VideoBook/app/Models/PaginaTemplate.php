<?php

namespace Modules\VideoBook\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Un layout di pagina riusabile: quanti riquadri foto ci sono e dove.
 *
 * `slots` è l'unica fonte della geometria: un array di
 * `{ordine, x, y, w, h}` in coordinate relative (0-1), applicabile a
 * qualunque formato fisico scelto per il libro — stesso ruolo che
 * [[\Modules\Memorial\Models\RicordinoTemplate]] ha per il Ricordino
 * Designer, qui per pagine con più foto invece di un solo fronte/retro.
 *
 * Non contiene didascalie: quelle sono per-foto, vivono su [[FotoPagina]]
 * quando l'utente riempie il riquadro, non sul layout.
 */
class PaginaTemplate extends Model
{
    protected $table = 'videobook_page_templates';

    protected $fillable = [
        'nome', 'numero_foto', 'slots', 'agenzia_id', 'is_predefinito', 'sort_order', 'anteprima',
    ];

    protected $casts = [
        'slots' => 'array',
        'is_predefinito' => 'boolean',
    ];

    public function pagine(): HasMany
    {
        return $this->hasMany(Pagina::class, 'template_id');
    }

    /** Ordine di presentazione: prima i predefiniti MemorAI, poi quelli propri, dal più recente. */
    public function scopeInOrdineDiElenco($query)
    {
        return $query->orderByDesc('is_predefinito')->orderBy('sort_order')->orderByDesc('id');
    }

    /**
     * Cosa vede chi chiama: i globali (`agenzia_id` null — predefiniti
     * MemorAI o creati da staff per tutti) sempre; i propri, se ha
     * un'agenzia, si aggiungono. Quelli di un'altra agenzia restano
     * invisibili — stesso criterio di RicordinoTemplate::scopeVisibiliPer().
     */
    public function scopeVisibiliPer($query, ?int $agenziaId)
    {
        return $query->when(
            $agenziaId,
            fn ($q) => $q->where(fn ($q2) => $q2->whereNull('agenzia_id')->orWhere('agenzia_id', $agenziaId)),
            fn ($q) => $q->whereNull('agenzia_id'),
        );
    }

    /** Il filtro principale del selettore: "mi serve un layout da N foto". */
    public function scopeConNumeroFoto($query, int $numeroFoto)
    {
        return $query->where('numero_foto', $numeroFoto);
    }

    /** Geometria (x,y,w,h) del riquadro `slot`, o null se il layout non lo prevede. */
    public function slot(int $slot): ?array
    {
        return collect($this->slots)->firstWhere('ordine', $slot);
    }

    /** URL relativo dell'anteprima (stessa scelta di RicordinoTemplate::anteprimaUrl()). */
    public function anteprimaUrl(): ?string
    {
        return $this->anteprima ? '/storage/'.ltrim($this->anteprima, '/') : null;
    }
}
