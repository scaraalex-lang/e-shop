<?php

namespace Modules\VideoBook\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Modules\VideoBook\Http\Controllers\Concerns\ControllaAccessoLibro;
use Modules\VideoBook\Models\FotoPagina;
use Modules\VideoBook\Models\Libro;
use Modules\VideoBook\Models\Pagina;
use Modules\VideoBook\Models\PaginaTemplate;
use Modules\VideoBook\Models\TestoPagina;
use Modules\VideoBook\Servizi\AutoCorrezioneFoto;
use Modules\VideoBook\Support\StileTesto;

/**
 * Le pagine di un libro e i due tipi di contenuto che può ospitare: le foto
 * nei riquadri del template (aggiungere/togliere una pagina, cambiare il
 * suo layout, caricare/sostituire/togliere una foto) e i box di testo
 * liberi (TestoPagina) — più lo "stile" condiviso da entrambi
 * (font/bordino/regolazione/viraggio del pannello Strumenti, vedi
 * StileTesto).
 *
 * Sotto /admin/api/videobook/, come PhotoPrint: il wrapper su window.fetch
 * nell'editor allega CSRF e sessione a ogni chiamata su questo prefisso.
 */
class PaginaApiController extends Controller
{
    use ControllaAccessoLibro;

    private const DISK_DIR = 'videobook/foto';

    public function aggiungiPagina(Request $request, Libro $libro)
    {
        $this->assicuraProprio($request, $libro);
        if ($blocco = $this->assicuraModificabile($libro)) {
            return $blocco;
        }

        $validated = $request->validate([
            'template_id' => ['required', 'exists:videobook_page_templates,id'],
        ]);

        $pagina = $libro->pagine()->create([
            'template_id' => $validated['template_id'],
            'ordine'      => ($libro->pagine()->max('ordine') ?? 0) + 1,
        ]);
        $pagina->load('template');

        return response()->json(['success' => true, 'pagina' => $this->paginaPerFrontend($pagina)]);
    }

    /**
     * Il primo passo dell'impaginatore: "da quante pagine si compone il tuo
     * libro?". Crea N pagine vuote (senza layout) tutte insieme, che l'utente
     * riempirà una per una scegliendo il template — separa "quante" da
     * "quale layout per ciascuna", invece di far ripetere il selettore N
     * volte in fila.
     *
     * Solo per un libro ancora senza pagine: è il passo *iniziale*, non un
     * modo per aggiungerne altre a un libro già composto — per quello resta
     * aggiungiPagina() (che chiede subito anche il template).
     */
    public function inizializzaPagine(Request $request, Libro $libro)
    {
        $this->assicuraProprio($request, $libro);
        if ($blocco = $this->assicuraModificabile($libro)) {
            return $blocco;
        }

        if ($libro->pagine()->exists()) {
            return response()->json(['error' => 'Il libro ha già delle pagine.'], 422);
        }

        $validated = $request->validate([
            'numero_pagine' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $pagine = collect(range(1, $validated['numero_pagine']))
            ->map(fn (int $ordine) => $libro->pagine()->create(['ordine' => $ordine]));

        return response()->json([
            'success' => true,
            'pagine'  => $pagine->map(fn (Pagina $p) => $this->paginaPerFrontend($p))->values(),
        ]);
    }

    /**
     * Cambia il layout di una pagina già composta. Le foto nei riquadri che
     * il nuovo template non prevede più non hanno più un posto — si tolgono,
     * file compreso; le altre restano dove sono (stesso numero di slot).
     */
    public function cambiaTemplate(Request $request, Pagina $pagina)
    {
        $this->assicuraProprioPagina($request, $pagina);
        if ($blocco = $this->bloccoSePaginaCompletata($pagina)) {
            return $blocco;
        }

        $validated = $request->validate([
            'template_id' => ['required', 'exists:videobook_page_templates,id'],
        ]);
        $nuovoTemplate = PaginaTemplate::findOrFail($validated['template_id']);

        $pagina->foto()->where('slot', '>', $nuovoTemplate->numero_foto)->get()
            ->each(fn (FotoPagina $f) => $this->eliminaFileEDati($f));

        $pagina->update(['template_id' => $nuovoTemplate->id]);
        $pagina->load(['template', 'foto']);

        return response()->json(['success' => true, 'pagina' => $this->paginaPerFrontend($pagina)]);
    }

    /** Nuovo ordine delle pagine dopo un drag nella striscia laterale. */
    public function riordinaPagine(Request $request, Libro $libro)
    {
        $this->assicuraProprio($request, $libro);
        if ($blocco = $this->assicuraModificabile($libro)) {
            return $blocco;
        }

        $validated = $request->validate([
            'ordine'   => ['required', 'array', 'min:1'],
            'ordine.*' => ['integer'],
        ]);

        foreach ($validated['ordine'] as $indice => $paginaId) {
            $libro->pagine()->where('id', $paginaId)->update(['ordine' => $indice + 1]);
        }

        return response()->json(['success' => true]);
    }

    public function eliminaPagina(Request $request, Pagina $pagina)
    {
        $this->assicuraProprioPagina($request, $pagina);
        if ($blocco = $this->bloccoSePaginaCompletata($pagina)) {
            return $blocco;
        }

        $pagina->foto->each(fn (FotoPagina $f) => Storage::disk('public')->delete($f->path));
        $pagina->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Carica (o sostituisce) la foto di un riquadro. Un file per richiesta,
     * lo slot arriva come campo del form: si trascina la foto direttamente
     * sopra il riquadro da riempire, niente galleria intermedia da gestire.
     */
    public function caricaFoto(Request $request, Pagina $pagina)
    {
        $this->assicuraProprioPagina($request, $pagina);
        $pagina->loadMissing('template', 'libro');
        if ($blocco = $this->assicuraModificabile($pagina->libro)) {
            return $blocco;
        }

        $validated = $request->validate([
            'slot'  => ['required', 'integer', 'min:1'],
            'photo' => ['required', 'image', 'max:12288'],   // 12 MB
        ], [
            'photo.image' => 'Il file non è un\'immagine.',
            'photo.max'   => 'L\'immagine supera i 12 MB: ridimensionala e riprova.',
        ]);

        if (! $pagina->template?->hasSlot((int) $validated['slot'])) {
            return response()->json(['error' => 'Questo riquadro non esiste nel layout della pagina.'], 422);
        }

        // Formato originale (store() non ricodifica mai, tiene solo
        // l'estensione), ma in una sottocartella per ordine: le foto di un
        // libro restano raggruppate, non tutte piatte in un solo mucchio.
        $path = $request->file('photo')->store($this->cartellaFoto($pagina), 'public');

        $esistente = $pagina->fotoNelloSlot((int) $validated['slot']);
        if ($esistente) {
            Storage::disk('public')->delete($esistente->path);
            // Foto nuova: il ritaglio salvato apparteneva all'immagine
            // precedente, non ha senso applicarlo a questa — si riparte dal
            // centro/cover come al primo caricamento.
            $esistente->update(['path' => $path, 'scala' => 1, 'pos_x' => 0.5, 'pos_y' => 0.5]);
            $foto = $esistente;
        } else {
            $foto = $pagina->foto()->create(['slot' => $validated['slot'], 'path' => $path]);
        }

        return response()->json(['success' => true, 'foto' => $this->fotoPerFrontend($foto)]);
    }

    public function aggiornaFoto(Request $request, FotoPagina $foto)
    {
        $this->assicuraProprioFoto($request, $foto);
        if ($blocco = $this->bloccoSeFotoCompletata($foto)) {
            return $blocco;
        }

        $validated = $request->validate([
            'durata_secondi' => ['nullable', 'integer', 'min:1', 'max:255'],
            // Come la foto è inquadrata nel riquadro: drag per posizionare,
            // maniglia per zoomare (editor.blade.php). 0.5/4.0 = da
            // rimpicciolita a 4× ingrandita, coerente con MIN_SCALA/MAX_SCALA
            // lato JS; 1.0 resta il "cover" minimo (nessun margine vuoto).
            'scala'          => ['sometimes', 'numeric', 'min:0.5', 'max:4'],
            'pos_x'          => ['sometimes', 'numeric', 'min:0', 'max:1'],
            'pos_y'          => ['sometimes', 'numeric', 'min:0', 'max:1'],
        ]);

        $foto->update($validated);

        return response()->json(['success' => true, 'foto' => $this->fotoPerFrontend($foto)]);
    }

    public function eliminaFoto(Request $request, FotoPagina $foto)
    {
        $this->assicuraProprioFoto($request, $foto);
        if ($blocco = $this->bloccoSeFotoCompletata($foto)) {
            return $blocco;
        }
        $this->eliminaFileEDati($foto);

        return response()->json(['success' => true]);
    }

    /**
     * Formattazione della foto dal pannello Strumenti: bordino, regolazione
     * (luminosità/contrasto/saturazione), viraggio — tutto tranne
     * ritaglio/posizione, che restano su aggiornaFoto(). Solo le chiavi
     * inviate cambiano, le altre del `stile` esistente restano (merge, non
     * sostituzione: il pannello manda un campo alla volta).
     */
    public function aggiornaStileFoto(Request $request, FotoPagina $foto)
    {
        $this->assicuraProprioFoto($request, $foto);
        if ($blocco = $this->bloccoSeFotoCompletata($foto)) {
            return $blocco;
        }

        $validated = $request->validate($this->regoleStile());

        $foto->update(['stile' => array_merge($foto->stile ?? [], $validated)]);

        return response()->json(['success' => true, 'foto' => $this->fotoPerFrontend($foto)]);
    }

    /**
     * Lo switch "✨" sotto la foto (editor.blade.php): analizza il file su
     * disco e applica luminosita/contrasto/saturazione suggeriti come farebbe
     * a mano aggiornaStileFoto() — stesso merge, stesso `stile` non
     * distruttivo, sempre ripristinabile. Vedi Servizi\AutoCorrezioneFoto per
     * l'algoritmo (classico, non un modello: perché nel commento lì).
     */
    public function autoCorreggiFoto(Request $request, FotoPagina $foto): JsonResponse
    {
        $this->assicuraProprioFoto($request, $foto);
        if ($blocco = $this->bloccoSeFotoCompletata($foto)) {
            return $blocco;
        }

        $suggerimento = (new AutoCorrezioneFoto())->analizza(Storage::disk('public')->path($foto->path));

        $foto->update(['stile' => array_merge($foto->stile ?? [], $suggerimento, ['auto_corretto' => true])]);

        return response()->json(['success' => true, 'foto' => $this->fotoPerFrontend($foto)]);
    }

    /**
     * Aggiunge un box di testo alla pagina ("Strumenti" → Box di testo).
     * Con `slot`: agganciato a quel riquadro foto — non solo vincolato per
     * calcolo, proprio DENTRO il suo contenitore a schermo (vedi
     * editor.blade.php, slotBoxes()/.slot-foto{overflow:hidden}), quindi
     * `x/y/w/h` sono relative allo SLOT stesso (0-1 dentro il suo
     * rettangolo), non alla pagina — non può uscirne nemmeno per un bug di
     * calcolo, lo taglierebbe il contenitore. Senza `slot`: libero su tutta
     * la pagina, coordinate relative alla pagina come prima.
     */
    public function aggiungiTesto(Request $request, Pagina $pagina)
    {
        $this->assicuraProprioPagina($request, $pagina);
        if ($blocco = $this->bloccoSePaginaCompletata($pagina)) {
            return $blocco;
        }

        $validated = $request->validate([
            'slot' => ['sometimes', 'nullable', 'integer', 'min:1'],
        ]);

        $agganciato = false;
        if (! empty($validated['slot'])) {
            $pagina->loadMissing('template');
            if (! $pagina->template?->hasSlot((int) $validated['slot'])) {
                return response()->json(['error' => 'Questo riquadro non esiste nel layout della pagina.'], 422);
            }
            $agganciato = true;
        }

        // Fascia bassa con un piccolo margine: dentro lo slot se agganciato
        // (frazioni del riquadro stesso), a piena pagina altrimenti.
        [$x, $y, $w, $h] = $agganciato ? [0.06, 0.72, 0.88, 0.22] : [0.10, 0.78, 0.80, 0.16];

        $testo = $pagina->testi()->create([
            'slot' => $agganciato ? (int) $validated['slot'] : null,
            'x' => $x, 'y' => $y, 'w' => $w, 'h' => $h,
            'stile' => ['corsivo' => false, 'dimensione' => 130],
        ]);

        return response()->json(['success' => true, 'testo' => $this->testoPerFrontend($testo)]);
    }

    /** Posizione/dimensione (drag/maniglia) e contenuto del box — non lo stile, vedi aggiornaStileTesto(). */
    public function aggiornaTesto(Request $request, TestoPagina $testo)
    {
        $this->assicuraProprioTesto($request, $testo);
        if ($blocco = $this->bloccoSeTestoCompletato($testo)) {
            return $blocco;
        }

        $validated = $request->validate([
            'x'     => ['sometimes', 'numeric', 'min:0', 'max:1'],
            'y'     => ['sometimes', 'numeric', 'min:0', 'max:1'],
            'w'     => ['sometimes', 'numeric', 'min:0.05', 'max:1'],
            'h'     => ['sometimes', 'numeric', 'min:0.03', 'max:1'],
            'testo' => ['sometimes', 'nullable', 'string', 'max:500'],
        ]);

        // x/y/w/h sono sempre relative al SUO contenitore (lo slot se
        // agganciato, la pagina se libero — vedi il commento su
        // aggiungiTesto()): qui basta che il rettangolo resti dentro 0-1,
        // niente lookup del layout, il contenitore giusto lo sceglie il
        // markup (slotBoxes() vs testiBoxes() in editor.blade.php).
        $validated = $this->vincolaAlContenitore($testo, $validated);

        $testo->update($validated);

        return response()->json(['success' => true, 'testo' => $this->testoPerFrontend($testo)]);
    }

    /**
     * Il rettangolo (x,y,w,h) non deve mai eccedere il suo contenitore
     * (0-1): un resize (w/h nella richiesta) tiene fermo l'angolo (x,y) e
     * accorcia la dimensione che avanza da lì; un trascinamento (x/y) tiene
     * ferma la dimensione e sposta la posizione perché non esca con quella
     * dimensione.
     */
    private function vincolaAlContenitore(TestoPagina $testo, array $validated): array
    {
        if (! array_intersect(['x', 'y', 'w', 'h'], array_keys($validated))) {
            return $validated;
        }

        $x = $validated['x'] ?? $testo->x;
        $y = $validated['y'] ?? $testo->y;
        $w = $validated['w'] ?? $testo->w;
        $h = $validated['h'] ?? $testo->h;

        if (array_key_exists('w', $validated) || array_key_exists('h', $validated)) {
            $w = min($w, 1 - $x);
            $h = min($h, 1 - $y);
        } else {
            $x = max(0, min(1 - $w, $x));
            $y = max(0, min(1 - $h, $y));
        }

        foreach (['x' => $x, 'y' => $y, 'w' => $w, 'h' => $h] as $campo => $valore) {
            if (array_key_exists($campo, $validated)) {
                $validated[$campo] = $valore;
            }
        }

        return $validated;
    }

    /** Stessa logica di aggiornaStileFoto(), per un box di testo. */
    public function aggiornaStileTesto(Request $request, TestoPagina $testo)
    {
        $this->assicuraProprioTesto($request, $testo);
        if ($blocco = $this->bloccoSeTestoCompletato($testo)) {
            return $blocco;
        }

        $validated = $request->validate($this->regoleStile());

        $testo->update(['stile' => array_merge($testo->stile ?? [], $validated)]);

        return response()->json(['success' => true, 'testo' => $this->testoPerFrontend($testo)]);
    }

    public function eliminaTesto(Request $request, TestoPagina $testo)
    {
        $this->assicuraProprioTesto($request, $testo);
        if ($blocco = $this->bloccoSeTestoCompletato($testo)) {
            return $blocco;
        }
        $testo->delete();

        return response()->json(['success' => true]);
    }

    /**
     * L'etichetta libera della card in sidebar ("Chiesa", "Ricevimento"…):
     * senza, la card mostra il nome del template come placeholder — vedi
     * paginaPerFrontend() e la migration di `titolo`.
     */
    public function aggiornaTitoloPagina(Request $request, Pagina $pagina)
    {
        $this->assicuraProprioPagina($request, $pagina);
        if ($blocco = $this->bloccoSePaginaCompletata($pagina)) {
            return $blocco;
        }

        $validated = $request->validate([
            'titolo' => ['nullable', 'string', 'max:60'],
        ]);

        // Stringa vuota = "nessun titolo personalizzato", non un titolo vuoto: torna al placeholder del template.
        $pagina->update(['titolo' => filled($validated['titolo'] ?? null) ? $validated['titolo'] : null]);

        return response()->json(['success' => true, 'pagina' => $this->paginaPerFrontend($pagina)]);
    }

    // ---- Helper ------------------------------------------------------

    private function eliminaFileEDati(FotoPagina $foto): void
    {
        Storage::disk('public')->delete($foto->path);
        $foto->delete();
    }

    /** Sottocartella di DISK_DIR per questo libro: raggruppata per ordine, o per libro se l'ordine non è (ancora) agganciato. */
    private function cartellaFoto(Pagina $pagina): string
    {
        return self::DISK_DIR.'/'.($pagina->libro?->ordine_id ?? 'libro-'.$pagina->videobook_progetto_id);
    }

    private function paginaPerFrontend(Pagina $pagina): array
    {
        return [
            'id'       => $pagina->id,
            'ordine'   => $pagina->ordine,
            'titolo'   => $pagina->titolo,
            'template' => $pagina->template ? [
                'id'          => $pagina->template->id,
                'name'        => $pagina->template->nome,
                'numero_foto' => $pagina->template->numero_foto,
                'slots'       => $pagina->template->slots,
            ] : null,
            'foto'  => $pagina->foto->map(fn (FotoPagina $f) => $this->fotoPerFrontend($f))->values(),
            'testi' => $pagina->testi->map(fn (TestoPagina $t) => $this->testoPerFrontend($t))->values(),
        ];
    }

    private function fotoPerFrontend(FotoPagina $foto): array
    {
        return [
            'id'             => $foto->id,
            'slot'           => $foto->slot,
            'url'            => $foto->url(),
            'scala'          => $foto->scala,
            'pos_x'          => $foto->pos_x,
            'pos_y'          => $foto->pos_y,
            'durata_secondi' => $foto->durata_secondi,
            'stile'          => $foto->stileEffettivo(),
        ];
    }

    private function testoPerFrontend(TestoPagina $testo): array
    {
        return [
            'id'    => $testo->id,
            'slot'  => $testo->slot,
            'x'     => $testo->x,
            'y'     => $testo->y,
            'w'     => $testo->w,
            'h'     => $testo->h,
            'testo' => $testo->testo,
            'stile' => $testo->stileEffettivo(),
        ];
    }

    /**
     * Regole condivise da aggiornaStileFoto() e aggiornaStileTesto(): tutte
     * `sometimes`, il pannello Strumenti manda un campo alla volta (es. solo
     * `grassetto` quando si preme quel bottone), mai l'intero oggetto stile.
     */
    private function regoleStile(): array
    {
        return [
            'font'           => ['sometimes', 'string', Rule::in(StileTesto::fontDisponibili())],
            'dimensione'     => ['sometimes', 'integer', 'min:50', 'max:220'],
            'allineamento'   => ['sometimes', 'string', Rule::in(['left', 'center', 'right'])],
            'grassetto'      => ['sometimes', 'boolean'],
            'sottolineato'   => ['sometimes', 'boolean'],
            'corsivo'        => ['sometimes', 'boolean'],
            'colore'         => ['sometimes', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'bordo'          => ['sometimes', 'nullable', 'string', Rule::in(StileTesto::bordiDisponibili())],
            'luminosita'     => ['sometimes', 'integer', 'min:50', 'max:150'],
            'contrasto'      => ['sometimes', 'integer', 'min:50', 'max:150'],
            'saturazione'    => ['sometimes', 'integer', 'min:0', 'max:200'],
            'viraggio'       => ['sometimes', 'nullable', 'string', Rule::in(StileTesto::viraggiDisponibili())],
            'auto_corretto'  => ['sometimes', 'boolean'],
            'sfondo_colore'  => ['sometimes', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'sfondo_opacita' => ['sometimes', 'integer', 'min:0', 'max:100'],
        ];
    }

    private function assicuraProprioPagina(Request $request, Pagina $pagina): void
    {
        $this->assicuraProprio($request, $pagina->libro);
    }

    private function assicuraProprioFoto(Request $request, FotoPagina $foto): void
    {
        $this->assicuraProprioPagina($request, $foto->pagina);
    }

    private function assicuraProprioTesto(Request $request, TestoPagina $testo): void
    {
        $this->assicuraProprioPagina($request, $testo->pagina);
    }

    // Stesso trio di sopra, ma per il blocco "in produzione" (assicuraModificabile
    // in ControllaAccessoLibro): risponde 422 invece di lanciare, va restituito
    // dal chiamante (`if ($blocco = ...) return $blocco;`), non solo invocato.

    private function bloccoSePaginaCompletata(Pagina $pagina): ?JsonResponse
    {
        return $this->assicuraModificabile($pagina->libro);
    }

    private function bloccoSeFotoCompletata(FotoPagina $foto): ?JsonResponse
    {
        return $this->bloccoSePaginaCompletata($foto->pagina);
    }

    private function bloccoSeTestoCompletato(TestoPagina $testo): ?JsonResponse
    {
        return $this->bloccoSePaginaCompletata($testo->pagina);
    }
}
