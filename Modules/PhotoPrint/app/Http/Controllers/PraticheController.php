<?php

namespace Modules\PhotoPrint\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Commerce\Models\Ordine;
use Modules\Memorial\Models\Defunto;

/**
 * L'archivio delle pratiche per chi apre gli editor di mestiere.
 *
 * Prima, chi entrava in /studio/* senza un ordine in sessione atterrava in
 * automatico sulla pratica di esempio (PhotoPrintController ripiegava su
 * Defunto::first()). Questa pagina sostituisce quell'atterraggio silenzioso
 * con una scelta esplicita: si sceglie l'ordine, si entra nella sua
 * lavorazione (che mette l'ordine in sessione), e da lì negli editor.
 *
 * Un'agenzia vede le pratiche di TUTTI i suoi referenti — l'ownership di un
 * ordine B2B è dell'agenzia, non del singolo login che l'ha comprato (vedi
 * Ordine::diChi). Lo staff vede tutto: lavora su qualunque pratica. Un
 * privato che ci arriva senza aver aperto prima la propria lavorazione (caso
 * raro: indirizzo salvato a mano) vede solo i propri ordini.
 */
class PraticheController extends Controller
{
    public function index(Request $request): View
    {
        $utente = $request->user();

        $ordini = Ordine::query()
            ->where('richiede_lavorazione', true)
            ->when(! $utente->eStaff(), function ($query) use ($utente) {
                if ($agenzia = $utente->agenzia) {
                    $query->where('agenzia_id', $agenzia->id);
                } else {
                    $query->where('user_id', $utente->id);
                }
            })
            ->latest()
            ->paginate(20);

        $defunti = Defunto::whereIn('id', $ordini->pluck('defunto_id')->filter())->get()->keyBy('id');

        return view('photoprint::pratiche.index', [
            'ordini' => $ordini,
            'defunti' => $defunti,
        ]);
    }
}
