<?php

namespace Modules\Commerce\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Commerce\Enums\MetodoPagamento;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $ammessi = array_map(
            fn (MetodoPagamento $m) => $m->value,
            MetodoPagamento::disponibiliPer($this->user()->eAgenziaApprovata()),
        );

        return [
            'nome' => ['required', 'string', 'max:150'],
            'telefono' => ['required', 'string', 'max:30'],
            'indirizzo' => ['required', 'string', 'max:255'],
            'cap' => ['required', 'digits:5'],
            'citta' => ['required', 'string', 'max:120'],
            'provincia' => ['required', 'string', 'size:2', 'alpha'],
            'note' => ['nullable', 'string', 'max:1000'],

            'metodo_pagamento' => ['required', Rule::in($ammessi)],

            // Serve solo se si paga con carta: la regola sta nel controller,
            // che sa quale metodo è stato scelto.
            'carta' => ['nullable', 'string', 'max:25'],

            // Solo un formato valido: il tetto vero (saldo disponibile,
            // totale dell'ordine) si verifica sotto lock in CreaOrdine, non
            // qui — un privato che lo inviasse non ha comunque un'agenzia da
            // cui attingere.
            'crediti_usati' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('provincia')) {
            $this->merge(['provincia' => strtoupper(trim($this->input('provincia')))]);
        }
    }

    public function attributes(): array
    {
        return [
            'nome' => 'il nome di chi riceve',
            'telefono' => 'il telefono',
            'indirizzo' => 'l\'indirizzo',
            'cap' => 'il CAP',
            'citta' => 'la città',
            'provincia' => 'la provincia',
            'metodo_pagamento' => 'il metodo di pagamento',
            'carta' => 'il numero della carta',
        ];
    }

    /** @return array<string, mixed> */
    public function datiConsegna(): array
    {
        return $this->safe()->only([
            'nome', 'telefono', 'indirizzo', 'cap', 'citta', 'provincia', 'note',
        ]);
    }

    public function metodo(): MetodoPagamento
    {
        return MetodoPagamento::from($this->validated('metodo_pagamento'));
    }
}
