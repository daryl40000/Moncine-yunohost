<?php
/**
 * Affichage et validation du profil utilisateur (nom, prénom, pseudo).
 */

declare(strict_types=1);

namespace Moncine;

final class UserProfile
{
    public const MAX_PSEUDO_LENGTH = 40;

    /** Nom affiché dans l’interface : pseudo, sinon « Prénom Nom ». */
    public static function displayName(array $user): string
    {
        $pseudo = trim((string) ($user['pseudo'] ?? ''));
        if ($pseudo !== '') {
            return $pseudo;
        }

        $prenom = trim((string) ($user['prenom'] ?? ''));
        $nom = trim((string) ($user['nom'] ?? ''));
        if ($prenom !== '' && $nom !== '') {
            return $prenom . ' ' . $nom;
        }
        if ($prenom !== '') {
            return $prenom;
        }
        if ($nom !== '') {
            return $nom;
        }

        return 'Utilisateur';
    }

    public static function sanitizePseudo(string $pseudo): string
    {
        $pseudo = trim($pseudo);
        if ($pseudo === '') {
            return '';
        }

        if (mb_strlen($pseudo, 'UTF-8') > self::MAX_PSEUDO_LENGTH) {
            $pseudo = mb_substr($pseudo, 0, self::MAX_PSEUDO_LENGTH, 'UTF-8');
        }

        return $pseudo;
    }

    /**
     * @return true|string
     */
    public static function validateIdentityFields(string $nom, string $prenom, string $pseudo): bool|string
    {
        $nom = trim($nom);
        $prenom = trim($prenom);
        $pseudo = self::sanitizePseudo($pseudo);

        if ($nom === '' && $prenom === '' && $pseudo === '') {
            return 'Indiquez au moins un nom, un prénom ou un pseudo.';
        }

        return true;
    }
}
