<?php

namespace Modules\Memorial\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Modules\Commerce\Models\Ordine;
use Modules\Memorial\Models\Defunto;
use Modules\Memorial\Models\Manifesto;
use Modules\Memorial\Models\Necrologio;

/**
 * La scheda del defunto: hub unico del percorso canalizzato Foto → Manifesto
 * → Necrologio (+ Ricordino, sempre disponibile appena la foto è pronta) e
 * dei necrologi extra (trigesimo, anniversario). Pensata per crescere: in
 * futuro altri servizi si aggiungeranno come nuovi passi qui, non come nuove
 * sezioni sparse nell'account.
 */
class DefuntiController extends Controller
{
    /**
     * "I miei defunti": unico punto d'ingresso cliccabile verso la Scheda
     * Defunto — prima si arrivava solo per redirect da "Acquisto Servizi" →
     * Lavorazione → form dati-defunto, mai da un elenco.
     */
    public function index(Request $request): View
    {
        $utente = $request->user();

        $ordini = Ordine::whereNotNull('defunto_id')
            ->when(! $utente->eStaff(), function ($query) use ($utente) {
                $query->where(function ($query) use ($utente) {
                    $query->where('user_id', $utente->id);

                    if ($utente->agenzia_id) {
                        $query->orWhere('agenzia_id', $utente->agenzia_id);
                    }
                });
            })
            ->get(['id', 'defunto_id']);

        $defunti = Defunto::whereIn('id', $ordini->pluck('defunto_id')->unique())
            ->select(['id', 'nome', 'cognome', 'data_nascita', 'data_morte', 'anni'])
            ->latest('id')
            ->paginate(20);

        return view('memorial::defunti.index', ['defunti' => $defunti]);
    }

    public function show(Request $request, Defunto $defunto): View
    {
        $ordini = $this->soloSuo($request, $defunto);

        // Niente `LavorazioneCorrente::imposta()` qui: Memorial non deve
        // dipendere da PhotoPrint (che già dipende da Memorial — vedi
        // Defunto::fotoPrincipalePath()). La sessione è già quella giusta se
        // si arriva da "Nuovo Ordine" (la pagina di lavorazione l'ha appena
        // impostata prima del redirect); altrimenti i link Foto/Ricordino
        // ripiegano da soli sull'archivio delle pratiche.
        $ordinePrincipale = Ordine::find($defunto->ordine_id) ?? $ordini->sortByDesc('id')->first();

        $fotoPath = $defunto->fotoPrincipalePath();
        $fotoPronta = $fotoPath !== null;
        $fotoBloccata = $ordinePrincipale?->fotoBloccata() ?? false;

        $manifestiAbilitato = $ordini->contains(fn (Ordine $o) => $o->designerAbilitato('manifesti'));
        // select() ristretta: 'canvas' è il layout Fabric.js in JSON, può
        // pesare diversi MB e manda MySQL in "Out of sort memory" su un
        // ORDER BY se finisce nel SELECT — vedi bug-select-star-sort-memory.
        $manifesti = Manifesto::where('defunto_id', $defunto->id)
            ->select(['id', 'defunto_id', 'etichetta', 'principale', 'web'])
            ->orderByDesc('principale')->latest()->get();

        $necrologiAbilitato = $ordini->contains(fn (Ordine $o) => $o->designerAbilitato('necrologi'));
        $necrologioFunerale = Necrologio::where('defunto_id', $defunto->id)->where('occasione', 'funerale')->first();
        // select() ristretta: stesso motivo dei manifesti sopra, 'card_canvas'
        // e 'manifesto_canvas' sono JSON grandi e qui c'è un ORDER BY (latest()).
        $necrologiAltri = Necrologio::where('defunto_id', $defunto->id)->where('occasione', '!=', 'funerale')
            ->select(['id', 'defunto_id', 'occasione', 'pubblicato', 'og_image'])
            ->latest()->get();

        $ricordinoAbilitato = $ordini->contains(fn (Ordine $o) => $o->designerAbilitato('ricordini'));
        $ricordino = $defunto->ricordini()->latest()->first();

        // Query builder puro, non il model Eloquent di TributeVideo: Memorial
        // non deve mai dipendere da TributeVideo, solo il contrario (stesso
        // schema a senso unico di PhotoPrint→Memorial) — vedi memoria del modulo.
        $videoAbilitato = $ordini->contains(fn (Ordine $o) => $o->designerAbilitato('video-memoriale'));
        $video = DB::table('video_memoriali')
            ->where('defunto_id', $defunto->id)
            ->latest('id')
            ->select(['id', 'token', 'stato', 'messaggio_errore'])
            ->first();

        // Stesso schema del Video Memoriale sopra: Memorial non deve mai
        // dipendere da SocialStory (che già dipende da Memorial), query
        // builder puro invece del model Eloquent.
        $storiaAbilitata = $ordini->contains(fn (Ordine $o) => $o->designerAbilitato('storia-social'));
        $storia = DB::table('storie_social')
            ->where('defunto_id', $defunto->id)
            ->latest('id')
            ->select(['id', 'token', 'canvas'])
            ->first();

        return view('memorial::defunti.show', [
            'defunto' => $defunto,
            'ordinePrincipale' => $ordinePrincipale,

            'fotoPronta' => $fotoPronta,
            'fotoBloccata' => $fotoBloccata,
            'fotoUrl' => $fotoPronta ? '/storage/'.ltrim($fotoPath, '/') : null,

            'manifestiAbilitato' => $fotoPronta && $manifestiAbilitato,
            'manifesti' => $manifesti,

            'necrologioAbilitato' => $manifesti->isNotEmpty() && $necrologiAbilitato,
            'necrologioFunerale' => $necrologioFunerale,
            'necrologiAltri' => $necrologiAltri,

            'ricordinoAbilitato' => $fotoPronta && $ricordinoAbilitato,
            'ricordino' => $ricordino,

            // Non richiede $fotoPronta: le foto del video sono un set dedicato,
            // separato dal ritratto principale usato da manifesto/ricordino.
            'videoAbilitato' => $videoAbilitato,
            'video' => $video,

            'storiaAbilitata' => $storiaAbilitata,
            'storia' => $storia,
        ]);
    }

    /**
     * Chi può vedere/gestire questo defunto: lo staff, o chi ha almeno un
     * ordine legato a questo defunto di cui è titolare (più ordini possono
     * riferirsi allo stesso defunto_id: il kit iniziale e, più avanti,
     * l'acquisto di altri servizi per la stessa persona). Ritorna la
     * collezione degli ordini trovati, riusata dal resto del metodo.
     */
    private function soloSuo(Request $request, Defunto $defunto)
    {
        $utente = $request->user();
        $ordini = Ordine::where('defunto_id', $defunto->id)->with('servizi.servizioEditor')->get();

        if ($utente->eStaff()) {
            return $ordini;
        }

        abort_unless($ordini->contains(fn (Ordine $o) => $o->diChi($utente)), 404);

        return $ordini;
    }
}
