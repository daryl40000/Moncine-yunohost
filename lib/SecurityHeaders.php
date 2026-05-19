<?php
/**
 * En-têtes HTTP de sécurité pour les pages web.
 *
 * Complément au code PHP : le navigateur applique ces règles avant d’afficher la page.
 */

declare(strict_types=1);

namespace Moncine;

final class SecurityHeaders
{
    public static function send(): void
    {
        if (PHP_SAPI === 'cli' || headers_sent()) {
            return;
        }

        // Empêche d’afficher Moncine dans une iframe d’un autre site (clickjacking).
        header('X-Frame-Options: SAMEORIGIN');
        // Évite qu’un fichier soit interprété avec un mauvais type MIME.
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        // Désactive géolocalisation / micro / caméra pour cette origine.
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    }
}
