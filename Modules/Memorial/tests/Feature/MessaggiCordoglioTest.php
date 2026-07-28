<?php

namespace Modules\Memorial\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Commerce\Enums\RuoloUtente;
use Modules\Commerce\Models\Agenzia;
use Modules\Memorial\Models\Defunto;
use Modules\Memorial\Models\MessaggioCordoglio;
use Modules\Memorial\Models\Necrologio;
use Tests\TestCase;

/**
 * Messaggi di cordoglio: lasciati senza account sulla pagina del manifesto
 * (il funerale, non la card del trigesimo), solo nome e testo.
 */
class MessaggiCordoglioTest extends TestCase
{
    use RefreshDatabase;

    private function agenziaConReferente(string $ragioneSociale = 'Onoranze Funebri Bianchi S.r.l.', string $partitaIva = '00743110157'): array
    {
        $staff = User::factory()->create();
        $staff->ruolo = RuoloUtente::Staff;
        $staff->save();

        $agenzia = Agenzia::create([
            'ragione_sociale' => $ragioneSociale,
            'partita_iva' => $partitaIva,
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

    private function necrologioPubblico(Agenzia $agenzia): Necrologio
    {
        $defunto = Defunto::create(['nome' => 'Luigia', 'cognome' => 'Rossetti']);
        $n = Necrologio::create([
            'defunto_id' => $defunto->id,
            'agenzia_id' => $agenzia->id,
            'percorso' => Necrologio::componiPercorso($defunto),
            'manifesto' => 'necrologi/manifesti/1-abcd1234.pdf',
        ]);
        $n->autorizzaPubblicazione('Giulia Ferrari', 'figlia');
        $n->pubblica();

        return $n->fresh();
    }

    public function test_si_lascia_un_messaggio_senza_account(): void
    {
        [, $agenzia] = $this->agenziaConReferente();
        $n = $this->necrologioPubblico($agenzia);

        $this->post(route('necrologio.manifesto.messaggio', ['agenzia' => $agenzia->slug, 'percorso' => $n->percorso]), [
            'nome' => 'Maria Verdi',
            'messaggio' => 'Un pensiero affettuoso per Luigia.',
        ])->assertRedirect($n->urlManifesto($agenzia->slug));

        $this->assertDatabaseHas('messaggi_cordoglio', [
            'necrologio_id' => $n->id,
            'nome' => 'Maria Verdi',
            'messaggio' => 'Un pensiero affettuoso per Luigia.',
        ]);
    }

    public function test_il_messaggio_compare_nella_pagina_pubblica(): void
    {
        [, $agenzia] = $this->agenziaConReferente();
        $n = $this->necrologioPubblico($agenzia);
        $n->messaggiCordoglio()->create(['nome' => 'Maria Verdi', 'messaggio' => 'Con affetto.']);

        $this->get($n->urlManifesto($agenzia->slug))
            ->assertOk()
            ->assertSee('Maria Verdi')
            ->assertSee('Con affetto.');
    }

    public function test_il_campo_honeypot_compilato_blocca_il_messaggio(): void
    {
        [, $agenzia] = $this->agenziaConReferente();
        $n = $this->necrologioPubblico($agenzia);

        $this->from($n->urlManifesto($agenzia->slug))
            ->post(route('necrologio.manifesto.messaggio', ['agenzia' => $agenzia->slug, 'percorso' => $n->percorso]), [
                'nome' => 'Spam Bot',
                'messaggio' => 'Comprate qui.',
                'sito_web' => 'http://spam.example',
            ])
            ->assertSessionHasErrors('sito_web');

        $this->assertDatabaseMissing('messaggi_cordoglio', ['nome' => 'Spam Bot']);
    }

    public function test_non_si_lascia_un_messaggio_su_un_necrologio_non_pubblico(): void
    {
        [, $agenzia] = $this->agenziaConReferente();
        $defunto = Defunto::create(['nome' => 'Luigia', 'cognome' => 'Rossetti']);
        $n = Necrologio::create([
            'defunto_id' => $defunto->id,
            'agenzia_id' => $agenzia->id,
            'percorso' => Necrologio::componiPercorso($defunto),
        ]);

        $this->post(route('necrologio.manifesto.messaggio', ['agenzia' => $agenzia->slug, 'percorso' => $n->percorso]), [
            'nome' => 'Maria Verdi',
            'messaggio' => 'Con affetto.',
        ])->assertNotFound();
    }

    public function test_l_agenzia_elimina_un_messaggio_del_proprio_necrologio(): void
    {
        [$referente, $agenzia] = $this->agenziaConReferente();
        $n = $this->necrologioPubblico($agenzia);
        $messaggio = $n->messaggiCordoglio()->create(['nome' => 'Maria Verdi', 'messaggio' => 'Con affetto.']);

        $this->actingAs($referente)
            ->delete(route('necrologi.messaggi.elimina', [$n, $messaggio]))
            ->assertRedirect(route('necrologi.modifica', $n));

        $this->assertDatabaseMissing('messaggi_cordoglio', ['id' => $messaggio->id]);
    }

    public function test_un_altra_agenzia_non_puo_eliminare_il_messaggio(): void
    {
        [, $agenzia] = $this->agenziaConReferente();
        $n = $this->necrologioPubblico($agenzia);
        $messaggio = $n->messaggiCordoglio()->create(['nome' => 'Maria Verdi', 'messaggio' => 'Con affetto.']);

        [$altroReferente] = $this->agenziaConReferente('Casa Funeraria Aurora S.n.c.', '12485671007');

        $this->actingAs($altroReferente)
            ->delete(route('necrologi.messaggi.elimina', [$n, $messaggio]))
            ->assertNotFound();

        $this->assertDatabaseHas('messaggi_cordoglio', ['id' => $messaggio->id]);
    }
}
