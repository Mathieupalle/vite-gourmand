<?php
declare(strict_types=1);

namespace App\Entity;

final class CommandeStatus
{
    public const EN_ATTENTE              = 'en_attente';
    public const ACCEPTE                 = 'accepte';
    public const EN_PREPARATION          = 'en_preparation';
    public const EN_COURS_LIVRAISON      = 'en_cours_livraison';
    public const LIVRE                   = 'livre';
    public const ATTENTE_RETOUR_MATERIEL = 'attente_retour_materiel';
    public const TERMINEE                = 'terminee';
    public const ANNULEE                 = 'annulee';

    public static function all(): array
    {
        return [
            self::EN_ATTENTE,
            self::ACCEPTE,
            self::EN_PREPARATION,
            self::EN_COURS_LIVRAISON,
            self::LIVRE,
            self::ATTENTE_RETOUR_MATERIEL,
            self::TERMINEE,
            self::ANNULEE,
        ];
    }

    public static function isValid(string $statut): bool
    {
        return in_array($statut, self::all(), true);
    }
}