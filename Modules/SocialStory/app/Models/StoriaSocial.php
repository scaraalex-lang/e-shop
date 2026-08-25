<?php

namespace Modules\SocialStory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Una storia social: un fotogramma 1080x1920 composto nel designer, condiviso
 * via link pubblico permanente perché l'utente lo pubblichi manualmente su
 * Facebook/Instagram (nessuna integrazione Graph API in questa fase).
 *
 * Il token è l'unica credenziale del link pubblico, stesso pattern di
 * `video_memoriali.token`: nessuno stato-ordine da controllare per la
 * validità, il link resta raggiungibile finché il record esiste.
 */
class StoriaSocial extends Model
{
    protected $table = 'storie_social';

    protected $fillable = [
        'token', 'defunto_id', 'ordine_id', 'agenzia_id', 'canvas', 'anteprima',
    ];

    protected $casts = [
        'canvas' => 'array',
    ];

    public function getRouteKeyName(): string
    {
        return 'token';
    }

    public static function nuovoToken(): string
    {
        return Str::random(64);
    }

    /** C'è già qualcosa da mostrare/condividere? Un canvas vuoto (mai salvato) non basta. */
    public function pronta(): bool
    {
        return ! empty($this->canvas['objects'] ?? []);
    }

    /** Path relativo (mai `Storage::url()`/`url()`, vedi CLAUDE.md): evita l'host sbagliato di APP_URL=localhost. */
    public function anteprimaUrl(): ?string
    {
        return $this->anteprima ? '/storage/'.ltrim($this->anteprima, '/') : null;
    }

    /**
     * Nome del file scaricato: servizio + codice univoco della storia.
     * Senza, il file salvato prende il nome casuale generato al salvataggio
     * (`.../yhe4d0s9.jpg`) e in cartella Download le storie di defunti
     * diversi sono indistinguibili — stesso ragionamento di Reel::nomeFile().
     *
     * Il codice è l'id della riga, non il token: dal file si risale al record
     * e il token resta la credenziale del link pubblico.
     *
     * Qui l'estensione c'è (a differenza dei fratelli su Cloudinary, dove la
     * aggiunge `fl_attachment`): l'anteprima è un file locale servito da
     * /storage, quindi il nome lo impone l'attributo HTML `download`, che
     * vuole il nome completo. Funziona perché è same-origin.
     *
     * Di default l'estensione è quella del file salvato; si può forzare per
     * l'export manuale dal designer, che è un png del canvas e non il jpeg
     * dell'anteprima.
     */
    public function nomeFile(?string $estensione = null): string
    {
        $estensione ??= pathinfo((string) $this->anteprima, PATHINFO_EXTENSION) ?: 'jpg';

        return 'storia-social-'.Str::padLeft((string) $this->id, 6, '0').'.'.$estensione;
    }
}
