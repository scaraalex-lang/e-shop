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

    /**
     * Le tre occasioni cambiano il titolo/badge della pagina pubblica: il
     * default 'trigesimo' (nessuna occasione impostata) tiene invariato il
     * comportamento di prima.
     */
    public function test_senza_occasione_impostata_il_titolo_resta_quello_del_trigesimo(): void
    {
        [, $agenzia] = $this->agenziaConReferente();
        $n = $this->necrologio($agenzia);
        $n->autorizzaPubblicazione('Giulia Ferrari', 'figlia');
        $n->pubblica();

        $this->get($n->url($agenzia->slug))
            ->assertOk()
            ->assertSee('Trigesimo di Luigia Rossetti')
            ->assertSee('Nel trigesimo della scomparsa');
    }

    public function test_l_occasione_funerale_usa_la_dicitura_coniugata_e_i_dati_della_cerimonia(): void
    {
        [, $agenzia] = $this->agenziaConReferente();
        $defunto = Defunto::create([
            'nome' => 'Luigia', 'cognome' => 'Rossetti', 'sesso' => 'F',
            'cerimonia_at' => Carbon::now()->addDays(2)->setTime(10, 0),
            'chiesa' => 'Chiesa di San Carlo', 'indirizzo_chiesa' => 'Via Roma 1',
        ]);
        $n = Necrologio::create([
            'defunto_id' => $defunto->id, 'agenzia_id' => $agenzia->id,
            'percorso' => Necrologio::componiPercorso($defunto),
            'occasione' => 'funerale',
        ]);
        $n->autorizzaPubblicazione('Giulia Ferrari', 'figlia');
        $n->pubblica();

        $this->get($n->url($agenzia->slug))
            ->assertOk()
            ->assertSee('Luigia Rossetti è venuta a mancare')
            ->assertSee('Chiesa di San Carlo')
            ->assertDontSee('Nel trigesimo della scomparsa');
    }

    public function test_l_occasione_funerale_resta_al_maschile_senza_sesso_registrato(): void
    {
        [, $agenzia] = $this->agenziaConReferente();
        $defunto = Defunto::create(['nome' => 'Mario', 'cognome' => 'Bianchi']);
        $n = Necrologio::create([
            'defunto_id' => $defunto->id, 'agenzia_id' => $agenzia->id,
            'percorso' => Necrologio::componiPercorso($defunto),
            'occasione' => 'funerale',
        ]);
        $n->autorizzaPubblicazione('Anna Bianchi', 'moglie');
        $n->pubblica();

        $this->get($n->url($agenzia->slug))
            ->assertOk()
            ->assertSee('Mario Bianchi è venuto a mancare');
    }

    public function test_l_occasione_anniversario_mostra_il_numero(): void
    {
        [, $agenzia] = $this->agenziaConReferente();
        $n = $this->necrologio($agenzia, ['occasione' => 'anniversario', 'numero_anniversario' => 2]);
        $n->autorizzaPubblicazione('Giulia Ferrari', 'figlia');
        $n->pubblica();

        $this->get($n->url($agenzia->slug))
            ->assertOk()
            ->assertSee('2° Anniversario della scomparsa di Luigia Rossetti')
            ->assertSee('2° anniversario della scomparsa');
    }

    /**
     * Bug trovato in verifica: "Aggiorna orario e luogo" dopo la
     * pubblicazione scriveva su trigesimo_at/luogo/indirizzo del necrologio,
     * ma per il Funerale la pagina pubblica legge quei dati dal defunto
     * (cerimonia_at/chiesa/indirizzo_chiesa) — l'aggiornamento non compariva
     * mai. Vedi NecrologiController::update().
     */
    public function test_aggiornare_orario_e_luogo_del_funerale_dopo_la_pubblicazione_scrive_sul_defunto(): void
    {
        [$referente, $agenzia] = $this->agenziaConReferente();
        $defunto = Defunto::create([
            'nome' => 'Luigia', 'cognome' => 'Rossetti', 'sesso' => 'F',
            'cerimonia_at' => Carbon::now()->addDays(2)->setTime(10, 0),
            'chiesa' => 'Chiesa di San Carlo', 'indirizzo_chiesa' => 'Via Roma 1',
        ]);
        $n = Necrologio::create([
            'defunto_id' => $defunto->id, 'agenzia_id' => $agenzia->id,
            'percorso' => Necrologio::componiPercorso($defunto),
            'occasione' => 'funerale',
        ]);
        $n->autorizzaPubblicazione('Giulia Ferrari', 'figlia');
        $n->pubblica();

        $nuovoOrario = Carbon::now()->addDays(3)->setTime(16, 0);
        $this->actingAs($referente)->patch(route('necrologi.aggiorna', $n), [
            'trigesimo_at' => $nuovoOrario->format('Y-m-d\TH:i'),
            'trigesimo_luogo' => 'Chiesa di San Michele',
            'trigesimo_indirizzo' => 'Via Nuova 2',
        ])->assertRedirect();

        $defunto->refresh();
        $this->assertSame('Chiesa di San Michele', $defunto->chiesa);
        $this->assertSame('Via Nuova 2', $defunto->indirizzo_chiesa);
        $this->assertTrue($nuovoOrario->equalTo($defunto->cerimonia_at));

        // il necrologio non tiene una copia propria per il Funerale: la
        // pagina pubblica legge dal defunto appena aggiornato.
        $this->get($n->fresh()->url($agenzia->slug))
            ->assertOk()
            ->assertSee('Chiesa di San Michele')
            ->assertDontSee('Chiesa di San Carlo');
    }
}
