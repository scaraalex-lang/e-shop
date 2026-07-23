<?php

namespace Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class AttributeDefinition extends Model
{
    protected $fillable = [
        'category_id', 'key', 'label', 'type',
        'options', 'is_filterable', 'is_required', 'sort_order',
    ];

    protected $casts = [
        'options'       => 'array',
        'is_filterable' => 'boolean',
        'is_required'   => 'boolean',
        'sort_order'    => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeFilterable(Builder $query): Builder
    {
        return $query->where('is_filterable', true);
    }
}
