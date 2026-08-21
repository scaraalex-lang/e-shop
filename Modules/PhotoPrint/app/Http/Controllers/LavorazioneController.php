<?php

namespace Modules\PhotoPrint\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Commerce\Enums\StatoOrdine;
use Modules\Commerce\Enums\StatoPagamento;
use Modules\Commerce\Models\Ordine;
use Modules\Memorial\Models\Defunto;
use Modules\PhotoPrint\Models\FotoPratica;
use Modules\PhotoPrint\Servizi\LavorazioneCorrente;

/**
 * La lavorazione fotografica di un ordine, vista dal cliente.
 *
 * È il ponte fra l'ordine (Commerce) e i due editor (PhotoPrint): qui si
 * raccolgono i dati del defunto e il consenso, e da qui si entra nel Foto
 * Manager e nel Designer già puntati su questa pratica.
 */
class LavorazioneController extends Controller
{
    public function __construct(private LavorazioneCorrente $lavorazione) {}

    public function show(Request $request, Ordine $ordine): View|RedirectResponse
    {
        $this->soloSuo($request, $ordine);

        // Da qui in poi gli editor sanno di quale pratica si tratta.
        $this->lavorazione->imposta($ordine);

        $defunto = $ordine->defunto_id ? Defunto::find($ordine->defunto_id) : null;
        $ricordino = $defunto?->ricordini()->latest()->first();

        // Video Memoriale B2C: sbloccato solo se l'ordine è DAVVERO pagato e
        // contiene la photoceramica con QR — niente Ordine::designerAbilitato()
        // qui, includerebbe gratis anche i kit senza QR (vedi
        // DefuntoVideoController per il perché). Query builder puro, non il
        // model Eloquent di TributeVideo: PhotoPrint non deve mai dipendere da
        // TributeVideo, solo il contrario (stesso senso unico verso Memorial).
        $ordine->load('righe.product');
        $videoAbilitato = $defunto !== null
            && $ordine->stato_pagamento === StatoPagamento::Pagato
            && $ordine->righe->contains(fn ($riga) => $riga->product?->has_qr_memorial === true);
        $video = $defunto ? DB::table('video_memoriali')
            ->where('defunto_id', $defunto->id)
            ->latest('id')
            ->select(['id', 'token', 'stato'])
            ->first() : null;

        // Storia Social B2C: stesso schema del video memoriale sopra, gate
        // su has_social_story invece di has_qr_memorial — query builder
        // puro, PhotoPrint non deve mai dipendere da SocialStory.
        $storiaAbilitata = $defunto !== null
            && $ordine->stato_pagamento === StatoPagamento::Pagato
            && $ordine->righe->contains(fn ($riga) => $riga->product?->has_social_story === true);
        $storia = $defunto ? DB::table('storie_social')
            ->where('defunto_id', $defunto->id)
            ->latest('id')
            ->select(['id', 'token', 'canvas'])
            ->first() : null;

        return view('photoprint::lavorazione.show', [
            'ordine' => $ordine->load('servizi.servizioEditor'),
            'defunto' => $defunto,
            'ricordino' => $ricordino,
            'foto' => FotoPratica::where('ordine_id', $ordine->id)->latest()->get(),
            'videoAbilitato' => $videoAbilitato,
            'video' => $video,
            'storiaAbilitata' => $storiaAbilitata,
            'storia' => $storia,
        ]);
    }

    /**
     * I dati del defunto e il consenso all'uso di immagine e dati.
     *
     * Il consenso lo dà chi sta ordinando: è un familiare, e dichiara la
     * propria parentela. Senza, non si lavora la fotografia.
     */
    public function salvaDefunto(Request $request, Ordine $ordine): RedirectResponse
    {
        $this->soloSuo($request, $ordine);

        $dati = $request->validate([
            'nome' => ['required', 'string', 'max:100'],
            'cognome' => ['required', 'string', 'max:100'],
            'sesso' => ['nullable', Rule::in(['M', 'F'])],
            'data_nascita' => ['nullable', 'date', 'before:today'],
            'data_morte' => ['nullable', 'date', 'before_or_equal:today', 'after_or_equal:data_nascita'],
            'frase' => ['nullable', 'string', 'max:200'],
            'preghiera' => ['nullable', 'string', 'max:2000'],
            'luogo_partenza' => ['nullable', 'string', Rule::in(Defunto::LUOGHI_PARTENZA)],
            'indirizzo_cerimonia' => ['nullable', 'string', 'max:255'],
            'cerimonia_at' => ['nullable', 'date'],
            'chiesa' => ['nullable', 'string', 'max:150'],
            'indirizzo_chiesa' => ['nullable', 'string', 'max:255'],
            'cimitero' => ['nullable', 'string', 'max:255'],
            'citta' => ['nullable', 'string', 'max:100'],
            'provincia' => ['nullable', 'string', 'size:2', 'alpha'],
            'gdpr_parentela' => ['required', 'string', 'max:80'],
            'gdpr_consenso' => ['accepted'],
        ], [
            'gdpr_consenso.accepted' => 'Serve il tuo consenso per lavorare la fotografia.',
            'data_morte.after_or_equal' => 'La data non può essere precedente a quella di nascita.',
        ]);

        $defunto = $ordine->defunto_id
            ? Defunto::findOrFail($ordine->defunto_id)
            : new Defunto;

        $defunto->fill([
            'nome' => $dati['nome'],
            'cognome' => $dati['cognome'],
            'sesso' => $dati['sesso'] ?? null,
            'data_nascita' => $dati['data_nascita'] ?? null,
            'data_morte' => $dati['data_morte'] ?? null,
            'frase' => $dati['frase'] ?? null,
            'preghiera' => $dati['preghiera'] ?? null,
            'luogo_partenza' => $dati['luogo_partenza'] ?? null,
            'indirizzo_cerimonia' => $dati['indirizzo_cerimonia'] ?? null,
            'cerimonia_at' => $dati['cerimonia_at'] ?? null,
            'chiesa' => $dati['chiesa'] ?? null,
            'indirizzo_chiesa' => $dati['indirizzo_chiesa'] ?? null,
            'cimitero' => $dati['cimitero'] ?? null,
            'citta' => $dati['citta'] ?? null,
            'provincia' => isset($dati['provincia']) ? strtoupper($dati['provincia']) : null,
            'ordine_id' => $ordine->id,
        ])->save();

        $defunto->autorizzaGdpr(
            $request->user()->name,
            $dati['gdpr_parentela'],
            "Consenso raccolto in fase d'ordine {$ordine->numero}.",
        );

        $ordine->forceFill(['defunto_id' => $defunto->id])->save();

        // Le agenzie proseguono dalla Scheda Defunto (Foto → Manifesto →
        // Necrologio, canalizzati); un privato B2C non ha manifesto/
        // necrologio, resta sulla lavorazione come prima.
        if ($ordine->agenzia_id) {
            return redirect()
                ->route('defunti.show', $defunto)
                ->with('stato', 'Dati registrati. Ora puoi caricare la fotografia.');
        }

        return redirect()
            ->route('lavorazione', $ordine)
            ->with('stato', 'Dati registrati. Ora puoi caricare la fotografia.');
    }

    /**
     * Il cliente approva la bozza: da qui si va in stampa.
     */
    public function approva(Request $request, Ordine $ordine): RedirectResponse
    {
        $this->soloSuo($request, $ordine);

        $defunto = $ordine->defunto_id ? Defunto::find($ordine->defunto_id) : null;
        $ricordino = $defunto?->ricordini()->latest()->first();

        if (! $ricordino) {
            return redirect()
                ->route('lavorazione', $ordine)
                ->with('stato', 'Prima di approvare bisogna comporre il ricordino nel Designer.');
        }

        $ricordino->forceFill(['stato' => 'approvato'])->save();
        $ordine->passaA(StatoOrdine::InProduzione);
        $this->lavorazione->dimentica();

        return redirect()
            ->route('ordine', $ordine)
            ->with('stato', 'Bozza approvata: il tuo ordine va in produzione.');
    }

    /**
     * Un ordine si lavora solo se è "suo" (proprio, o della propria agenzia,
     * o si è staff — vedi Ordine::diChi) ed è ancora aperto.
     */
    private function soloSuo(Request $request, Ordine $ordine): void
    {
        abort_unless($ordine->diChi($request->user()), 404);
        abort_unless($ordine->lavorazioneApribile(), 403, 'Questo ordine non è (più) in lavorazione.');
    }
}
