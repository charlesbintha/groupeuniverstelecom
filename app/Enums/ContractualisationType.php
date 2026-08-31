<?php

namespace App\Enums;

enum ContractualisationType: string
{
    case BON_COMMANDE = 'Bon de commande';
    case ANNEXES = 'Annexes';

    public function label(): string
    {
        return match ($this) {
            self::BON_COMMANDE => 'Bon de commande',
            self::ANNEXES => 'Annexes',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::BON_COMMANDE => 'Document de type bon de commande',
            self::ANNEXES => 'Document de type annexes contractuelles',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::BON_COMMANDE => 'blue',
            self::ANNEXES => 'purple',
        };
    }

    public static function options(): array
    {
        return [
            self::BON_COMMANDE->value => self::BON_COMMANDE->label(),
            self::ANNEXES->value => self::ANNEXES->label(),
        ];
    }
}
