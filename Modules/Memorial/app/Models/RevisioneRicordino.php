<?php

namespace Modules\Memorial\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Un giro di approvazione: la bozza mandata alla famiglia e la sua risposta.
 */
class RevisioneRicordino extends Model
{
    public const APPROVATA = 'approvata';
    public const MODIFICHE = 'modifiche';

    protected $table = 'revisioni_ricordino';

    protected $fillable = ['ricordino_id', 'token', 'inviata_a', 'inviata_da', 'inviata_at', 'anteprima'];

    protected $casts = [
        'inviata_at' => 'datetime',
        'vista_at' => 'datetime',
        'risposta_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'token';
    }

    public function ricordino(): BelongsTo
    {
        return $this->belongsTo(Ricordino::class);
    }

    public function inviataDa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inviata_da');
    }

    /** Il link è l'unica credenziale: dev'essere lungo e casuale. */
    public static function nuovoToken(): string
    {
        return Str::random(64);
    }

    public function inAttesa(): bool
    {
        return $this->esito === null;
    }

    public function segnaVista(): void
    {
        // Solo la prima apertura: dice quando la famiglia ha guardato, non
        // quante volte ci è tornata sopra.
        if (! $this->vista_at) {
            $this->forceFill(['vista_at' => Carbon::now()])->save();
        }
    }

    public function approva(): void
    {
        $this->rispondi(self::APPROVATA);
    }

    public function chiediModifiche(string $nota): void
    {
        $this->rispondi(self::MODIFICHE, $nota);
    }

    private function rispondi(string $esito, ?string $nota = null): void
    {
        $this->forceFill([
            'esito' => $esito,
            'nota' => $nota,
            'risposta_at' => Carbon::now(),
        ])->save();
    }

    public function esitoLeggibile(): string
    {
        return match ($this->esito) {
            self::APPROVATA => 'Approvata',
            self::MODIFICHE => 'Modifiche richieste',
            default => $this->vista_at ? 'Vista, in attesa di risposta' : 'In attesa',
        };
    }
}
