<?php

namespace App\Enum;

enum StatutVente: string
{
    case BROUILLON = 'brouillon';
    case VALIDEE = 'validee';
    case ANNULEE = 'annulee';

    public function label(): string
    {
        return match($this) {
            self::BROUILLON => 'Brouillon',
            self::VALIDEE => 'Validée',
            self::ANNULEE => 'Annulée',
        };
    }
}
