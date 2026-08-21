<?php

namespace Modules\SocialStory\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Memorial\Models\Defunto;
use Modules\SocialStory\Models\StoriaSocial;

/**
 * La storia vista da chi apre il link condiviso: nessun account, il link è
 * la credenziale (stesso principio di `VideoPubblicoController`).
 *
 * Nessun controllo sullo stato di un eventuale ordine collegato: il link
 * resta permanente, pensato per essere incollato su Facebook/Instagram
 * anche a distanza di tempo dall'acquisto.
 */
class StoriaPubblicaController extends Controller
{
    public function show(StoriaSocial $storia)
    {
        abort_unless($storia->pronta(), 404);

        return view('socialstory::pubblico.show', [
            'storia' => $storia,
            'defunto' => $storia->defunto_id ? Defunto::find($storia->defunto_id) : null,
        ]);
    }
}
