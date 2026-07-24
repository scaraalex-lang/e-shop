<?php

namespace Modules\Memorial\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ricordino extends Model
{
    protected $table = 'ricordini';

    protected $fillable = [
        'defunto_id', 'formato', 'fronte', 'retro', 'stato',
        'anteprima_fronte', 'anteprima_retro',
    ];

    protected $casts = [
        'fronte' => 'array',
        'retro'  => 'array',
    ];

    public function defunto(): BelongsTo
    {
        return $this->belongsTo(Defunto::class);
    }
}
