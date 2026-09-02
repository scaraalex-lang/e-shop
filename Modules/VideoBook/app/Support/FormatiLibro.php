<?php

namespace Modules\VideoBook\Support;

/**
 * Archivio dei formati fisici di stampa vendibili per il fotolibro:
 * misure ricalcate sulla gamma di mercato dei fotolibri fotografici
 * standard (stessa fascia di taglie di un fornitore di stampa come CEWE),
 * non un elenco inventato — servono a scegliere un formato che poi verrà
 * davvero stampato, quindi devono restare taglie reali e stampabili.
 *
 * Unica fonte: sia la validazione server (EditorController::aggiornaFormato)
 * sia il selettore "Dimensioni libro" lato JS (editor.blade.php, passato
 * come `formatiData`) leggono da qui — le due liste non possono più
 * disallinearsi, a differenza della prima versione del selettore dove erano
 * duplicate.
 *
 * `codice` è la stringa salvata su `videobook_progetti.formato` ("LxH" in
 * cm, vedi quella migration e formatoLibroMm() lato JS/PdfController).
 */
class FormatiLibro
{
    /**
     * @return array<int, array{codice: string, nome: string, larghezza_cm: int, altezza_cm: int, orientamento: string}>
     */
    public static function tutti(): array
    {
        return [
            ['codice' => '15x15', 'nome' => 'Quadrato compatto', 'larghezza_cm' => 15, 'altezza_cm' => 15, 'orientamento' => 'quadrato'],
            ['codice' => '19x15', 'nome' => 'Panoramico compatto', 'larghezza_cm' => 19, 'altezza_cm' => 15, 'orientamento' => 'orizzontale'],
            ['codice' => '21x21', 'nome' => 'Quadrato', 'larghezza_cm' => 21, 'altezza_cm' => 21, 'orientamento' => 'quadrato'],
            ['codice' => '21x28', 'nome' => 'Verticale', 'larghezza_cm' => 21, 'altezza_cm' => 28, 'orientamento' => 'verticale'],
            ['codice' => '28x21', 'nome' => 'Panoramico', 'larghezza_cm' => 28, 'altezza_cm' => 21, 'orientamento' => 'orizzontale'],
            ['codice' => '30x30', 'nome' => 'Quadrato grande', 'larghezza_cm' => 30, 'altezza_cm' => 30, 'orientamento' => 'quadrato'],
            ['codice' => '28x36', 'nome' => 'Verticale grande', 'larghezza_cm' => 28, 'altezza_cm' => 36, 'orientamento' => 'verticale'],
            ['codice' => '38x29', 'nome' => 'Panoramico grande', 'larghezza_cm' => 38, 'altezza_cm' => 29, 'orientamento' => 'orizzontale'],
        ];
    }

    /** Solo i codici ("15x15", …), per la whitelist di validazione. */
    public static function codici(): array
    {
        return array_column(self::tutti(), 'codice');
    }

    /** Formato di partenza finché non esiste una scelta vera in fase d'acquisto (vedi EditorController). */
    public static function default(): string
    {
        return '21x21';
    }
}
