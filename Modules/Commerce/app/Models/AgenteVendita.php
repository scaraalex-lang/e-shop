<?php

namespace Modules\Commerce\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Solo anagrafica: l'agente di vendita B2B non ha un proprio login, è un
 * record gestito dallo staff per attribuire le agenzie a chi le segue.
 */
class AgenteVendita extends Model
{
    use SoftDeletes;

    protected $table = 'agenti_vendita';

    protected $fillable = ['nome', 'email', 'telefono', 'note'];

    public function agenzie(): HasMany
    {
        return $this->hasMany(Agenzia::class);
    }
}
