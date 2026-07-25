<?php

namespace Modules\Commerce\Enums;

/**
 * I tre soggetti che entrano nella piattaforma. Non sono permessi granulari:
 * sono i canali (B2C / B2B) più lo staff che li gestisce.
 */
enum RuoloUtente: string
{
    case Privato = 'privato';
    case Agenzia = 'agenzia';
    case Staff = 'staff';

    public function etichetta(): string
    {
        return match ($this) {
            self::Privato => 'Privato',
            self::Agenzia => 'Onoranze funebri',
            self::Staff => 'Staff MemorAI',
        };
    }
}
