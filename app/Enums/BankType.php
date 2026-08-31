<?php

namespace App\Enums;

enum BankType: string
{
    case BIS = 'BIS';
    case FBN_BANK = 'FBN Bank';

    /**
     * Get the display label for the bank
     */
    public function label(): string
    {
        return match ($this) {
            self::BIS => 'BIS (Banque Islamique du Sénégal)',
            self::FBN_BANK => 'FBN Bank',
        };
    }

    /**
     * Get a description for the bank
     */
    public function description(): string
    {
        return match ($this) {
            self::BIS => 'Banque Islamique du Sénégal',
            self::FBN_BANK => 'First Bank of Nigeria',
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
     * Get the icon for the bank
     */
    public function icon(): string
    {
        return match ($this) {
            self::BIS => '🕌',
            self::FBN_BANK => '🏦',
        };
    }
}
