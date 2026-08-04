<?php

namespace Modules\Memorial\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Manifesto extends Model
{
    protected $table = 'manifesti';

    protected $fillable = [
        'defunto_id', 'etichetta', 'formato', 'canvas', 'pdf', 'web', 'principale',
    ];

    protected $casts = [
        'canvas' => 'array',
        'principale' => 'boolean',
    ];

    public function defunto(): BelongsTo
    {
        return $this->belongsTo(Defunto::class);
    }

    public function scopePrincipale(Builder $query): Builder
    {
        return $query->where('principale', true);
    }

    /** Path relativo (mai `Storage::url()`/`url()`, vedi CLAUDE.md): evita l'host sbagliato di APP_URL=localhost. */
    public function pdfUrl(): ?string
    {
        return $this->pdf ? '/storage/'.ltrim($this->pdf, '/') : null;
    }

    public function webUrl(): ?string
    {
        return $this->web ? '/storage/'.ltrim($this->web, '/') : null;
    }

    /**
     * Un solo principale per defunto (mostrato in miniatura sul necrologio):
     * stesso schema di `FotoPratica::rendiPrincipale()`.
     */
    public function rendiPrincipale(): void
    {
        static::where('defunto_id', $this->defunto_id)
            ->where('id', '!=', $this->id)
            ->update(['principale' => false]);

        $this->forceFill(['principale' => true])->save();
    }

    /**
     * Clona canvas e formato in una nuova riga: utile per passare dal
     * manifesto del funerale a quello del trigesimo mantenendo il layout.
     */
    public function duplica(string $nuovaEtichetta): self
    {
        return static::create([
            'defunto_id' => $this->defunto_id,
            'etichetta' => $nuovaEtichetta,
            'formato' => $this->formato,
            'canvas' => $this->canvas,
            'principale' => false,
        ]);
    }
}
