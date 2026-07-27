<?php

namespace App\Models;

use App\Enums\ZonaMenu;
use Illuminate\Database\Eloquent\Model;

/**
 * Un link del menu principale, di una colonna del footer, o dei link legali.
 * Stessa forma di dato ovunque (etichetta, url, ordine): cambia solo la zona.
 */
class VoceMenu extends Model
{
    protected $table = 'voci_menu';

    protected $fillable = ['zona', 'etichetta', 'url', 'sort_order', 'is_active'];

    protected $casts = [
        'zona' => ZonaMenu::class,
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeInZona($query, ZonaMenu $zona)
    {
        return $query->where('zona', $zona);
    }

    public function scopeAttive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
