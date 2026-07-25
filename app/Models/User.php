<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Modules\Commerce\Enums\RuoloUtente;
use Modules\Commerce\Models\Agenzia;

/**
 * `ruolo` e `agenzia_id` restano FUORI da Fillable di proposito: sono
 * l'autorizzazione dell'account. Assegnarli in massa vorrebbe dire lasciare
 * che un modulo di registrazione possa spedire `ruolo=staff`.
 */
#[Fillable(['name', 'email', 'password', 'telefono'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'ruolo' => RuoloUtente::class,
        ];
    }

    public function agenzia(): BelongsTo
    {
        return $this->belongsTo(Agenzia::class);
    }

    public function eAgenzia(): bool
    {
        return $this->ruolo === RuoloUtente::Agenzia;
    }

    public function eStaff(): bool
    {
        return $this->ruolo === RuoloUtente::Staff;
    }

    /**
     * Vero solo se l'account è di un'agenzia e l'agenzia è stata approvata:
     * è la condizione che sblocca listino riservato, sconti a scaglioni e
     * minimo d'ordine B2B.
     */
    public function eAgenziaApprovata(): bool
    {
        return $this->eAgenzia() && $this->agenzia?->eApprovata() === true;
    }
}
