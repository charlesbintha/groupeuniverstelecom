<?php

namespace App\Enums;

enum ProjectType: string
{
    case INTERNE = 'Interne';
    case EXTERNE = 'Externe';

    /**
     * Libellé français pour affichage
     */
    public function label(): string
    {
        return match($this) {
            self::INTERNE => 'Projet Interne',
            self::EXTERNE => 'Projet Externe (Client)',
        };
    }

    /**
     * Description du type
     */
    public function description(): string
    {
        return match($this) {
            self::INTERNE => 'Projet interne au groupe GUT (sans client externe)',
            self::EXTERNE => 'Projet client avec opportunité Salesforce',
        };
    }

    /**
     * Un projet externe requiert une opportunité Salesforce
     */
    public function requiresSalesforce(): bool
    {
        return $this === self::EXTERNE;
    }

    /**
     * Badge color (Tailwind)
     */
    public function badgeColor(): string
    {
        return match($this) {
            self::INTERNE => 'bg-blue-100 text-blue-800',
            self::EXTERNE => 'bg-green-100 text-green-800',
        };
    }

    /**
     * Tous les types disponibles pour select
     */
    public static function options(): array
    {
        return array_map(
            fn(self $type) => ['value' => $type->value, 'label' => $type->label()],
            self::cases()
        );
    }
}
