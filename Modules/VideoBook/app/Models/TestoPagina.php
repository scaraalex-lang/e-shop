<?php

namespace Modules\VideoBook\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\VideoBook\Support\StileTesto;

/**
 * Un box di testo su una pagina: un titolo, una data, una citazione, spesso
 * sopra una foto con uno sfondo semi-trasparente per restare leggibile
 * ("tipo Tipografia", pannello Strumenti → Box di testo).
 *
 * `slot`: se valorizzato, il box è agganciato a quel riquadro foto (stesso
 * numero di FotoPagina::slot) — non solo per calcolo: nel markup vive
 * proprio DENTRO il contenitore della foto (`.slot-foto`, che ha
 * `overflow:hidden`), quindi non può uscirne nemmeno con un bug di calcolo,
 * lo taglierebbe il contenitore. `x`/`y`/`w`/`h` in quel caso sono relative
 * allo SLOT (0-1 dentro il SUO rettangolo), non alla pagina.
 *
 * Null = box libero su tutta la pagina — un titolo che non sta sopra
 * nessuna foto in particolare — e allora `x`/`y`/`w`/`h` sono relative alla
 * pagina, stesso significato degli slot di PaginaTemplate ma libere.
 */
class TestoPagina extends Model
{
    protected $table = 'videobook_testi';

    protected $fillable = ['pagina_id', 'slot', 'x', 'y', 'w', 'h', 'testo', 'stile'];

    protected $casts = [
        'x'     => 'float',
        'y'     => 'float',
        'w'     => 'float',
        'h'     => 'float',
        'stile' => 'array',
    ];

    public function pagina(): BelongsTo
    {
        return $this->belongsTo(Pagina::class, 'pagina_id');
    }

    /** `stile` con i default applicati alle chiavi mancanti — mai null/parziale verso il frontend. */
    public function stileEffettivo(): array
    {
        return StileTesto::effettivo($this->stile);
    }
}
