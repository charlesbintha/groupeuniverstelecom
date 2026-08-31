<?php

namespace App\Enums;

enum ProjectNature: string
{
    case B2B = 'B2B';
    case B2C = 'B2C';
    case GOV = 'GOV';
    case AUTRES = 'Autres';

    /**
     * Libellé complet
     */
    public function label(): string
    {
        return match($this) {
            self::B2B => 'Business to Business',
            self::B2C => 'Business to Consumer',
            self::GOV => 'Gouvernement / Secteur Public',
            self::AUTRES => 'Autres',
        };
    }

    /**
     * Description
     */
    public function description(): string
    {
        return match($this) {
            self::B2B => 'Projet à destination d\'entreprises clientes',
            self::B2C => 'Projet à destination de consommateurs finaux',
            self::GOV => 'Projet à destination d\'institutions gouvernementales',
            self::AUTRES => 'Autre nature de projet',
        };
    }

    /**
     * Icône (pour UI)
     */
    public function icon(): string
    {
        return match($this) {
            self::B2B => '🏢',
            self::B2C => '👤',
            self::GOV => '🏛️',
            self::AUTRES => '📋',
        };
    }

    /**
     * Badge color
     */
    public function badgeColor(): string
    {
        return match($this) {
            self::B2B => 'bg-purple-100 text-purple-800',
            self::B2C => 'bg-pink-100 text-pink-800',
            self::GOV => 'bg-indigo-100 text-indigo-800',
            self::AUTRES => 'bg-gray-100 text-gray-800',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn(self $nature) => ['value' => $nature->value, 'label' => $nature->label()],
            self::cases()
        );
    }
}
