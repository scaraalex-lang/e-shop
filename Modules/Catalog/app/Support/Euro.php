<?php

namespace Modules\Catalog\Support;

/**
 * Converte l'euro digitato in un form ("26,00", "26.00", "26") nei centesimi
 * interi che il resto del progetto usa ovunque per il denaro — mai un float.
 */
class Euro
{
    public static function centesimi(?string $valore): ?int
    {
        if ($valore === null || trim($valore) === '') {
            return null;
        }

        $valore = trim($valore);

        // Con la virgola presente si legge all'italiana: punto = migliaia,
        // virgola = decimali ("1.234,56"). Senza virgola il punto, se c'è,
        // è già il separatore decimale ("26.00") — non si tocca.
        $normalizzato = str_contains($valore, ',')
            ? str_replace('.', '', $valore)
            : $valore;
        $normalizzato = str_replace(',', '.', $normalizzato);

        if (! is_numeric($normalizzato)) {
            return null;
        }

        return (int) round(((float) $normalizzato) * 100);
    }

    /** L'inverso: centesimi -> stringa "26,00" per prevalorizzare un form. */
    public static function daCentesimi(?int $centesimi): ?string
    {
        if ($centesimi === null) {
            return null;
        }

        return number_format($centesimi / 100, 2, ',', '');
    }
}
