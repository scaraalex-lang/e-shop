<?php

namespace Modules\VideoBook\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Una pagina del libro: il template scelto e le foto che lo riempiono.
 *
 * FK vera verso `videobook_progetti` e `videobook_page_templates`: stesso
 * modulo, niente motivo per il legame debole usato verso Commerce/Memorial.
 * `template_id` è nullable — se il template scelto viene tolto dal catalogo
 * la pagina già composta non deve sparire, solo non sarà più riproponibile.
 */
class Pagina extends Model
{
    protected $table = 'videobook_pagine';

    protected $fillable = ['videobook_progetto_id', 'template_id', 'ordine'];

    public function libro(): BelongsTo
    {
        return $this->belongsTo(Libro::class, 'videobook_progetto_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(PaginaTemplate::class, 'template_id');
    }

    public function foto(): HasMany
    {
        return $this->hasMany(FotoPagina::class, 'pagina_id')->orderBy('slot');
    }

    /** I box di testo liberi della pagina (titoli, citazioni…), non legati a un riquadro del template. */
    public function testi(): HasMany
    {
        return $this->hasMany(TestoPagina::class, 'pagina_id')->orderBy('id');
    }

    /** La foto che riempie un dato riquadro, o null se non ancora caricata. */
    public function fotoNelloSlot(int $slot): ?FotoPagina
    {
        return $this->foto->firstWhere('slot', $slot);
    }
}
