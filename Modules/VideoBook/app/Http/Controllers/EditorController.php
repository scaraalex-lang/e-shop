<?php

namespace Modules\VideoBook\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Commerce\Models\Ordine;
use Modules\VideoBook\Enums\StatoLibro;
use Modules\VideoBook\Http\Controllers\Concerns\ControllaAccessoLibro;
use Modules\VideoBook\Models\Libro;
use Modules\VideoBook\Models\PaginaTemplate;
use Modules\VideoBook\Support\FormatiLibro;
use Modules\VideoBook\Support\StileTesto;

/**
 * Il ponte fra l'ordine (Commerce) e l'impaginatore: dove nasce il libro di
 * un ordine e dove si apre l'editor già puntato su di esso.
 *
 * A differenza di LavorazioneController (Foto Manager/Ricordino Designer),
 * qui non c'è nessuna scheda del defunto da compilare prima: il video book
 * non è legato a una pratica funebre, è un percorso a sé — un solo libro
 * per ordine, l'id del libro nell'indirizzo basta a sapere su cosa si
 * lavora. Vedi ControllaAccessoLibro per cosa serve per aprirlo e cosa per
 * scaricarne i file.
 */
class EditorController extends Controller
{
    use ControllaAccessoLibro;

    /**
     * Dall'ordine al suo libro: lo crea la prima volta che ci si entra,
     * poi ritrova sempre lo stesso — un solo libro per ordine.
     */
    public function apriDalOrdine(Request $request, Ordine $ordine): RedirectResponse
    {
        $this->assicuraOrdineHaVideoBook($request, $ordine);

        $libro = Libro::firstOrCreate(
            ['ordine_id' => $ordine->id],
            [
                'defunto_id' => $ordine->defunto_id,
                'formato'    => FormatiLibro::default(),
                'stato'      => StatoLibro::Bozza,
            ],
        );

        return redirect()->route('studio.videobook', $libro);
    }

    public function show(Request $request, Libro $libro): View
    {
        $this->assicuraProprio($request, $libro);

        // Comporre e vedere l'anteprima non richiede pagamento; scaricare i
        // file sì (tranne per staff/agenzia) — vedi ControllaAccessoLibro.
        $scaricabile = $this->libroScaricabile($request, $libro);

        $libro->load(['pagine' => fn ($q) => $q->orderBy('ordine'), 'pagine.template', 'pagine.foto', 'pagine.testi', 'video']);

        // I template disponibili nel selettore: predefiniti MemorAI sempre,
        // quelli dell'agenzia (se c'è) in più — stessa regola della lista.
        $templates = PaginaTemplate::visibiliPer($request->user()->agenzia?->id)
            ->inOrdineDiElenco()
            ->get();

        // Stessa forma che l'API restituisce dopo ogni azione (vedi
        // PaginaApiController), così il JS dell'editor non deve distinguere
        // "dati del primo caricamento" da "risposta di una fetch".
        return view('videobook::editor', [
            'libro'         => $libro,
            'libroData'     => [
                'id'          => $libro->id,
                'formato'     => $libro->formato,
                // Il PDF resta visibile per l'anteprima (si apre in una
                // scheda, non forza il salvataggio) anche prima di pagare:
                // solo il bottone "Scarica" del video è vincolato a
                // $scaricabile, vedi videoData sotto e editor.blade.php.
                'pdf_url'     => $libro->pdfUrl(),
                'scaricabile' => $scaricabile,
                'pagine'  => $libro->pagine->map(fn ($p) => [
                    'id'       => $p->id,
                    'ordine'   => $p->ordine,
                    'template' => $p->template ? [
                        'id'          => $p->template->id,
                        'name'        => $p->template->nome,
                        'numero_foto' => $p->template->numero_foto,
                        'slots'       => $p->template->slots,
                    ] : null,
                    'foto' => $p->foto->map(fn ($f) => [
                        'id'             => $f->id,
                        'slot'           => $f->slot,
                        'url'            => $f->url(),
                        'scala'          => $f->scala,
                        'pos_x'          => $f->pos_x,
                        'pos_y'          => $f->pos_y,
                        'didascalia'     => $f->didascalia,
                        'durata_secondi' => $f->durata_secondi,
                        'stile'          => $f->stileEffettivo(),
                    ])->values(),
                    // Box di testo (pannello Strumenti → Box di testo), vedi TestoPagina.
                    'testi' => $p->testi->map(fn ($t) => [
                        'id'    => $t->id,
                        'slot'  => $t->slot,
                        'x'     => $t->x,
                        'y'     => $t->y,
                        'w'     => $t->w,
                        'h'     => $t->h,
                        'testo' => $t->testo,
                        'stile' => $t->stileEffettivo(),
                    ])->values(),
                ])->values(),
            ],
            'templatesData' => $templates->map(fn ($t) => [
                'id'          => $t->id,
                'name'        => $t->nome,
                'numero_foto' => $t->numero_foto,
                // L'anteprima statica (SVG 4:3 pre-generato, vedi
                // GeneratoreAnteprimaTemplate) non basta più da sola: il
                // selettore la sostituisce con un disegno degli `slots`
                // proporzionato al formato del libro in lavorazione — vedi
                // templateAnteprimaHtml() in editor.blade.php.
                'thumbnail'   => $t->anteprimaUrl(),
                'slots'       => $t->slots,
            ])->values(),
            // Archivio delle taglie fisiche di stampa (selettore "Dimensioni
            // libro"): unica fonte anche per la validazione, vedi
            // aggiornaFormato() e Support\FormatiLibro.
            'formatiData' => FormatiLibro::tutti(),
            // Le opzioni del pannello Strumenti (font, bordino, viraggio):
            // stessa lista usata per validare le richieste PUT .../stile.
            'strumentiData' => [
                'font'     => StileTesto::fontDisponibili(),
                'bordi'    => StileTesto::bordiDisponibili(),
                'viraggi'  => StileTesto::viraggiDisponibili(),
                'default'  => StileTesto::default(),
            ],
            // Stessa forma di VideoController::videoPerFrontend(), così il
            // JS non deve distinguere "stato al primo caricamento" da
            // "risposta del polling".
            'videoData' => $libro->video ? [
                'stato'            => $libro->video->stato->value,
                'in_corso'         => $libro->video->inCorso(),
                'url'              => $libro->video->cloudinary_url,
                'download_url'     => $scaricabile ? $libro->video->downloadUrl() : null,
                'messaggio_errore' => $libro->video->messaggio_errore,
            ] : null,
        ]);
    }

    /**
     * Cambia la taglia fisica di stampa del libro (selettore "Dimensioni
     * libro" in editor.blade.php): proprietà del libro, non del template di
     * pagina — vale per tutte le pagine, presenti e future (vedi commento
     * su `formato` nella migration di videobook_progetti).
     */
    public function aggiornaFormato(Request $request, Libro $libro): JsonResponse
    {
        $this->assicuraProprio($request, $libro);

        $validated = $request->validate([
            'formato' => ['required', 'string', Rule::in(FormatiLibro::codici())],
        ]);

        $libro->update(['formato' => $validated['formato']]);

        return response()->json(['success' => true, 'formato' => $libro->formato]);
    }
}
