<?php

namespace App\Enums;

enum StatutFinancier: string
{
    case EN_COURS = 'En cours';
    case TERMINE = 'Terminé';
    case NON_DEMARRE = 'Non démarré';

    public function label(): string
    {
        return match ($this) {
            self::EN_COURS => 'En cours',
            self::TERMINE => 'Terminé',
            self::NON_DEMARRE => 'Non démarré',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::EN_COURS => 'Le financement est en cours de traitement',
            self::TERMINE => 'Le financement est finalisé',
            self::NON_DEMARRE => 'Le financement n\'a pas encore démarré',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::EN_COURS => 'blue',
            self::TERMINE => 'green',
            self::NON_DEMARRE => 'yellow',
        };
    }

    public static function options(): array
    {
        return [
            self::EN_COURS->value => self::EN_COURS->label(),
            self::TERMINE->value => self::TERMINE->label(),
            self::NON_DEMARRE->value => self::NON_DEMARRE->label(),
        ];
    }
}
