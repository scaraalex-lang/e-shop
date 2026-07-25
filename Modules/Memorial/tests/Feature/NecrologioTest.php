<?php

namespace Modules\Memorial\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Modules\Commerce\Enums\RuoloUtente;
use Modules\Commerce\Models\Agenzia;
use Modules\Memorial\Models\Defunto;
use Modules\Memorial\Models\Necrologio;
use Tests\TestCase;

/**
 * Il necrologio pubblico.
 *
 * La regola che questi test difendono: una pagina è visibile SOLO se
 * ricorrono tre condizioni insieme — consenso alla pubblicazione,
 * interruttore acceso, scadenza non passata. Nessuna basta da sola.
 */
class NecrologioTest extends TestCase
{
    use RefreshDatabase;

    private function agenziaConReferente(): array
    {
        $staff = User::factory()->create();
        $staff->ruolo = RuoloUtente::Staff;
        $staff->save();

        $agenzia = Agenzia::create([
            'ragione_sociale' => 'Onoranze Funebri Bianchi S.r.l.',
            'partita_iva' => '00743110157',
            'indirizzo' => 'Via Roma 12', 'cap' => '20121',
            'citta' => 'Milano', 'provincia' => 'MI', 'telefono' => '0212345678',
        ]);
        $agenzia->approva($staff);

        $referente = User::factory()->create();
        $referente->ruolo = RuoloUtente::Agenzia;
        $referente->agenzia()->associate($agenzia);
        $referente->save();

        return [$referente->fresh(), $agenzia->fresh()];
    }

    private function necrologio(Agenzia $agenzia, array $attributi = []): Necrologio
    {
        $defunto = Defunto::create(['nome' => 'Luigia', 'cognome' => 'Rossetti']);

        return Necrologio::create(array_merge([
            'defunto_id' => $defunto->id,
            'agenzia_id' => $agenzia->id,
            'percorso' => Necrologio::componiPercorso($defunto),
            'trigesimo_at' => Carbon::now()->addDays(10)->setTime(15, 30),
            'trigesimo_luogo' => 'Chiesa di San Carlo',
        ], $attributi));
    }

    public function test_l_agenzia_riceve_uno_spicchio_di_indirizzo(): void
    {
        [, $agenzia] = $this->agenziaConReferente();

        $this->assertSame('onoranze-funebri-bianchi-srl', $agenzia->slug);
    }

    public function test_il_percorso_porta_il_nome_ma_non_si_indovina(): void
    {
        $defunto = Defunto::create(['nome' => 'Luigia', 'cognome' => 'Rossetti']);

        $primo = Necrologio::componiPercorso($defunto);
        $secondo = Necrologio::componiPercorso($defunto);

        $this->assertStringStartsWith('luigia-rossetti-', $primo);
        $this->assertNotSame($primo, $secondo, 'due necrologi omonimi non collidono');
    }

    public function test_senza_consenso_non_si_pubblica(): void
    {
        [$referente, $agenzia] = $this->agenziaConReferente();
        $n = $this->necrologio($agenzia);

        $this->actingAs($referente)->post(route('necrologi.pubblica', $n));

        $n->refresh();
        $this->assertFalse($n->pubblicato);
        $this->assertFalse($n->pubblico());
        $this->get($n->url($agenzia->slug))->assertNotFound();
    }

    public function test_col_consenso_e_l_interruttore_la_pagina_e_pubblica(): void
    {
        [$referente, $agenzia] = $this->agenziaConReferente();
        $n = $this->necrologio($agenzia);

        $this->actingAs($referente)->post(route('necrologi.consenso', $n), [
            'autorizzata_da' => 'Giulia Ferrari', 'parentela' => 'figlia', 'conferma' => '1',
        ]);
        $this->actingAs($referente)->post(route('necrologi.pubblica', $n));

        $n->refresh();
        $this->assertTrue($n->pubblico());

        // da ospite, senza account
        $this->get($n->url($agenzia->slug))
            ->assertOk()
            ->assertSee('Luigia Rossetti')
            ->assertSee('Chiesa di San Carlo');
    }

    public function test_il_consenso_alla_lavorazione_non_vale_come_consenso_a_pubblicare(): void
    {
        [, $agenzia] = $this->agenziaConReferente();
        $n = $this->necrologio($agenzia);

        // il familiare autorizza l'uso di immagine e dati PER GLI ARTICOLI
        $n->defunto->autorizzaGdpr('Giulia Ferrari', 'figlia');

        $this->assertFalse($n->fresh()->pubblicazione_consenso, 'sono due consensi distinti');
        $this->assertFalse($n->fresh()->pubblico());
    }

    public function test_la_pagina_si_spegne_da_sola_alla_scadenza(): void
    {
        [$referente, $agenzia] = $this->agenziaConReferente();
        $n = $this->necrologio($agenzia);
        $n->autorizzaPubblicazione('Giulia Ferrari', 'figlia');
        $n->pubblica(Carbon::now()->addDay());

        $this->get($n->url($agenzia->slug))->assertOk();

        $this->travel(3)->days();

        $this->assertTrue($n->fresh()->scaduto());
        $this->get($n->url($agenzia->slug))->assertNotFound();
    }

    public function test_ritirare_toglie_subito_la_pagina(): void
    {
        [$referente, $agenzia] = $this->agenziaConReferente();
        $n = $this->necrologio($agenzia);
        $n->autorizzaPubblicazione('Giulia Ferrari', 'figlia');
        $n->pubblica();

        $this->get($n->url($agenzia->slug))->assertOk();

        $this->actingAs($referente)->post(route('necrologi.ritira', $n));

        $this->get($n->url($agenzia->slug))->assertNotFound();
    }

    public function test_revocare_il_consenso_spegne_anche_la_pubblicazione(): void
    {
        [$referente, $agenzia] = $this->agenziaConReferente();
        $n = $this->necrologio($agenzia);
        $n->autorizzaPubblicazione('Giulia Ferrari', 'figlia');
        $n->pubblica();

        $this->actingAs($referente)->post(route('necrologi.revoca', $n));

        $n->refresh();
        $this->assertFalse($n->pubblicazione_consenso);
        $this->assertFalse($n->pubblicato);
        $this->get($n->url($agenzia->slug))->assertNotFound();
    }

    public function test_la_pagina_espone_i_meta_per_whatsapp_e_facebook(): void
    {
        [, $agenzia] = $this->agenziaConReferente();
        $n = $this->necrologio($agenzia);
        $n->autorizzaPubblicazione('Giulia Ferrari', 'figlia');
        $n->pubblica();

        $risposta = $this->get($n->url($agenzia->slug))->assertOk();

        $risposta->assertSee('og:title', false);
        $risposta->assertSee('og:description', false);
        $risposta->assertSee('og:url', false);
        // fuori dai motori di ricerca: la pagina e' per chi riceve il link
        $risposta->assertSee('noindex', false);
    }

    public function test_il_necrologio_di_un_altra_agenzia_non_si_tocca(): void
    {
        [$referente] = $this->agenziaConReferente();

        $altra = Agenzia::create([
            'ragione_sociale' => 'Casa Funeraria Aurora S.n.c.',
            'partita_iva' => '12485671007',
            'indirizzo' => 'Corso Vittorio 88', 'cap' => '10121',
            'citta' => 'Torino', 'provincia' => 'TO', 'telefono' => '011998877',
        ]);
        $suo = $this->necrologio($altra);

        $this->actingAs($referente)->get(route('necrologi.modifica', $suo))->assertNotFound();
        $this->actingAs($referente)->post(route('necrologi.ritira', $suo))->assertNotFound();
    }

    public function test_un_privato_non_ha_i_necrologi(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('necrologi.index'))
            ->assertForbidden();
    }
}
