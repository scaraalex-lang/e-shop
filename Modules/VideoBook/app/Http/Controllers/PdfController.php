<?php

namespace Modules\VideoBook\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\VideoBook\Http\Controllers\Concerns\ControllaAccessoLibro;
use Modules\VideoBook\Models\Libro;

/**
 * Il PDF pronto stampa, per il prodotto "Fotoalbum VideoBook": costruito
 * interamente lato client (canvas + jsPDF, stesso approccio di Ricordino
 * Designer) sulle pagine popolate, qui solo salvato — nessun rendering
 * server, a differenza del video non serve ffmpeg.
 */
class PdfController extends Controller
{
    use ControllaAccessoLibro;

    private const DISK_DIR = 'videobook/pdf';

    public function salva(Request $request, Libro $libro)
    {
        $this->assicuraProprio($request, $libro);

        $validated = $request->validate([
            'pdf' => ['required', 'file', 'mimetypes:application/pdf', 'max:51200'], // 50 MB
        ], [
            'pdf.mimetypes' => 'Il file generato non è un PDF valido.',
            'pdf.max' => 'Il PDF supera i 50 MB.',
        ]);

        if ($libro->pdf_path) {
            Storage::disk('public')->delete($libro->pdf_path);
        }

        $path = $validated['pdf']->storeAs(self::DISK_DIR, $libro->id.'.pdf', 'public');
        $libro->forceFill(['pdf_path' => $path])->save();

        return response()->json(['success' => true, 'url' => $libro->pdfUrl()]);
    }
}
