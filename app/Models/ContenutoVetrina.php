<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Un blocco di testo della vetrina (hero, fascia valori, CTA, topbar,
 * footer): chiave/valore, non una lista — home.blade.php e layouts/app.blade.php
 * lo leggono direttamente con ContenutoVetrina::valore(), niente controller
 * di mezzo per le pagine che non ne hanno uno proprio (il layout condiviso).
 */
class ContenutoVetrina extends Model
{
    protected $table = 'contenuti_vetrina';

    protected $fillable = ['chiave', 'valore'];

    /**
     * Tutte le chiavi lette in questa richiesta, per non fare una query a
     * campo. Nella app container, non in una proprietà statica: nei test
     * Laravel rifà il container ad ogni test (refreshApplication), una
     * proprietà statica invece sopravvivrebbe da un test all'altro con
     * valori di un database già rollback-ato.
     */
    private const CACHE_BINDING = 'contenuti_vetrina.cache';

    public static function valore(string $chiave, ?string $default = null): ?string
    {
        if (! app()->bound(self::CACHE_BINDING)) {
            app()->instance(self::CACHE_BINDING, static::query()->pluck('valore', 'chiave')->all());
        }

        return app(self::CACHE_BINDING)[$chiave] ?? $default;
    }

    protected static function booted(): void
    {
        static::saved(fn () => app()->forgetInstance(self::CACHE_BINDING));
    }
}
