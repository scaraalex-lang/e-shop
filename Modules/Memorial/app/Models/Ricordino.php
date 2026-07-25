<?php

namespace Modules\Memorial\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ricordino extends Model
{
    public const BOZZA = 'bozza';
    public const IN_APPROVAZIONE = 'in_approvazione';
    public const APPROVATO = 'approvato';

    protected $table = 'ricordini';

    protected $fillable = [
        'defunto_id', 'formato', 'fronte', 'retro', 'stato',
        'anteprima_fronte', 'anteprima_retro',
    ];

    protected $casts = [
        'fronte' => 'array',
        'retro'  => 'array',
    ];

    public function defunto(): BelongsTo
    {
        return $this->belongsTo(Defunto::class);
    }

    /** Lo storico dei giri di approvazione, dal più recente. */
    public function revisioni(): HasMany
    {
        return $this->hasMany(RevisioneRicordino::class)->latest('inviata_at');
    }

    /** Il giro aperto: mandato alla famiglia e ancora senza risposta. */
    public function revisioneAperta(): ?RevisioneRicordino
    {
        return $this->revisioni()->whereNull('esito')->first();
    }

    public function approvato(): bool
    {
        return $this->stato === self::APPROVATO;
    }

    public function inAttesaDiApprovazione(): bool
    {
        return $this->stato === self::IN_APPROVAZIONE;
    }

    /**
     * Manda la bozza alla famiglia: nasce una revisione col suo token.
     *
     * L'anteprima viene congelata sulla revisione, non letta dal ricordino:
     * la bozza può cambiare mentre la famiglia guarda, e resta scritto cosa
     * aveva davanti quando ha risposto.
     */
    public function inviaInApprovazione(string $email, ?int $utenteId, ?string $anteprima): RevisioneRicordino
    {
        $revisione = $this->revisioni()->create([
            'token' => RevisioneRicordino::nuovoToken(),
            'inviata_a' => $email,
            'inviata_da' => $utenteId,
            'inviata_at' => now(),
            'anteprima' => $anteprima ?? $this->anteprima_fronte,
        ]);

        $this->forceFill(['stato' => self::IN_APPROVAZIONE])->save();

        return $revisione;
    }

    /**
     * La risposta della famiglia si riflette sul ricordino: approvato si
     * chiude, "modifiche" torna bozza perché c'è da rimetterci mano.
     */
    public function registraRisposta(RevisioneRicordino $revisione): void
    {
        $this->forceFill([
            'stato' => $revisione->esito === RevisioneRicordino::APPROVATA
                ? self::APPROVATO
                : self::BOZZA,
        ])->save();
    }
}
