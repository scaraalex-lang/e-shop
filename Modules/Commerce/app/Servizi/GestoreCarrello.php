<?php

namespace Modules\Commerce\Servizi;

use App\Models\User;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Catalog\Models\Product;
use Modules\Commerce\Enums\Occasione;
use Modules\Commerce\Models\Agenzia;
use Modules\Commerce\Models\Carrello;
use Modules\Commerce\Models\RigaCarrello;

/**
 * Trova (o crea) il carrello di chi sta navigando e ci lavora sopra.
 *
 * Chi ha fatto accesso ha un carrello legato all'account; l'ospite ne ha uno
 * legato a un token in sessione. Al login i due si fondono: vedi unisci().
 */
class GestoreCarrello
{
    private const CHIAVE_SESSIONE = 'carrello_token';

    public function __construct(private Session $sessione) {}

    /**
     * Il carrello attuale, SENZA crearne uno.
     *
     * Serve alle pagine che devono solo sapere quanto c'è dentro (il contatore
     * in barra): passare di qui evita di creare una riga a database per ogni
     * visitatore che apre la home.
     */
    public function corrente(): ?Carrello
    {
        if ($utente = Auth::user()) {
            return Carrello::where('user_id', $utente->id)->first();
        }

        $token = $this->sessione->get(self::CHIAVE_SESSIONE);

        return $token ? Carrello::where('token', $token)->first() : null;
    }

    /** Il carrello attuale, creandolo se non c'è ancora. */
    public function correnteOCrea(): Carrello
    {
        if ($esistente = $this->corrente()) {
            return $esistente;
        }

        if ($utente = Auth::user()) {
            return Carrello::create(['user_id' => $utente->id]);
        }

        $token = (string) Str::uuid();
        $this->sessione->put(self::CHIAVE_SESSIONE, $token);

        return Carrello::create(['token' => $token]);
    }

    /** Quanti pezzi ci sono nel carrello: 0 se non esiste nemmeno. */
    public function pezzi(): int
    {
        return $this->corrente()?->loadMissing('righe')->pezzi() ?? 0;
    }

    /**
     * Aggiunge pezzi al carrello. Se il prodotto c'è già, si somma alla riga
     * esistente invece di aprirne una seconda.
     *
     * Un servizio (funerale/trigesimo/anniversario) fa eccezione: un ordine
     * ha un solo defunto quindi una sola occasione, la quantità resta
     * sempre 1 (non si somma) e porta con sé il numero dell'anniversario,
     * se è quello il servizio. Il conflitto tra occasioni diverse nello
     * stesso carrello si controlla PRIMA di chiamare questo metodo (vedi
     * `occasioneNelCarrello()`), qui si assume già validato.
     */
    public function aggiungi(Product $prodotto, int $quantita, ?int $numeroAnniversario = null): RigaCarrello
    {
        $carrello = $this->correnteOCrea();
        $eServizio = Occasione::daSku($prodotto->sku) !== null;

        $riga = $carrello->righe()->firstOrNew(['product_id' => $prodotto->id]);
        $riga->quantita = $eServizio ? 1 : max(($riga->quantita ?? 0) + $quantita, 1);

        if ($eServizio) {
            $riga->numero_anniversario = $numeroAnniversario;
        }

        $riga->save();

        return $riga;
    }

    /** L'occasione già presente nel carrello, se c'è un servizio dentro. */
    public function occasioneNelCarrello(Carrello $carrello): ?Occasione
    {
        foreach ($carrello->righe as $riga) {
            if ($occasione = Occasione::daSku($riga->product?->sku ?? '')) {
                return $occasione;
            }
        }

        return null;
    }

    /**
     * Quanti crediti mancano per confermare questo carrello, `null` se non ne
     * mancano (o non ce n'è bisogno). Un servizio ha `crediti` negativo: la
     * somma di tutti i negativi, in positivo, è quanto costa il carrello.
     * Un privato non ha un saldo su cui attingere: se il carrello consuma
     * crediti, per lui mancano sempre tutti quelli richiesti.
     *
     * @return array{richiesti:int, saldo:int, mancano:int}|null
     */
    public function creditiMancanti(Carrello $carrello, ?Agenzia $agenzia): ?array
    {
        $richiesti = $carrello->righe->sum(
            fn (RigaCarrello $r) => $r->product && $r->product->crediti < 0 ? -$r->product->crediti * $r->quantita : 0
        );

        if ($richiesti === 0) {
            return null;
        }

        $saldo = $agenzia?->creditiSaldo() ?? 0;

        if ($saldo >= $richiesti) {
            return null;
        }

        return ['richiesti' => $richiesti, 'saldo' => $saldo, 'mancano' => $richiesti - $saldo];
    }

    /** Cambia la quantità di una riga. A zero, la riga sparisce. */
    public function aggiorna(RigaCarrello $riga, int $quantita): void
    {
        if ($quantita <= 0) {
            $riga->delete();

            return;
        }

        $riga->update(['quantita' => $quantita]);
    }

    public function svuota(Carrello $carrello): void
    {
        $carrello->righe()->delete();
    }

    /**
     * Fonde il carrello dell'ospite in quello dell'account, al momento
     * dell'accesso.
     *
     * Le quantità si sommano: chi aveva messo 20 ricordini da ospite e ne
     * trova 30 nel proprio carrello se ne ritrova 50, che è quello che si
     * aspetta chi ha aggiunto due volte la stessa cosa.
     */
    public function unisci(User $utente): void
    {
        $token = $this->sessione->pull(self::CHIAVE_SESSIONE);

        if (! $token) {
            return;
        }

        $ospite = Carrello::with('righe')->where('token', $token)->first();

        if (! $ospite) {
            return;
        }

        DB::transaction(function () use ($ospite, $utente) {
            $suo = Carrello::firstOrCreate(['user_id' => $utente->id]);

            foreach ($ospite->righe as $riga) {
                $destinazione = $suo->righe()->firstOrNew(['product_id' => $riga->product_id]);
                $destinazione->quantita = ($destinazione->quantita ?? 0) + $riga->quantita;
                $destinazione->save();
            }

            $ospite->delete();
        });
    }
}
