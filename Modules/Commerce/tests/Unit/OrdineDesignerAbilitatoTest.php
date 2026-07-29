<?php

namespace Modules\Commerce\Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Commerce\Database\Seeders\ServiziEditorSeeder;
use Modules\Commerce\Enums\MetodoPagamento;
use Modules\Commerce\Models\Ordine;
use Modules\Commerce\Models\ServizioEditor;
use Tests\TestCase;

/**
 * `Ordine::designerAbilitato()`: un ordine "kit fisico" (nessuna riga in
 * ordine_servizi) sblocca tutto, come deciso dal committente ("col kit
 * trigesimo il designer è incluso"). Un ordine di solo servizio apre solo
 * i designer effettivamente attivati.
 */
class OrdineDesignerAbilitatoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        (new ServiziEditorSeeder)->run();
    }

    private function ordine(): Ordine
    {
        $utente = User::factory()->create();

        return Ordine::create([
            'numero' => Ordine::prossimoNumero(),
            'user_id' => $utente->id,
            'metodo_pagamento' => MetodoPagamento::Fattura->value,
            'totale_pieno' => 0, 'totale_merce' => 0, 'totale' => 0,
            'consegna_nome' => 'Prova', 'consegna_telefono' => '000',
            'consegna_indirizzo' => 'Via Prova 1', 'consegna_cap' => '20121',
            'consegna_citta' => 'Milano', 'consegna_provincia' => 'MI',
        ]);
    }

    public function test_un_ordine_senza_servizi_sblocca_tutti_i_designer(): void
    {
        $ordine = $this->ordine();

        $this->assertTrue($ordine->designerAbilitato('ricordini'));
        $this->assertTrue($ordine->designerAbilitato('manifesti'));
        $this->assertTrue($ordine->designerAbilitato('necrologi'));
    }

    public function test_un_ordine_di_solo_servizio_sblocca_solo_quelli_attivati(): void
    {
        $ordine = $this->ordine();
        $ricordini = ServizioEditor::where('codice', 'ricordini')->firstOrFail();
        $ordine->servizi()->create(['servizio_editor_id' => $ricordini->id, 'costo_crediti' => $ricordini->costo_crediti]);

        $this->assertTrue($ordine->fresh(['servizi'])->designerAbilitato('ricordini'));
        $this->assertFalse($ordine->fresh(['servizi'])->designerAbilitato('manifesti'));
        $this->assertFalse($ordine->fresh(['servizi'])->designerAbilitato('necrologi'));
    }
}
