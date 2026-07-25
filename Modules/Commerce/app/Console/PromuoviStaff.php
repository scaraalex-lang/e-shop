<?php

namespace Modules\Commerce\Console;

use App\Models\User;
use Illuminate\Console\Command;
use Modules\Commerce\Enums\RuoloUtente;

/**
 * L'area /gestione è riservata allo staff, ma il ruolo staff non si può
 * chiedere da nessun modulo web (sarebbe un buco). Il primo account staff —
 * e ogni successivo — si assegna da qui, con accesso al server.
 */
class PromuoviStaff extends Command
{
    protected $signature = 'commerce:staff
                            {email : Indirizzo email dell\'utente}
                            {--rimuovi : Riporta l\'utente a privato}';

    protected $description = 'Assegna (o toglie) il ruolo staff a un utente esistente';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error("Nessun utente con email {$this->argument('email')}.");

            return self::FAILURE;
        }

        if ($this->option('rimuovi')) {
            if ($user->agenzia_id) {
                $this->error('Questo utente è collegato a un\'agenzia: non può tornare privato.');

                return self::FAILURE;
            }

            $user->ruolo = RuoloUtente::Privato;
            $user->save();

            $this->info("{$user->email} non è più staff.");

            return self::SUCCESS;
        }

        if ($user->agenzia_id) {
            $this->error('Questo utente è il referente di un\'agenzia: usa un account separato per lo staff.');

            return self::FAILURE;
        }

        $user->ruolo = RuoloUtente::Staff;
        $user->save();

        $this->info("{$user->email} è ora staff: può entrare in /gestione.");

        return self::SUCCESS;
    }
}
