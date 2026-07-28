<?php

namespace Modules\Memorial\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Defunto extends Model
{
    protected $table = 'defunti';

    /** Da dove parte il corteo: le uniche tre opzioni concrete, niente testo libero. */
    public const LUOGHI_PARTENZA = ['Casa del commiato', 'Casa funeraria', 'Casa del defunto'];

    protected $fillable = [
        'nome', 'cognome', 'data_nascita', 'data_morte', 'anni',
        'frase', 'preghiera',
        'luogo_partenza', 'indirizzo_cerimonia', 'cerimonia_at',
        'chiesa', 'indirizzo_chiesa',
        'cimitero',
        'gdpr_consenso', 'gdpr_autorizzato_da', 'gdpr_parentela',
        'gdpr_autorizzato_at', 'gdpr_note',
        'ordine_id',
    ];

    protected $casts = [
        'data_nascita'        => 'date',
        'data_morte'          => 'date',
        'anni'                => 'integer',
        'cerimonia_at'        => 'datetime',
        'gdpr_consenso'       => 'boolean',
        'gdpr_autorizzato_at' => 'datetime',
    ];

    public function ricordini(): HasMany
    {
        return $this->hasMany(Ricordino::class);
    }

    /**
     * Il path (disco `public`) della foto principale scelta nel Foto Manager
     * per l'ordine legato a questo defunto.
     *
     * Letta con query builder puro sulla tabella `foto_pratica` (modulo
     * PhotoPrint), non con il model Eloquent: PhotoPrint dipende già da
     * Memorial (importa Defunto e Ricordino), quindi un import del model
     * FotoPratica qui creerebbe la dipendenza circolare opposta. Stesso
     * pattern già in uso in questo modulo per `ordine_id`: nessuna FK reale,
     * lettura per id.
     */
    public function fotoPrincipalePath(): ?string
    {
        if (! $this->ordine_id) {
            return null;
        }

        return DB::table('foto_pratica')
            ->where('ordine_id', $this->ordine_id)
            ->where('is_principale', true)
            ->value('path');
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
