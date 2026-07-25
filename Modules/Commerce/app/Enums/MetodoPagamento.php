<?php

namespace Modules\Commerce\Enums;

/**
 * Come si paga. Scelte con il committente il 25/07/2026: niente bonifico
 * anticipato.
 *
 * `Fattura` è riservato alle agenzie approvate: è il modo in cui lavorano
 * davvero le onoranze funebri, che ordinano durante il mese e pagano dopo.
 */
enum MetodoPagamento: string
{
    case Contrassegno = 'contrassegno';
    case Carta = 'carta';
    case Fattura = 'fattura';

    public function etichetta(): string
    {
        return match ($this) {
            self::Contrassegno => 'Pagamento alla consegna',
            self::Carta => 'Carta di credito',
            self::Fattura => 'Fattura a termini',
        };
    }

    public function spiegazione(): string
    {
        return match ($this) {
            self::Contrassegno => 'Paghi al corriere quando ricevi il pacco.',
            self::Carta => 'Pagamento immediato e sicuro, l\'ordine parte subito.',
            self::Fattura => 'Ti fatturiamo secondo i termini concordati con la tua agenzia.',
        };
    }

    /** Solo la carta passa da un incasso: gli altri due si saldano dopo. */
    public function incassaSubito(): bool
    {
        return $this === self::Carta;
    }

    /** I metodi che questo account può usare. */
    public static function disponibiliPer(bool $agenziaApprovata): array
    {
        return $agenziaApprovata
            ? [self::Fattura, self::Carta, self::Contrassegno]
            : [self::Carta, self::Contrassegno];
    }
}
