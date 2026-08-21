<?php

namespace Modules\SocialStory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\Commerce\Enums\StatoPagamento;
use Modules\Commerce\Models\Agenzia;
use Modules\Commerce\Models\Ordine;
use Modules\Memorial\Models\Defunto;
use Modules\SocialStory\Models\StoriaSocial;

/**
 * La Storia Social legata a un defunto reale, sui due canali:
 *
 * - B2B (agenzie): aperta dalla Scheda Defunto, gate a crediti come
 *   Manifesti/Necrologi/Ricordini/Video Memoriale
 *   (ServizioEditor::CODICI_ORDINE via Ordine::designerAbilitato()).
 * - B2C (privati): NON a crediti — i privati non hanno un saldo crediti
 *   (AttivaServiziOrdine::da() richiede sempre un'Agenzia). Sbloccata da un
 *   acquisto pagato per davvero che contiene un prodotto con
 *   `has_social_story=true` — stesso schema di `has_qr_memorial` per il
 *   Video Memoriale B2C (vedi DefuntoVideoController::qrPagato()).
 *
 * Stesso schema di ManifestiController/DefuntoVideoController (soloSuo/
 * assicuraAbilitato). A differenza del Video Memoriale non serve un set di
 * foto dedicato caricato lato server: il designer lavora tutto lato client
 * (Fabric.js), qui si salva solo lo stato finale del canvas.
 */
class DefuntoStoriaController extends Controller
{
    private const DISK_DIR = 'storiesocial';

    public function show(Request $request, Defunto $defunto): View
    {
        $this->soloSuo($request, $defunto);
        $ordine = $this->assicuraAbilitato($defunto);

        $storia = StoriaSocial::firstOrCreate(
            ['defunto_id' => $defunto->id],
            [
                'token' => StoriaSocial::nuovoToken(),
                'ordine_id' => $ordine->id,
                'agenzia_id' => $ordine->agenzia_id,
            ],
        );

        return view('socialstory::editor', [
            'defunto' => $defunto,
            'storia' => $storia,
            'praticaData' => $defunto->toPraticaData(),
            'agenziaData' => ['name' => $this->agenziaDi($defunto)?->ragione_sociale ?? 'MemorAI'],
            'fotoPrincipale' => $defunto->fotoPrincipalePath() ? '/storage/'.ltrim($defunto->fotoPrincipalePath(), '/') : null,
        ]);
    }

    /**
     * Salva canvas + anteprima JPEG (generata dal client ad ogni
     * salvataggio, stesso schema di ManifestiController::salva()). L'unico
     * controllo qui è l'ownership: chi può aprire il designer per questo
     * defunto ha già superato il gate a crediti/pagamento in show().
     */
    public function salva(Request $request, StoriaSocial $storia): JsonResponse
    {
        $defunto = Defunto::findOrFail($storia->defunto_id);
        $this->soloSuo($request, $defunto);

        $dati = $request->validate([
            'canvas' => ['required', 'string'],
            'anteprima' => ['nullable', 'string'],
        ]);

        $canvas = json_decode($dati['canvas'], true);
        abort_if(! is_array($canvas), 422, 'La storia non è valida.');

        $vecchiaAnteprima = $storia->anteprima;
        $pathAnteprima = $vecchiaAnteprima;
        if ($dati['anteprima'] ?? null) {
            if (! preg_match('/^data:image\/jpeg;base64,(.+)$/', $dati['anteprima'], $m)) {
                abort(422, 'L\'anteprima non è valida.');
            }
            $binario = base64_decode($m[1], true);
            abort_if($binario === false, 422, 'L\'anteprima non è valida.');

            // Nome nuovo ad ogni salvataggio: se il link viene già condiviso
            // su FB/IG, quei social mettono in cache l'anteprima per
            // indirizzo — stesso motivo di NecrologiController::salvaCard().
            $pathAnteprima = self::DISK_DIR.'/'.$storia->token.'/'.Str::lower(Str::random(8)).'.jpg';
            Storage::disk('public')->put($pathAnteprima, $binario);
        }

        $storia->update(['canvas' => $canvas, 'anteprima' => $pathAnteprima]);

        if ($pathAnteprima !== $vecchiaAnteprima && $vecchiaAnteprima) {
            Storage::disk('public')->delete($vecchiaAnteprima);
        }

        return response()->json(['success' => true, 'anteprima' => $storia->fresh()->anteprimaUrl()]);
    }

    /** Stesso schema di ManifestiController::soloSuo(). */
    private function soloSuo(Request $request, Defunto $defunto): void
    {
        $utente = $request->user();

        if ($utente->eStaff()) {
            return;
        }

        abort_unless(
            Ordine::where('defunto_id', $defunto->id)->get()->contains(fn (Ordine $o) => $o->diChi($utente)),
            404,
        );
    }

    /**
     * Gate del passo, sui due canali (vedi doc-block della classe). Ritorna
     * il primo ordine di questo defunto che lo abilita, a cui la storia
     * nasce agganciata.
     */
    private function assicuraAbilitato(Defunto $defunto): Ordine
    {
        $ordini = Ordine::where('defunto_id', $defunto->id)
            ->with(['servizi.servizioEditor', 'righe.product'])
            ->get();

        $ordine = $ordini->first(fn (Ordine $o) => $o->designerAbilitato('storia-social') && $o->agenzia_id !== null)
            ?? $ordini->first(fn (Ordine $o) => $this->storiaPagata($o));

        abort_unless($ordine !== null, 403, 'La Storia Social non è disponibile per questo defunto.');

        return $ordine;
    }

    /** B2C: l'ordine è pagato davvero e contiene un prodotto con has_social_story=true. */
    private function storiaPagata(Ordine $ordine): bool
    {
        return $ordine->stato_pagamento === StatoPagamento::Pagato
            && $ordine->righe->contains(fn ($riga) => $riga->product?->has_social_story === true);
    }

    private function agenziaDi(Defunto $defunto)
    {
        $ordine = $defunto->ordine_id ? Ordine::find($defunto->ordine_id) : null;

        return $ordine?->agenzia_id ? Agenzia::find($ordine->agenzia_id) : null;
    }
}
