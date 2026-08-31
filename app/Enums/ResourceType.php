<?php

namespace App\Enums;

enum ResourceType: string
{
    case GUT = 'GUT';
    case BANQUE = 'Banque';

    /**
     * Get the display label for the resource type
     */
    public function label(): string
    {
        return match ($this) {
            self::GUT => 'GUT (Groupe Univers Télécom)',
            self::BANQUE => 'Banque',
        };
    }

    /**
     * Get a description for the resource type
     */
    public function description(): string
    {
        return match ($this) {
            self::GUT => 'Ressources internes du Groupe Univers Télécom',
            self::BANQUE => 'Financement bancaire externe',
        };
    }

    /**
     * Get all options as array for form select
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }

    /**
     * Get the icon for the resource type
     */
    public function icon(): string
    {
        return match ($this) {
            self::GUT => '🏢',
            self::BANQUE => '🏦',
        };
    }
}
