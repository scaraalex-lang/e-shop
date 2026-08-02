<?php

namespace Modules\Commerce\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Commerce\Enums\Occasione;
use Modules\Commerce\Models\ServizioEditor;

class AttivaServiziOrdineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->eAgenziaApprovata() ?? false;
    }

    public function rules(): array
    {
        return [
            'occasione' => ['required', Rule::in(array_column(Occasione::cases(), 'value'))],
            'numero_anniversario' => ['required_if:occasione,anniversario', 'nullable', 'integer', 'min:1', 'max:99'],
            // Solo i servizi ancora attivi: un pannello che ne disattiva uno
            // deve bloccare anche un form già aperto altrove.
            'servizi' => ['required', 'array', 'min:1'],
            'servizi.*' => ['string', Rule::in(ServizioEditor::attivi()->attivabiliSuOrdine()->pluck('codice'))],
        ];
    }

    public function attributes(): array
    {
        return [
            'occasione' => 'l\'occasione',
            'numero_anniversario' => 'il numero dell\'anniversario',
            'servizi' => 'i servizi',
        ];
    }

    public function occasione(): Occasione
    {
        return Occasione::from($this->validated('occasione'));
    }

    /** @return array<int, string> */
    public function codiciServizio(): array
    {
        return $this->validated('servizi');
    }
}
