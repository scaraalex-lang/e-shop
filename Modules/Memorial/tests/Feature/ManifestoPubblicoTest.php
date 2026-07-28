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
 * La pagina pubblica del manifesto: condivide interamente il gate del
 * necrologio (stesso consenso, stesso interruttore, stessa scadenza) — non
 * ha un proprio stato di pubblicazione, è lo stesso evento.
 */
class ManifestoPubblicoTest extends TestCase
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
        ], $attributi));
    }

    public function test_senza_manifesto_caricato_la_pagina_da_404(): void
    {
        [, $agenzia] = $this->agenziaConReferente();
        $n = $this->necrologio($agenzia);
        $n->autorizzaPubblicazione('Giulia Ferrari', 'figlia');
        $n->pubblica();

        $this->get($n->urlManifesto($agenzia->slug))->assertNotFound();
    }

    public function test_col_consenso_e_l_interruttore_il_manifesto_e_pubblico(): void
    {
        [, $agenzia] = $this->agenziaConReferente();
        $n = $this->necrologio($agenzia, ['manifesto' => 'necrologi/manifesti/1-abcd1234.pdf']);
        $n->autorizzaPubblicazione('Giulia Ferrari', 'figlia');
        $n->pubblica();

        $this->get($n->urlManifesto($agenzia->slug))
            ->assertOk()
            ->assertSee('Luigia Rossetti');
    }

    public function test_senza_consenso_il_manifesto_non_e_pubblico(): void
    {
        [, $agenzia] = $this->agenziaConReferente();
        $n = $this->necrologio($agenzia, ['manifesto' => 'necrologi/manifesti/1-abcd1234.pdf']);

        $this->get($n->urlManifesto($agenzia->slug))->assertNotFound();
    }

    public function test_il_manifesto_si_spegne_insieme_al_necrologio_alla_scadenza(): void
    {
        [, $agenzia] = $this->agenziaConReferente();
        $n = $this->necrologio($agenzia, ['manifesto' => 'necrologi/manifesti/1-abcd1234.pdf']);
        $n->autorizzaPubblicazione('Giulia Ferrari', 'figlia');
        $n->pubblica(Carbon::now()->addDay());

        $this->get($n->urlManifesto($agenzia->slug))->assertOk();

        $this->travel(3)->days();

        $this->get($n->urlManifesto($agenzia->slug))->assertNotFound();
    }

    public function test_revocare_il_consenso_del_necrologio_spegne_anche_il_manifesto(): void
    {
        [$referente, $agenzia] = $this->agenziaConReferente();
        $n = $this->necrologio($agenzia, ['manifesto' => 'necrologi/manifesti/1-abcd1234.pdf']);
        $n->autorizzaPubblicazione('Giulia Ferrari', 'figlia');
        $n->pubblica();

        $this->get($n->urlManifesto($agenzia->slug))->assertOk();

        $this->actingAs($referente)->post(route('necrologi.revoca', $n));

        $this->get($n->urlManifesto($agenzia->slug))->assertNotFound();
    }

    public function test_la_pagina_del_manifesto_espone_i_meta_per_whatsapp_e_facebook(): void
    {
        [, $agenzia] = $this->agenziaConReferente();
        $n = $this->necrologio($agenzia, ['manifesto' => 'necrologi/manifesti/1-abcd1234.pdf']);
        $n->autorizzaPubblicazione('Giulia Ferrari', 'figlia');
        $n->pubblica();

        $risposta = $this->get($n->urlManifesto($agenzia->slug))->assertOk();

        $risposta->assertSee('og:title', false);
        $risposta->assertSee('og:description', false);
        $risposta->assertSee('og:url', false);
        $risposta->assertSee('noindex', false);
    }
}
