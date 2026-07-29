<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Avviso "c'è un'app dedicata" mostrato su categoria e schede prodotto delle
 * photoceramiche — testo di partenza, modificabile da /gestione/contenuti
 * come gli altri blocchi editoriali (vedi create_contenuti_vetrina_table).
 */
return new class extends Migration
{
    public function up(): void
    {
        $ora = now();

        $valori = [
            'photoceramiche.avviso_titolo' => 'Ordina dall\'app MemorAI',
            'photoceramiche.avviso_testo' => 'Per le photoceramiche abbiamo un\'app dedicata (Web, iOS e Android): puoi ordinare e creare l\'anteprima con l\'intelligenza artificiale direttamente dal telefono.',
            'photoceramiche.avviso_bottone' => 'Apri l\'app',
            'photoceramiche.avviso_url' => 'https://staging.d2bhfx46t69ao9.amplifyapp.com/',
        ];

        foreach ($valori as $chiave => $valore) {
            DB::table('contenuti_vetrina')->updateOrInsert(
                ['chiave' => $chiave],
                ['valore' => $valore, 'created_at' => $ora, 'updated_at' => $ora]
            );
        }
    }

    public function down(): void
    {
        DB::table('contenuti_vetrina')->whereIn('chiave', [
            'photoceramiche.avviso_titolo',
            'photoceramiche.avviso_testo',
            'photoceramiche.avviso_bottone',
            'photoceramiche.avviso_url',
        ])->delete();
    }
};
