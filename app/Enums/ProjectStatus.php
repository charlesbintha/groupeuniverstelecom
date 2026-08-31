<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case PLANIFIE = 'Planifié';
    case EN_COURS = 'En cours';
    case PAUSE = 'Pause';
    case SUSPENDU = 'Suspendu';
    case MIS_EN_PAUSE = 'Mis en pause';
    case RETARD = 'Retard';
    case TERMINE = 'Terminé';

    /**
     * Libellé français
     */
    public function label(): string
    {
        return $this->value;
    }

    /**
     * Description du statut
     */
    public function description(): string
    {
        return match($this) {
            self::PLANIFIE => 'Projet planifié, pas encore démarré',
            self::EN_COURS => 'Projet en cours de réalisation',
            self::PAUSE => 'Projet temporairement en pause',
            self::SUSPENDU => 'Projet suspendu',
            self::MIS_EN_PAUSE => 'Projet temporairement mis en pause',
            self::RETARD => 'Projet en retard sur son calendrier',
            self::TERMINE => 'Projet terminé et livré',
        };
    }

    /**
     * Badge color (Tailwind)
     */
    public function badgeColor(): string
    {
        return match($this) {
            self::PLANIFIE => 'bg-yellow-100 text-yellow-800',
            self::EN_COURS => 'bg-blue-100 text-blue-800',
            self::PAUSE => 'bg-orange-100 text-orange-800',
            self::SUSPENDU => 'bg-gray-100 text-gray-800',
            self::MIS_EN_PAUSE => 'bg-purple-100 text-purple-800',
            self::RETARD => 'bg-red-100 text-red-800',
            self::TERMINE => 'bg-green-100 text-green-800',
        };
    }

    /**
     * Icône (pour UI)
     */
    public function icon(): string
    {
        return match($this) {
            self::PLANIFIE => '📅',
            self::EN_COURS => '⚙️',
            self::PAUSE => '⏸️',
            self::SUSPENDU => '⛔',
            self::MIS_EN_PAUSE => '⏸️',
            self::RETARD => '⚠️',
            self::TERMINE => '✅',
        };
    }

    /**
     * Le projet est actif (ni pause ni terminé)
     */
    public function isActive(): bool
    {
        return in_array($this, [self::PLANIFIE, self::EN_COURS, self::RETARD]);
    }

    /**
     * Le projet est clos
     */
    public function isClosed(): bool
    {
        return $this === self::TERMINE;
    }

    /**
     * Statuts disponibles pour transition depuis ce statut
     */
    public function allowedTransitions(): array
    {
        return match($this) {
            self::PLANIFIE => [self::EN_COURS, self::PAUSE, self::SUSPENDU, self::MIS_EN_PAUSE, self::RETARD],
            self::EN_COURS => [self::PAUSE, self::SUSPENDU, self::MIS_EN_PAUSE, self::RETARD, self::TERMINE],
            self::PAUSE, self::SUSPENDU, self::MIS_EN_PAUSE, self::RETARD => [self::EN_COURS, self::TERMINE],
            self::TERMINE => [], // Pas de transition depuis terminé
        };
    }

    public static function options(): array
    {
        return array_map(
            fn(self $status) => ['value' => $status->value, 'label' => $status->label()],
            self::cases()
        );
    }
}
