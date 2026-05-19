<?php
/**
 * Utilisateur courant (session de connexion).
 *
 * Point d’accès pour le code métier (films, envies…) : quel user_id utiliser dans bibliotheque.
 * Délègue à Auth ; redirige vers la connexion si personne n’est connecté.
 */

declare(strict_types=1);

namespace Moncine;

final class UserContext
{
    /** @deprecated Ancien mono-utilisateur (id 1) ; ne plus utiliser. */
    public const DEFAULT_USER_ID = 1;

    /** ID SQLite de l’utilisateur connecté ; arrête la page si session absente. */
    public static function currentUserId(): int
    {
        $id = Auth::currentUserId();
        if ($id > 0) {
            return $id;
        }

        if (Auth::needsSetup()) {
            return 0;
        }

        Auth::enforceWebAccess();
        exit;
    }

    public static function canManageCatalog(): bool
    {
        return Auth::isAdmin();
    }
}
