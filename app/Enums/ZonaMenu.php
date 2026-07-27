<?php

namespace App\Enums;

/**
 * Dove compare una voce di navigazione: il menu principale del layout, una
 * delle tre colonne del footer, o la riga dei link legali in fondo.
 */
enum ZonaMenu: string
{
    case Principale = 'principale';
    case FooterCollezioni = 'footer_collezioni';
    case FooterServizi = 'footer_servizi';
    case FooterAssistenza = 'footer_assistenza';
    case Legale = 'legale';

    public function etichetta(): string
    {
        return match ($this) {
            self::Principale => 'Menu principale',
            self::FooterCollezioni => 'Footer — Collezioni',
            self::FooterServizi => 'Footer — Servizi',
            self::FooterAssistenza => 'Footer — Assistenza',
            self::Legale => 'Link legali',
        };
    }
}
