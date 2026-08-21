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
}
