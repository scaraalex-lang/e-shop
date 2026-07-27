<?php

namespace Modules\Catalog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Catalog\Support\Euro;

/**
 * Crea un kit componibile: è un Product a tutti gli effetti, con prezzo
 * impostato a mano dallo staff (non la somma dei componenti — deciso col
 * committente). La composizione si aggiunge dopo, dalla pagina del kit.
 */
class KitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'sku' => ['required', 'string', 'max:60', Rule::unique('products', 'sku')],
            'slug' => ['required', 'string', 'max:120', 'alpha_dash', Rule::unique('products', 'slug')],
            'name' => ['required', 'string', 'max:180'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'string'],
            'stock' => ['required', 'integer', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'category_id' => 'la categoria',
            'sku' => 'lo SKU',
            'slug' => 'lo slug',
            'name' => 'il nome',
            'price' => 'il prezzo',
            'stock' => 'la quantità disponibile',
        ];
    }

    /** @return array<string, mixed> */
    public function datiKit(): array
    {
        return [
            'category_id' => $this->integer('category_id'),
            'sku' => $this->string('sku')->trim()->toString(),
            'slug' => $this->string('slug')->trim()->toString(),
            'name' => $this->string('name')->trim()->toString(),
            'short_description' => $this->input('short_description'),
            'price' => Euro::centesimi($this->input('price')) ?? 0,
            'stock' => $this->integer('stock'),
            'is_active' => true,
            'is_componibile' => true,
        ];
    }
}
