<?php

namespace Modules\VideoBook\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Un layout di pagina riusabile: quanti riquadri foto ci sono e dove.
 *
 * `slots` è l'unica fonte della geometria, ma non la geometria finita: è un
 * albero `{area, nodo}` (colonna/riga/foto, vedi Support\GrigliaPagina e
 * PaginaTemplateSeeder) che si risolve in rettangoli concreti solo
 * conoscendo il formato fisico scelto per il libro — è così che un gap di
 * 4mm tra due foto resta 4mm veri in stampa su qualunque taglia (15x15 come
 * 38x36), invece di un numero relativo congelato una volta sola. Stesso
 * ruolo che [[\Modules\Memorial\Models\RicordinoTemplate]] ha per il
 * Ricordino Designer, qui per pagine con più foto invece di un solo
 * fronte/retro.
 *
 * Non contiene didascalie: le foto non ne hanno più (rimosse dal layout di
 * stampa e dal video, vedi FotoPagina).
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

    /** true se il layout prevede un riquadro foto con questo numero. Solo esistenza: la geometria serve un formato, vedi Support\GrigliaPagina. */
    public function hasSlot(int $ordine): bool
    {
        return in_array($ordine, $this->ordiniFoto(), true);
    }

    /** Tutti i numeri di riquadro (foglie 'foto') previsti dal layout, in qualunque punto dell'albero. */
    public function ordiniFoto(): array
    {
        $ordini = [];
        $this->raccogliOrdini($this->slots['nodo'] ?? [], $ordini);

        return $ordini;
    }

    private function raccogliOrdini(array $nodo, array &$ordini): void
    {
        if (($nodo['tipo'] ?? null) === 'foto') {
            $ordini[] = $nodo['ordine'];

            return;
        }

        foreach ($nodo['figli'] ?? [] as $figlio) {
            $this->raccogliOrdini($figlio['nodo'], $ordini);
        }
    }

    /** URL relativo dell'anteprima (stessa scelta di RicordinoTemplate::anteprimaUrl()). */
    public function anteprimaUrl(): ?string
    {
        return $this->anteprima ? '/storage/'.ltrim($this->anteprima, '/') : null;
    }
}
