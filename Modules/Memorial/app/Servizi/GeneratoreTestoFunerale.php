<?php

namespace Modules\Memorial\Servizi;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Memorial\Models\Defunto;

/**
 * Il blocco "Info funerale" del designer manifesti, nel formato editoriale a
 * 4 righe dei necrologi italiani (vedi esempio sotto), invece dei dati grezzi
 * concatenati a mano dal blocco manuale esistente.
 *
 * Solo la resa linguistica passa dall'AI (OpenAI, se configurata): "oggi"/
 * "domani"/la data estesa sono calcolati qui in PHP, non lasciati decidere
 * al modello — è un fatto verificabile, non una scelta di stile, e un errore
 * lì sarebbe un errore sui dati del funerale stampato. All'AI arrivano solo
 * i campi già puliti del defunto, mai un autofill grezzo: composizione e
 * grammatica sono l'unica cosa che le si chiede.
 *
 * Senza OPENAI_API_KEY configurata, o se la chiamata fallisce, si ripiega
 * sullo stesso template deterministico — il pulsante nel designer funziona
 * comunque, solo con una resa meno rifinita.
 *
 * Esempio del formato di destinazione:
 *   I funerali si svolgeranno domani 30 Luglio 2026 alle ore 15:00
 *   Partenza da Via Garibaldi nr. 3, Boscoreale, NA
 *   Parrocchia Immacolata Concezione, Via Chiesa 2, Boscoreale, NA
 *   Cimitero di Boscoreale, Boscoreale, NA
 *
 * Indirizzo di chiesa e cimitero compaiono solo se presenti in scheda —
 * l'operatore può sempre averli lasciati vuoti, il formato regge comunque
 * a 4 righe. "Italia" non deve mai comparire: gli indirizzi raccolti da
 * Google Places arrivano a volte come stringa completa (via, città,
 * provincia, "Italia" in coda) — vedi rimuoviItalia().
 */
class GeneratoreTestoFunerale
{
    public function generaPerDefunto(Defunto $defunto): string
    {
        $dati = $this->preparaDati($defunto);

        if (config('services.openai.key')) {
            $testo = $this->chiediAllAi($dati);
            if ($testo !== null) {
                return $testo;
            }
        }

        return $this->componiTemplate($dati);
    }

    /**
     * Solo campi già verificati in scheda, niente stringhe grezze da
     * ricomporre lato AI. `citta`/`provincia` sono uniche per tutta la
     * cerimonia (partenza, chiesa e cimitero sono quasi sempre nello stesso
     * comune) — vedi migration `add_citta_provincia_to_defunti_table`.
     */
    private function preparaDati(Defunto $defunto): array
    {
        return [
            'quando' => $this->quandoRelativo($defunto->cerimonia_at),
            'ora' => $defunto->cerimonia_at?->format('H:i'),
            'indirizzo' => $this->rimuoviItalia($defunto->indirizzo_cerimonia),
            'chiesa' => $defunto->chiesa,
            'indirizzo_chiesa' => $this->rimuoviItalia($defunto->indirizzo_chiesa),
            'cimitero' => $this->rimuoviItalia($defunto->cimitero),
            'citta' => $defunto->citta,
            // Sigla sempre maiuscola: il form la normalizza già in
            // LavorazioneController, ma un defunto può nascere anche da
            // altre strade (seed, tinker, futuri import).
            'provincia' => $defunto->provincia ? Str::upper($defunto->provincia) : null,
        ];
    }

    /**
     * Toglie "Italia" (e la virgola davanti) quando è l'ultima parola di un
     * indirizzo raccolto da Google Places — che lo mette sempre in coda al
     * formatted_address. Solo quel token: non tocca il resto dell'indirizzo,
     * niente parsing di città/CAP (già scartato altrove nel progetto perché
     * fragile, vedi migration drop_indirizzo_cimitero).
     */
    private function rimuoviItalia(?string $indirizzo): ?string
    {
        if (! $indirizzo) {
            return $indirizzo;
        }

        $pulito = preg_replace('/,?\s*Italia\s*$/iu', '', $indirizzo);

        return trim($pulito) ?: null;
    }

    /**
     * "oggi"/"domani"/"il 30 Luglio 2026" — un fatto calcolabile con
     * certezza dalla data, non una sfumatura linguistica da affidare all'AI.
     */
    private function quandoRelativo(?Carbon $cerimonia): ?string
    {
        if (! $cerimonia) {
            return null;
        }
        if ($cerimonia->isToday()) {
            return 'oggi';
        }
        if ($cerimonia->isTomorrow()) {
            return 'domani';
        }

        return 'il '.$cerimonia->day.' '.Str::ucfirst($cerimonia->translatedFormat('F')).' '.$cerimonia->year;
    }

    private function chiediAllAi(array $dati): ?string
    {
        try {
            $risposta = Http::withToken(config('services.openai.key'))
                ->timeout(15)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => config('services.openai.model'),
                    'temperature' => 0.2,
                    'messages' => [
                        ['role' => 'system', 'content' => $this->promptDiSistema()],
                        ['role' => 'user', 'content' => json_encode($dati, JSON_UNESCAPED_UNICODE)],
                    ],
                ]);

            if (! $risposta->successful()) {
                Log::warning('GeneratoreTestoFunerale: OpenAI ha risposto '.$risposta->status(), [
                    'body' => $risposta->body(),
                ]);

                return null;
            }

            $testo = trim((string) $risposta->json('choices.0.message.content'));

            return $testo !== '' ? $testo : null;
        } catch (\Throwable $e) {
            Log::warning('GeneratoreTestoFunerale: chiamata OpenAI fallita — '.$e->getMessage());

            return null;
        }
    }

    private function promptDiSistema(): string
    {
        return <<<PROMPT
            Componi il testo "informazioni funerali" per un manifesto funerario italiano.
            Ricevi in JSON dati già puliti e verificati: quando (già risolto — "oggi", "domani" o "il [data estesa]": usalo esattamente così, non calcolarlo), ora, indirizzo, chiesa, indirizzo_chiesa, cimitero, citta, provincia.

            Restituisci SOLO il testo finale, esattamente 4 righe in questo formato, senza markdown, saluti o commenti:

            I funerali si svolgeranno {quando} alle ore {ora}
            Partenza da {indirizzo}, {citta}, {provincia}
            {chiesa}, {indirizzo_chiesa}, {citta}, {provincia}
            {cimitero}, {citta}, {provincia}

            Regole:
            - Non inventare né alterare alcun dato fattuale (indirizzi, orari, nomi, città, provincia): riportali esattamente come ricevuti, senza aggiungere o togliere informazioni.
            - Se "chiesa" è vuoto, scrivi solo "Parrocchia" al posto del nome.
            - Se "indirizzo_chiesa" è vuoto, ometti quella parte (niente virgole vuote).
            - Se "cimitero" è vuoto, scrivi solo "Cimitero" al posto del nome.
            - Se "citta" o "provincia" mancano, ometti la parte mancante senza lasciare virgole vuote.
            - Se "quando" o "ora" mancano, scrivi "[data]" o "[ora]" al loro posto.
            - Non scrivere mai "Italia" da nessuna parte nel testo.
            - "indirizzo", "indirizzo_chiesa" e "cimitero" a volte contengono già città e/o provincia al loro interno (indirizzi salvati per intero in passato): se città o provincia compaiono già in una di queste stringhe, non ripeterle di nuovo alla fine della stessa riga — ogni riga deve leggersi come una frase compiuta e naturale, mai con lo stesso nome di città ripetuto due volte.
            - Esattamente 4 righe, non una di più né una di meno.
            PROMPT;
    }

    /**
     * Riserva senza AI (chiave assente o chiamata fallita): stesso schema
     * a 4 righe composto con semplice concatenazione, come il blocco
     * manuale "Info funerale" già esistente nel designer. Indirizzo di
     * chiesa e cimitero compaiono solo se presenti — l'operatore può
     * averli lasciati vuoti, il formato regge comunque.
     */
    private function componiTemplate(array $dati): string
    {
        $citta = $dati['citta'];
        $luogo = collect([$citta, $dati['provincia']])->filter()->implode(', ');

        // Un indirizzo salvato per intero prima del fix su Google Places può
        // già contenere la città al suo interno: se è già lì, non la si
        // ripete in fondo alla riga ("Boscoreale, Via X, Boscoreale, NA"
        // non si legge come una frase compiuta).
        $riga = function (?string ...$parti) use ($luogo, $citta) {
            $parti = collect($parti)->filter();
            $cittaGiaPresente = $citta && $parti->contains(
                fn (string $p) => Str::contains(Str::lower($p), Str::lower($citta))
            );

            return $cittaGiaPresente ? $parti->implode(', ') : $parti->push($luogo)->filter()->implode(', ');
        };

        $quando = $dati['quando'] ?? '[data]';
        $ora = $dati['ora'] ?? '[ora]';

        return implode("\n", [
            "I funerali si svolgeranno {$quando} alle ore {$ora}",
            'Partenza da '.$riga($dati['indirizzo']),
            $riga($dati['chiesa'] ?: 'Parrocchia', $dati['indirizzo_chiesa']),
            $riga($dati['cimitero'] ?: 'Cimitero'),
        ]);
    }
}
