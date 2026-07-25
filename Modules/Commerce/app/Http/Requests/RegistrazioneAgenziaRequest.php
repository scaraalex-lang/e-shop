<?php

namespace Modules\Commerce\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Modules\Commerce\Rules\PartitaIva;

class RegistrazioneAgenziaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Referente: è la persona che farà login per l'agenzia.
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Password::defaults()],

            // Agenzia
            'ragione_sociale' => ['required', 'string', 'max:255'],
            'partita_iva' => ['required', 'string', 'unique:agenzie,partita_iva', new PartitaIva],
            'codice_fiscale' => ['nullable', 'string', 'max:16'],
            'codice_sdi' => ['nullable', 'string', 'size:7', 'alpha_num'],
            'pec' => ['nullable', 'email', 'max:255'],

            'indirizzo' => ['required', 'string', 'max:255'],
            'cap' => ['required', 'digits:5'],
            'citta' => ['required', 'string', 'max:255'],
            'provincia' => ['required', 'string', 'size:2', 'alpha'],
            'telefono' => ['required', 'string', 'max:30'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(array_filter([
            'partita_iva' => preg_replace('/\D/', '', (string) $this->input('partita_iva')),
            'provincia' => strtoupper(trim((string) $this->input('provincia'))),
            'codice_fiscale' => strtoupper(trim((string) $this->input('codice_fiscale'))) ?: null,
            'codice_sdi' => strtoupper(trim((string) $this->input('codice_sdi'))) ?: null,
        ], fn ($v) => $v !== ''));
    }

    public function attributes(): array
    {
        return [
            'name' => 'il nome del referente',
            'ragione_sociale' => 'la ragione sociale',
            'partita_iva' => 'la partita IVA',
            'codice_sdi' => 'il codice destinatario SdI',
            'pec' => 'la PEC',
            'indirizzo' => 'l\'indirizzo',
            'cap' => 'il CAP',
            'citta' => 'la città',
            'provincia' => 'la provincia',
            'telefono' => 'il telefono',
        ];
    }

    /**
     * Dati della sola agenzia, pronti per `Agenzia::create()`.
     */
    public function datiAgenzia(): array
    {
        return $this->safe()->only([
            'ragione_sociale', 'partita_iva', 'codice_fiscale', 'codice_sdi', 'pec',
            'indirizzo', 'cap', 'citta', 'provincia', 'telefono',
        ]);
    }
}
