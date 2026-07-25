<?php

namespace Modules\PhotoPrint\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Commerce\Models\Agenzia;
use Modules\Commerce\Models\Ordine;
use Modules\Memorial\Models\Ricordino;
use Modules\PhotoPrint\Mail\BozzaDaApprovare;
use Modules\PhotoPrint\Servizi\LavorazioneCorrente;

/**
 * L'invio della bozza alla famiglia, dal Designer.
 *
 * Sta sotto /admin/api/ e non altrove: è il prefisso che il wrapper su
 * window.fetch dei due blade copre con CSRF e sessione. Un endpoint fuori da
 * lì si becca un 419 e nessuno capisce perché.
 */
class ApprovazioneController extends Controller
{
    public function __construct(private LavorazioneCorrente $lavorazione) {}

    public function invia(Request $request, Ricordino $ricordino): JsonResponse
    {
        $dati = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'immagine' => ['nullable', 'string'],
        ], [
            'email.required' => 'Serve l\'indirizzo email della famiglia.',
            'email.email' => 'L\'indirizzo email non sembra valido.',
        ]);

        if (! $this->puoInviare($request, $ricordino)) {
            return response()->json(['error' => 'Questo ricordino non è tuo, o la pratica è chiusa.'], 403);
        }

        $revisione = $ricordino->inviaInApprovazione(
            $dati['email'],
            $request->user()?->id,
            $this->congelaAnteprima($dati['immagine'] ?? null),
        );

        $link = route('bozza', $revisione->token);
        $inviata = $this->spedisci($revisione->inviata_a, $ricordino, $link, $this->agenziaDi($ricordino));

        return response()->json([
            'success' => true,
            'email' => $revisione->inviata_a,
            'email_inviata' => $inviata,
            'approva_url' => $link,
            'whatsapp_url' => 'https://wa.me/?text='.rawurlencode(
                "Ecco la bozza del ricordino, da guardare e approvare: {$link}"
            ),
        ]);
    }

    /**
     * Può inviare chi sta lavorando quella pratica: il proprietario
     * dell'ordine, oppure lo staff.
     */
    private function puoInviare(Request $request, Ricordino $ricordino): bool
    {
        if ($request->user()?->eStaff()) {
            return true;
        }

        $ordine = $this->lavorazione->ordine();

        return $ordine !== null && $ordine->defunto_id === $ricordino->defunto_id;
    }

    /**
     * L'agenzia per conto della quale sta partendo questa email, se c'è.
     *
     * Si legge dall'ordine della pratica, non da chi ha premuto il pulsante:
     * anche quando manda lo staff, la famiglia deve poter rispondere
     * all'agenzia che sta seguendo quel funerale. Un ordine di un privato non
     * ha agenzia: lì la risposta torna a noi, ed è giusto così.
     */
    private function agenziaDi(Ricordino $ricordino): ?Agenzia
    {
        $ordineId = $this->lavorazione->ordine()?->id ?? $ricordino->defunto?->ordine_id;

        return $ordineId
            ? Ordine::with('agenzia.user')->find($ordineId)?->agenzia
            : null;
    }

    /**
     * Salva l'immagine mandata dal Designer, così la revisione conserva
     * esattamente ciò che la famiglia ha visto.
     */
    private function congelaAnteprima(?string $dataUrl): ?string
    {
        if (! $dataUrl || ! str_starts_with($dataUrl, 'data:')) {
            return null;
        }

        $virgola = strpos($dataUrl, ',');
        $binario = $virgola === false ? null : base64_decode(substr($dataUrl, $virgola + 1), true);

        if (! $binario) {
            return null;
        }

        $path = 'ricordini/revisioni/'.Str::uuid().'.png';
        Storage::disk('public')->put($path, $binario);

        return $path;
    }

    /**
     * L'email alla famiglia.
     *
     * Se la spedizione non riesce non si annulla niente: il link esiste
     * comunque e l'agenzia lo può mandare a mano. Meglio una revisione senza
     * email che un invio perso.
     */
    private function spedisci(string $email, Ricordino $ricordino, string $link, ?Agenzia $agenzia): bool
    {
        try {
            Mail::to($email)->send(new BozzaDaApprovare($ricordino, $link, $agenzia));

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }
}
