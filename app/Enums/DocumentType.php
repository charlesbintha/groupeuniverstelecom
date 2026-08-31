<?php

namespace App\Enums;

enum DocumentType: string
{
    case CONTRACTUALISATION = 'Contractualisation';
    case LIVRABLE = 'Livrable';
    case GENERAL = 'Général';
    case AUTRE = 'Autre';

    public function label(): string
    {
        return match ($this) {
            self::CONTRACTUALISATION => 'Contractualisation',
            self::LIVRABLE => 'Livrable',
            self::GENERAL => 'Général',
            self::AUTRE => 'Autre',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::CONTRACTUALISATION => 'Document de contractualisation du projet',
            self::LIVRABLE => 'Document associé à un livrable',
            self::GENERAL => 'Document général du projet',
            self::AUTRE => 'Autre type de document',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::CONTRACTUALISATION => '📄',
            self::LIVRABLE => '📦',
            self::GENERAL => '📁',
            self::AUTRE => '📎',
        };
    }

    public static function options(): array
    {
        return [
            self::CONTRACTUALISATION->value => self::CONTRACTUALISATION->label(),
            self::LIVRABLE->value => self::LIVRABLE->label(),
            self::GENERAL->value => self::GENERAL->label(),
            self::AUTRE->value => self::AUTRE->label(),
        ];
    }
}
