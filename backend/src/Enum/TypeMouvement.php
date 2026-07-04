<?php

namespace App\Enum;

enum TypeMouvement: string
{
    case ENTREE = 'entree';
    case SORTIE = 'sortie';
    case AJUSTEMENT = 'ajustement';
    case RETOUR = 'retour';

    public function getLabel(): string
    {
        return match($this) {
            self::ENTREE => 'Entrée',
            self::SORTIE => 'Sortie',
            self::AJUSTEMENT => 'Ajustement',
            self::RETOUR => 'Retour',
        };
    }

    public function getBadgeClass(): string
    {
        return match($this) {
            self::ENTREE => 'success',
            self::SORTIE => 'danger',
            self::AJUSTEMENT => 'warning',
            self::RETOUR => 'info',
        };
    }
}
