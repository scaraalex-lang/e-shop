<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Un box di testo può nascere agganciato a un riquadro foto della pagina
 * invece che libero su tutta la pagina: `slot` combacia con l'`ordine`
 * dentro `PaginaTemplate::slots`, esattamente come `videobook_foto.slot` —
 * quando è valorizzato, drag e resize del box restano vincolati al
 * rettangolo di quel riquadro (editor.blade.php, limiteBox()), non più
 * all'intera pagina.
 *
 * Nullable: un box senza `slot` resta libero su tutta la pagina, comportamento
 * di prima — un titolo che non sta sopra nessuna foto in particolare.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('videobook_testi', function (Blueprint $table) {
            $table->unsignedTinyInteger('slot')->nullable()->after('pagina_id');
        });
    }

    public function down(): void
    {
        Schema::table('videobook_testi', function (Blueprint $table) {
            $table->dropColumn('slot');
        });
    }
};
