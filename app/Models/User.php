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

    /**
     * Le voci di menu per competenza di ruolo — [etichetta, url, pattern di
     * rotta per lo stato attivo]. Un solo posto: le usano sia la sidebar di
     * /account sia il menu a tendina della barra pubblica, così staff/
     * agenzia/privato vedono sempre lo stesso set di voci ovunque compaia.
     */
    public function vociAccount(): array
    {
        $voci = [
            ['Panoramica', route('account'), 'account'],
        ];

        // L'attività quotidiana dell'agenzia: subito dopo Panoramica, non in
        // fondo al menu.
        if ($this->eAgenziaApprovata()) {
            $voci[] = ['Acquisto Servizi', route('servizi'), 'servizi'];
            $voci[] = ['Nuovo ordine', route('ordini.nuovo'), 'ordini.nuovo'];
            $voci[] = ['Pagamenti', route('fatture'), 'fatture'];
        }

        if (! $this->eStaff()) {
            $voci[] = ['I miei ordini', route('ordini'), ['ordini', 'ordine', 'lavorazione*']];
            // Unico punto d'ingresso cliccabile verso la Scheda Defunto: prima
            // ci si arrivava solo per redirect da Acquisto Servizi → Lavorazione.
            $voci[] = ['I miei defunti', route('defunti.index'), ['defunti.index', 'defunti.show']];
        }

        // I necrologi sono uno strumento dell'agenzia, non un prodotto: non
        // passano dall'ordine e stanno accanto agli editor.
        if ($this->eAgenzia()) {
            $voci[] = ['Necrologi', route('necrologi.index'), 'necrologi.*'];
        }

        if ($this->eAgenziaApprovata()) {
            $voci[] = ['Studio ricordini', route('pratiche.index'), ['pratiche.*', 'studio.*']];
        }

        $voci[] = ['Profilo e accesso', route('account.profilo'), 'account.profilo'];

        return $voci;
    }
}
