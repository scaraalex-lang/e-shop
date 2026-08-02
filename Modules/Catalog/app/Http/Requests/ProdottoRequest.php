<?php

namespace Modules\Catalog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Catalog\Support\Euro;

/**
 * Il form prodotto usato da /gestione. I prezzi arrivano come testo
 * ("26,00") e si convertono in centesimi qui, prima che tocchino il modello:
 * a valle di questa request tutto il progetto lavora solo su interi.
 */
class ProdottoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $prodotto = $this->route('prodotto');

        return [
            'category_id' => ['required', 'exists:categories,id'],
            'sku' => [
                'required', 'string', 'max:60',
                Rule::unique('products', 'sku')->ignore($prodotto?->id),
            ],
            'slug' => [
                'required', 'string', 'max:120', 'alpha_dash',
                Rule::unique('products', 'slug')->ignore($prodotto?->id),
            ],
            'name' => ['required', 'string', 'max:180'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],

            'price' => ['required', 'string'],
            'compare_at_price' => ['nullable', 'string'],

            'material' => ['nullable', 'string', 'max:120'],
            'color' => ['nullable', 'string', 'max:60'],

            'stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'is_hero' => ['nullable', 'boolean'],
            'is_configurable' => ['nullable', 'boolean'],
            'is_photo_printable' => ['nullable', 'boolean'],
            'has_qr_memorial' => ['nullable', 'boolean'],

            'is_kit' => ['nullable', 'boolean'],
            'included_units' => ['nullable', 'required_if:is_kit,1', 'integer', 'min:0'],
            'extra_unit_price' => ['nullable', 'required_if:is_kit,1', 'string'],

            // 12288 KB: una foto da telefono raramente supera i 10MB, e
            // nginx/php-fpm sono allineati a 32M lato upload.
            'foto_catalogo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:12288'],
            'foto_zoom' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:12288'],
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
            'compare_at_price' => 'il prezzo barrato',
            'stock' => 'la quantità disponibile',
            'included_units' => 'i pezzi inclusi',
            'extra_unit_price' => 'il prezzo del pezzo extra',
            'foto_catalogo' => 'la foto catalogo',
            'foto_zoom' => 'la foto zoom',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function datiProdotto(): array
    {
        return [
            'category_id' => $this->integer('category_id'),
            'sku' => $this->string('sku')->trim()->toString(),
            'slug' => $this->string('slug')->trim()->toString(),
            'name' => $this->string('name')->trim()->toString(),
            'short_description' => $this->input('short_description'),
            'description' => $this->input('description'),
            'price' => Euro::centesimi($this->input('price')) ?? 0,
            'compare_at_price' => Euro::centesimi($this->input('compare_at_price')),
            'material' => $this->input('material'),
            'color' => $this->input('color'),
            'stock' => $this->integer('stock'),
            'is_active' => $this->boolean('is_active'),
            'is_featured' => $this->boolean('is_featured'),
            'is_hero' => $this->boolean('is_hero'),
            'is_configurable' => $this->boolean('is_configurable'),
            'is_photo_printable' => $this->boolean('is_photo_printable'),
            'has_qr_memorial' => $this->boolean('has_qr_memorial'),
            'is_kit' => $this->boolean('is_kit'),
            'included_units' => $this->boolean('is_kit') ? $this->integer('included_units') : null,
            'extra_unit_price' => $this->boolean('is_kit') ? Euro::centesimi($this->input('extra_unit_price')) : null,
        ];
    }
}
