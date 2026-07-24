<?php

namespace Modules\Memorial\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Defunto extends Model
{
    protected $table = 'defunti';

    protected $fillable = [
        'nome', 'cognome', 'data_nascita', 'data_morte', 'anni',
        'frase', 'preghiera',
        'gdpr_consenso', 'gdpr_autorizzato_da', 'gdpr_parentela',
        'gdpr_autorizzato_at', 'gdpr_note',
        'ordine_id',
    ];

    protected $casts = [
        'data_nascita'        => 'date',
        'data_morte'          => 'date',
        'anni'                => 'integer',
        'gdpr_consenso'       => 'boolean',
        'gdpr_autorizzato_at' => 'datetime',
    ];

    public function ricordini(): HasMany
    {
        return $this->hasMany(Ricordino::class);
    }

    public function nomeCompleto(): string
    {
        return trim("{$this->nome} {$this->cognome}");
    }

    /**
     * Registra il consenso GDPR (in-app) per l'uso di immagine e dati del
     * defunto: chi autorizza, in che relazione, quando.
     */
    public function autorizzaGdpr(string $autorizzatoDa, ?string $parentela = null, ?string $note = null): void
    {
        $this->forceFill([
            'gdpr_consenso'       => true,
            'gdpr_autorizzato_da' => $autorizzatoDa,
            'gdpr_parentela'      => $parentela,
            'gdpr_note'           => $note,
            'gdpr_autorizzato_at' => Carbon::now(),
        ])->save();
    }

    /**
     * Dati pronti per precompilare i blocchi testo del ricordino-designer.
     */
    public function toPraticaData(): array
    {
        return [
            'nome'         => $this->nome,
            'cognome'      => $this->cognome,
            'anni'         => $this->anni,
            'data_nascita' => optional($this->data_nascita)->format('d/m/Y'),
            'data_morte'   => optional($this->data_morte)->format('d/m/Y'),
            'frase'        => $this->frase,
            'prayer'       => $this->preghiera,
        ];
    }
}
