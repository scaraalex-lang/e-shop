<?php

namespace Modules\Catalog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoria = $this->route('categoria');

        return [
            'name' => ['required', 'string', 'max:180'],
            'slug' => [
                'required', 'string', 'max:180', 'alpha_dash',
                Rule::unique('categories', 'slug')->ignore($categoria?->id),
            ],
            'parent_id' => [
                'nullable',
                Rule::exists('categories', 'id')->whereNot('id', $categoria?->id),
            ],
            'description' => ['nullable', 'string'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            // 12288 KB: una foto da telefono raramente supera i 10MB, e
            // nginx/php-fpm sono allineati a 32M lato upload.
            'immagine' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:12288'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'il nome',
            'slug' => 'lo slug',
            'parent_id' => 'la categoria padre',
            'sort_order' => 'l\'ordine',
            'immagine' => 'l\'immagine',
        ];
    }

    /** @return array<string, mixed> */
    public function datiCategoria(): array
    {
        return [
            'name' => $this->string('name')->trim()->toString(),
            'slug' => $this->string('slug')->trim()->toString(),
            'parent_id' => $this->input('parent_id') ?: null,
            'description' => $this->input('description'),
            'sort_order' => $this->integer('sort_order'),
            'is_active' => $this->boolean('is_active'),
        ];
    }
}
