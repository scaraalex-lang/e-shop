<?php

namespace Modules\TributeVideo\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Modules\Commerce\Enums\StatoPagamento;
use Modules\Commerce\Models\Ordine;
use Modules\Memorial\Models\Defunto;
use Modules\TributeVideo\Jobs\GeneraVideoMemoriale;
use Modules\TributeVideo\Models\VideoMemoriale;

/**
 * Il Video Memoriale legato a un defunto reale, su entrambi i canali:
 *
 * - B2B (agenzie): aperto dalla Scheda Defunto, gate a crediti come
 *   Manifesti/Necrologi/Ricordini (ServizioEditor::CODICI_ORDINE via
 *   Ordine::designerAbilitato()). Il credito scalato all'attivazione del
 *   servizio È il pagamento in questo canale.
 * - B2C (privati): aperto dalla pagina di lavorazione dell'ordine, gate
 *   sull'ordine effettivamente pagato (stato_pagamento===Pagato) che
 *   contiene in riga il prodotto con `has_qr_memorial=true` — NON si può
 *   riusare designerAbilitato() qui: quel metodo include gratis qualunque
 *   servizio su un ordine "kit fisico" senza righe ordine_servizi (decisione
 *   precedente del committente, "col kit il designer è incluso"), che
 *   legherebbe il video a QUALUNQUE kit invece che solo alla photoceramica
 *   con QR — vedi memoria del modulo.
 *
 * Stesso schema di ManifestiController (soloSuo/assicuraAbilitato).
 * Diversamente dagli altri passi del percorso canalizzato non richiede la
 * foto principale pronta: le foto per il video sono un set dedicato (serve
 * più di un'inquadratura per il Ken Burns), non il singolo ritratto usato
 * da manifesto/ricordino.
 */
class DefuntoVideoController extends Controller
{
    private const DISK_DIR = 'tributevideo';

    public function show(Request $request, Defunto $defunto): View
    {
        $this->soloSuo($request, $defunto);
        $this->assicuraAbilitato($defunto);

        $video = VideoMemoriale::where('defunto_id', $defunto->id)->latest()->first();

        return view('tributevideo::defunti.show', [
            'defunto' => $defunto,
            'video' => $video,
            'modifica' => false,
        ]);
    }

    /**
     * Riapre l'editor timeline precompilato sul video esistente.
     *
     * Se il video non esiste affatto è un vero 404 (link mai valido). Se
     * invece esiste ma è "in_coda"/"in_elaborazione" — es. l'utente ha
     * cliccato "Modifica" un istante prima che un altro giro di render
     * partisse — non è un errore: rimandiamo alla pagina di stato con un
     * messaggio, non un 404 secco che sembra un link rotto.
     */
    public function edit(Request $request, Defunto $defunto): View|RedirectResponse
    {
        $this->soloSuo($request, $defunto);
        $this->assicuraAbilitato($defunto);

        $video = VideoMemoriale::where('defunto_id', $defunto->id)->latest()->first();

        abort_unless($video, 404);

        if (! $video->modificabile()) {
            return redirect()
                ->route('defunti.video-memoriale.show', $defunto)
                ->with('stato', 'Il video è di nuovo in lavorazione: si può modificare solo a render concluso.');
        }

        return view('tributevideo::defunti.show', [
            'defunto' => $defunto,
            'video' => $video,
            'modifica' => true,
        ]);
    }

    public function store(Request $request, Defunto $defunto): RedirectResponse
    {
        $this->soloSuo($request, $defunto);
        $ordine = $this->assicuraAbilitato($defunto);

        $request->validate([
            'citazione' => ['nullable', 'string', 'max:1000'],
            'foto' => ['required', 'array', 'min:1'],
            'foto.*' => ['image', 'max:12288'],   // 12 MB, come il Foto Manager
            'testo' => ['nullable', 'array'],
            'testo.*' => ['nullable', 'string', 'max:180'],
            'posizione' => ['nullable', 'array'],
            'posizione.*' => ['nullable', 'in:alto,centro,basso'],
            'durata' => ['nullable', 'array'],
            'durata.*' => ['nullable', 'integer', 'min:1', 'max:15'],
            'zoom' => ['nullable', 'array'],
            'zoom.*' => ['nullable', 'boolean'],
            'audio' => ['nullable', 'file', 'mimes:mp3,wav,m4a,ogg', 'max:20480'], // 20 MB
        ], [
            'foto.required' => 'Serve almeno una fotografia.',
            'foto.*.image' => 'Uno dei file non è un\'immagine.',
            'foto.*.max' => 'Una foto supera i 12 MB: ridimensionala e riprova.',
            'audio.mimes' => 'Formato audio non riconosciuto (mp3, wav, m4a, ogg).',
            'audio.max' => 'L\'audio supera i 20 MB.',
        ]);

        $token = VideoMemoriale::nuovoToken();

        $audioPath = null;
        if ($request->hasFile('audio')) {
            $audioPath = $request->file('audio')->store(self::DISK_DIR.'/'.$token.'/audio', 'public');
        }

        // In transazione: stesso motivo di TestController::store, niente
        // video "fantasma" (in coda, zero foto) se un upload fallisce a metà.
        $video = DB::transaction(function () use ($request, $token, $audioPath, $defunto, $ordine) {
            $video = VideoMemoriale::create([
                'token' => $token,
                'defunto_id' => $defunto->id,
                'ordine_id' => $ordine->id,
                'nome_completo' => $defunto->nomeCompleto(),
                'date' => $this->periodoLeggibile($defunto),
                'citazione' => $request->input('citazione'),
                'audio_path' => $audioPath,
                'stato' => 'in_coda',
                'render_avviato_il' => now(),
            ]);

            foreach ($request->file('foto') as $ordine => $file) {
                $path = $file->store(self::DISK_DIR.'/'.$token.'/foto', 'public');
                $testo = trim((string) $request->input("testo.$ordine")) ?: null;
                $video->foto()->create([
                    'path' => $path,
                    'ordine' => $ordine,
                    'testo' => $testo,
                    'testo_posizione' => $testo ? $request->input("posizione.$ordine") : null,
                    'durata_secondi' => $request->input("durata.$ordine"),
                    'zoom_attivo' => $request->boolean("zoom.$ordine", true),
                ]);
            }

            return $video;
        });

        GeneraVideoMemoriale::dispatch($video->id);

        return redirect()->route('defunti.video-memoriale.show', $defunto);
    }

    /**
     * Aggiorna il video esistente (stesso record, stesso token → stesso link
     * pubblico/QR) invece di crearne uno nuovo, e ri-lancia il render.
     *
     * L'editor manda un'unica sequenza ordinata (campo `sequenza`, JSON) che
     * interleaves foto già salvate ("esistente", riferite per id) e foto
     * appena caricate ("nuova", riferite per indice nell'array file `foto[]`)
     * — è l'unico modo per esprimere un ordine unificato quando i file nuovi
     * arrivano in un array multipart separato da quelli già su disco.
     */
    public function update(Request $request, Defunto $defunto): RedirectResponse
    {
        $this->soloSuo($request, $defunto);
        $this->assicuraAbilitato($defunto);

        $video = VideoMemoriale::where('defunto_id', $defunto->id)->latest()->first();
        abort_unless($video && $video->modificabile(), 404);

        $request->merge(['sequenza' => json_decode((string) $request->input('sequenza'), true) ?? []]);

        $request->validate([
            'citazione' => ['nullable', 'string', 'max:1000'],
            'audio' => ['nullable', 'file', 'mimes:mp3,wav,m4a,ogg', 'max:20480'],
            'foto' => ['nullable', 'array'],
            'foto.*' => ['image', 'max:12288'],
            'sequenza' => ['required', 'array', 'min:1'],
            'sequenza.*.tipo' => ['required', 'in:esistente,nuova'],
            'sequenza.*.testo' => ['nullable', 'string', 'max:180'],
            'sequenza.*.posizione' => ['nullable', 'in:alto,centro,basso'],
            'sequenza.*.durata' => ['nullable', 'integer', 'min:1', 'max:15'],
            'sequenza.*.zoom' => ['nullable', 'boolean'],
            'sequenza.*.id' => ['nullable', 'integer'],
            'sequenza.*.indice' => ['nullable', 'integer', 'min:0'],
        ], [
            'sequenza.required' => 'Serve almeno una fotografia.',
            'foto.*.image' => 'Uno dei file non è un\'immagine.',
            'foto.*.max' => 'Una foto supera i 12 MB: ridimensionala e riprova.',
            'audio.mimes' => 'Formato audio non riconosciuto (mp3, wav, m4a, ogg).',
            'audio.max' => 'L\'audio supera i 20 MB.',
        ]);

        $sequenza = collect($request->input('sequenza'));
        $nuoviFile = $request->file('foto', []);

        abort_if(
            $sequenza->contains(fn ($voce) => ($voce['tipo'] ?? null) === 'esistente' && ! isset($voce['id']))
                || $sequenza->contains(fn ($voce) => ($voce['tipo'] ?? null) === 'nuova' && ! isset($nuoviFile[$voce['indice'] ?? -1])),
            422,
            'Sequenza foto incoerente.'
        );

        $idEsistentiMantenuti = $sequenza->where('tipo', 'esistente')->pluck('id')->all();

        $audioPath = $video->audio_path;
        if ($request->hasFile('audio')) {
            if ($audioPath) {
                Storage::disk('public')->delete($audioPath);
            }
            $audioPath = $request->file('audio')->store(self::DISK_DIR.'/'.$video->token.'/audio', 'public');
        }

        DB::transaction(function () use ($video, $sequenza, $nuoviFile, $idEsistentiMantenuti, $audioPath, $request) {
            // Le foto tolte dall'editor (drag alla × / mai ripresentate nella
            // sequenza) vanno rimosse davvero, file su disco compreso.
            $video->foto()->whereNotIn('id', $idEsistentiMantenuti)->get()->each(function ($foto) {
                Storage::disk('public')->delete($foto->path);
                $foto->delete();
            });

            foreach ($sequenza->values() as $ordine => $voce) {
                $testo = trim((string) ($voce['testo'] ?? '')) ?: null;
                $attributi = [
                    'ordine' => $ordine,
                    'testo' => $testo,
                    'testo_posizione' => $testo ? ($voce['posizione'] ?? 'basso') : null,
                    'durata_secondi' => $voce['durata'] ?? null,
                    'zoom_attivo' => (bool) ($voce['zoom'] ?? true),
                ];

                if ($voce['tipo'] === 'esistente') {
                    $video->foto()->whereKey($voce['id'])->update($attributi);
                } else {
                    $path = $nuoviFile[$voce['indice']]->store(self::DISK_DIR.'/'.$video->token.'/foto', 'public');
                    $video->foto()->create($attributi + ['path' => $path]);
                }
            }

            $video->forceFill([
                'citazione' => $request->input('citazione'),
                'audio_path' => $audioPath,
                'stato' => 'in_coda',
                'render_avviato_il' => now(),
                'messaggio_errore' => null,
            ])->save();
        });

        GeneraVideoMemoriale::dispatch($video->id);

        return redirect()->route('defunti.video-memoriale.show', $defunto);
    }

    /** "1939 - 2026", o solo l'anno disponibile, o null se mancano entrambi. */
    private function periodoLeggibile(Defunto $defunto): ?string
    {
        if (! $defunto->data_nascita && ! $defunto->data_morte) {
            return null;
        }

        return trim(
            ($defunto->data_nascita?->format('Y') ?? '').' - '.($defunto->data_morte?->format('Y') ?? ''),
            ' -'
        ) ?: null;
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
     * il primo ordine di questo defunto che lo abilita, a cui il video nasce
     * agganciato — a crediti (B2B) o pagato con la photoceramica QR (B2C).
     */
    private function assicuraAbilitato(Defunto $defunto): Ordine
    {
        $ordini = Ordine::where('defunto_id', $defunto->id)
            ->with(['servizi.servizioEditor', 'righe.product'])
            ->get();

        $ordine = $ordini->first(fn (Ordine $o) => $o->designerAbilitato('video-memoriale') && $o->agenzia_id !== null)
            ?? $ordini->first(fn (Ordine $o) => $this->qrPagato($o));

        abort_unless($ordine !== null, 403, 'Il Video Memoriale non è disponibile per questo defunto.');

        return $ordine;
    }

    /** B2C: l'ordine è pagato davvero e contiene la photoceramica con QR. */
    private function qrPagato(Ordine $ordine): bool
    {
        return $ordine->stato_pagamento === StatoPagamento::Pagato
            && $ordine->righe->contains(fn ($riga) => $riga->product?->has_qr_memorial === true);
    }
}
