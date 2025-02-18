<?php
namespace App\Enum;

enum etat: string
{
    case EnAttente = 'en_attente';
    case Rejetee = 'rejetee';
    case Acceptee = 'acceptee';

    public function label(): string
    {
        return match ($this) {
            self::EnAttente => 'En attente',
            self::Rejetee => 'Rejetée',
            self::Acceptee => 'Acceptée',
        };
    }
}
