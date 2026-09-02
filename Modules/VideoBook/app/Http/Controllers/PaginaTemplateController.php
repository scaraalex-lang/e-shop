<?php

namespace Modules\VideoBook\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\VideoBook\Models\PaginaTemplate;

/**
 * Il selettore di layout: cosa vede l'utente quando sceglie il template per
 * una nuova pagina del libro.
 *
 * Sola lettura per ora: salvare o promuovere template propri (come fa
 * RicordinoApiController per il Ricordino Designer) resta un passo
 * successivo, quando l'editor che li consuma esisterà davvero.
 */
class PaginaTemplateController extends Controller
{
    /**
     * Elenco dei layout visibili a chi chiama: i predefiniti MemorAI sempre,
     * i propri (se ha un'agenzia) in più — vedi
     * [[PaginaTemplate::scopeVisibiliPer]]. Filtrabile per numero di foto,
     * il criterio con cui l'utente sceglie nella griglia ("mi serve un
     * layout da 4 foto").
     */
    public function index(Request $request)
    {
        $agenziaId = $request->user()?->agenzia?->id;
        $numeroFoto = $request->integer('numero_foto') ?: null;

        $templates = PaginaTemplate::visibiliPer($agenziaId)
            ->when($numeroFoto, fn ($q) => $q->conNumeroFoto($numeroFoto))
            ->inOrdineDiElenco()
            ->get()
            ->map(fn (PaginaTemplate $t) => [
                'id'          => $t->id,
                'name'        => $t->nome,
                'numero_foto' => $t->numero_foto,
                'predefinito' => $t->is_predefinito,
                'globale'     => $t->agenzia_id === null,
                'thumbnail'   => $t->anteprimaUrl(),
                'slots'       => $t->slots,
            ]);

        return response()->json($templates);
    }
}
