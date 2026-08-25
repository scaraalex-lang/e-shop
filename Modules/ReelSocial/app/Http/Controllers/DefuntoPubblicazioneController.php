<?php

namespace Modules\ReelSocial\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Commerce\Enums\StatoPagamento;
use Modules\Commerce\Models\Ordine;
use Modules\Memorial\Models\Defunto;
use Modules\ReelSocial\Jobs\GeneraReelSocial;
use Modules\ReelSocial\Models\Reel;
use Modules\SocialStory\Models\StoriaSocial;
use Modules\TributeVideo\Models\VideoMemoriale;

/**
 * La pagina "Pubblicazione Social" legata a un defunto reale: aggrega Storia
 * Social e Video Memoriale (letti dai rispettivi moduli — vedi routes/web.php
 * sul perché questo modulo, a differenza di Memorial/PhotoPrint, può
 * dipendere da entrambi) e offre il bottone "Crea reel" che li concatena.
 *
 * Nessun gate a crediti proprio: la pagina è sempre visibile a chi può
 * vedere il defunto (soloSuo), ogni sotto-sezione mostra solo quello che
 * Storia/Video hanno già sbloccato per conto proprio — il reel combina cose
 * già pagate separatamente, non è un servizio a sé. Il bottone "Crea la
 * storia"/"Genera il video" va però mostrato attivo solo se il servizio è
 * DAVVERO abilitato (stesso gate di DefuntoStoriaController/
 * DefuntoVideoController): altrimenti si clicca, si arriva sul designer, e
 * lì scatta un 403 "non sei autorizzato" — bug trovato verificando a
 * schermo, il link andava mostrato disabilitato fin da qui.
 */
class DefuntoPubblicazioneController extends Controller
{
    public function show(Request $request, Defunto $defunto): View
    {
        $this->soloSuo($request, $defunto);

        $storia = StoriaSocial::where('defunto_id', $defunto->id)->first();
        $video = VideoMemoriale::where('defunto_id', $defunto->id)->latest()->first();
        $reel = Reel::where('defunto_id', $defunto->id)->latest()->first();

        $ordini = Ordine::where('defunto_id', $defunto->id)
            ->with(['servizi.servizioEditor', 'righe.product'])
            ->get();

        return view('reelsocial::defunti.show', [
            'defunto' => $defunto,
            'storia' => $storia,
            'video' => $video,
            'reel' => $reel,
            'reelDaAggiornare' => (bool) $reel?->daAggiornare($storia, $video),
            'storiaAbilitata' => $this->abilitato($ordini, 'storia-social', 'has_social_story'),
            'videoAbilitato' => $this->abilitato($ordini, 'video-memoriale', 'has_qr_memorial'),
        ]);
    }

    /**
     * Stesso doppio gate di DefuntoStoriaController/DefuntoVideoController:
     * B2B via designerAbilitato() sul codice servizio, B2C via ordine
     * davvero pagato con la riga prodotto che sblocca quel flag.
     */
    private function abilitato($ordini, string $codiceServizio, string $flagProdotto): bool
    {
        return $ordini->contains(fn (Ordine $o) => $o->designerAbilitato($codiceServizio))
            || $ordini->contains(fn (Ordine $o) => $o->stato_pagamento === StatoPagamento::Pagato
                && $o->righe->contains(fn ($riga) => $riga->product?->{$flagProdotto} === true));
    }

    public function creaReel(Request $request, Defunto $defunto): RedirectResponse
    {
        $this->soloSuo($request, $defunto);

        $storia = StoriaSocial::where('defunto_id', $defunto->id)->first();
        $video = VideoMemoriale::where('defunto_id', $defunto->id)->latest()->first();

        abort_unless(
            $storia?->pronta() && $video?->pronto(),
            422,
            'Servono sia la storia che il video, entrambi pronti, prima di poter creare il reel.'
        );

        $reel = Reel::create([
            'token' => Reel::nuovoToken(),
            'defunto_id' => $defunto->id,
            'storia_social_id' => $storia->id,
            'video_memoriale_id' => $video->id,
            'stato' => 'in_coda',
            'render_avviato_il' => now(),
        ]);

        GeneraReelSocial::dispatch($reel->id);

        return redirect()->route('defunti.pubblicazione-social.show', $defunto);
    }

    /** Stesso schema di DefuntoVideoController::soloSuo() / DefuntoStoriaController::soloSuo(). */
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
}
