<?php

namespace Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id', 'sku', 'slug', 'name',
        'short_description', 'description',
        'price', 'compare_at_price',
        'material', 'color', 'attributes',
        'is_configurable', 'is_photo_printable', 'has_qr_memorial',
        'is_kit', 'included_units', 'extra_unit_price', 'crediti',
        'stock', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'attributes'         => 'array',
        'price'              => 'integer',
        'compare_at_price'   => 'integer',
        'is_configurable'    => 'boolean',
        'is_photo_printable' => 'boolean',
        'has_qr_memorial'    => 'boolean',
        'is_kit'             => 'boolean',
        'included_units'     => 'integer',
        'extra_unit_price'   => 'integer',
        'crediti'            => 'integer',
        'stock'              => 'integer',
        'is_active'          => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeWhereAttribute(Builder $query, string $key, mixed $value): Builder
    {
        return $query->where("attributes->{$key}", $value);
    }

    public function attr(string $key, mixed $default = null): mixed
    {
        return data_get($this->getAttribute('attributes') ?? [], $key, $default);
    }

    public function priceForQuantity(int $quantity): int
    {
        if (! $this->is_kit) {
            return $this->price * max($quantity, 0);
        }

        $included = $this->included_units ?? 0;
        $extra    = max($quantity - $included, 0);

        return $this->price + ($extra * ($this->extra_unit_price ?? 0));
    }

    public function requiresPhotoFlow(): bool
    {
        return $this->is_photo_printable;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
