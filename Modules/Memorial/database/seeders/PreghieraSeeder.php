<?php

namespace Modules\Memorial\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Memorial\Models\Preghiera;

/**
 * Archivio di partenza: testi liturgici di uso comune, tagliati sulla misura
 * di un ricordino (poche righe, niente paragrafi lunghi).
 *
 * Rilanciabile: aggiorna i testi esistenti invece di duplicarli. Da qui in poi
 * si gestiscono da /gestione/preghiere.
 */
class PreghieraSeeder extends Seeder
{
    public function run(): void
    {
        $preghiere = [
            [
                'categoria' => 'Preghiere',
                'titolo'    => 'L\'eterno riposo',
                'testo'     => "L'eterno riposo dona a lui, o Signore,\ne splenda a lui la luce perpetua.\nRiposi in pace. Amen.",
            ],
            [
                'categoria' => 'Preghiere',
                'titolo'    => 'Ave Maria',
                'testo'     => "Ave Maria, piena di grazia,\nil Signore è con te.\nPrega per noi peccatori,\nadesso e nell'ora della nostra morte. Amen.",
            ],
            [
                'categoria' => 'Preghiere',
                'titolo'    => 'Padre nostro',
                'testo'     => "Padre nostro, che sei nei cieli,\nsia fatta la tua volontà,\ncome in cielo così in terra.\nE rimetti a noi i nostri debiti. Amen.",
            ],
            [
                'categoria' => 'Preghiere',
                'titolo'    => 'Salve Regina',
                'testo'     => "Salve, Regina, madre di misericordia,\nvita, dolcezza e speranza nostra, salve.\nMostraci, dopo questo esilio,\nil frutto del tuo seno, Gesù.",
            ],
            [
                'categoria' => 'Preghiere',
                'titolo'    => 'Angelo di Dio',
                'testo'     => "Angelo di Dio, che sei il mio custode,\nillumina, custodisci,\nreggi e governa me,\nche ti fui affidato dalla pietà celeste. Amen.",
            ],
            [
                'categoria' => 'Preghiere',
                'titolo'    => 'Accogli, Signore',
                'testo'     => "Accogli, Signore, nella tua pace\nl'anima del tuo servo,\ne donagli la gioia\nche non conosce tramonto. Amen.",
            ],
            [
                'categoria' => 'Salmi',
                'titolo'    => 'Il Signore è il mio pastore',
                'testo'     => "Il Signore è il mio pastore:\nnon manco di nulla.\nSe dovessi camminare in una valle oscura,\nnon temerei alcun male, perché tu sei con me.",
            ],
            [
                'categoria' => 'Salmi',
                'titolo'    => 'A te levo i miei occhi',
                'testo'     => "A te levo i miei occhi,\na te che abiti nei cieli.\nIl mio aiuto viene dal Signore,\nche ha fatto cielo e terra.",
            ],
            [
                'categoria' => 'Frasi brevi',
                'titolo'    => 'Chi vive nel ricordo',
                'testo'     => "Chi vive nel cuore di chi resta\nnon muore.",
            ],
            [
                'categoria' => 'Frasi brevi',
                'titolo'    => 'Il tuo esempio',
                'testo'     => "Ci resta il tuo esempio,\nla tua bontà, il tuo sorriso:\nun bene che il tempo non consuma.",
            ],
            [
                'categoria' => 'Frasi brevi',
                'titolo'    => 'Ringraziamento',
                'testo'     => "La famiglia, commossa,\nringrazia quanti hanno condiviso\nil proprio dolore.",
            ],
            [
                'categoria' => 'Frasi brevi',
                'titolo'    => 'Nel silenzio',
                'testo'     => "Nel silenzio di ogni giorno\nsei presenza che accompagna\nchi ti ha voluto bene.",
            ],
        ];

        foreach ($preghiere as $i => $dati) {
            Preghiera::updateOrCreate(
                ['titolo' => $dati['titolo']],
                $dati + ['sort_order' => ($i + 1) * 10, 'is_active' => true],
            );
        }
    }
}
