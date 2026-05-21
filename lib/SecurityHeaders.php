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
    private const SHARE_PATHS = [
        '/partage.php',
        '/partage-film.php',
    ];

    public static function send(): void
    {
        if (PHP_SAPI === 'cli' || headers_sent()) {
            return;
        }

        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

        self::sendContentSecurityPolicy();
        self::sendStrictTransportSecurityIfHttps();

        $path = self::currentPath();
        if (in_array($path, self::SHARE_PATHS, true)) {
            self::sendShareVisitorHeaders();
        }
    }

    /** Pages partagées visiteur : pas d’indexation, pas de mise en cache proxy. */
    public static function sendShareVisitorHeaders(): void
    {
        if (PHP_SAPI === 'cli' || headers_sent()) {
            return;
        }

        header('X-Robots-Tag: noindex, nofollow');
        header('Cache-Control: private, no-store, max-age=0');
        header('Pragma: no-cache');
    }

    private static function sendContentSecurityPolicy(): void
    {
        $directives = [
            "default-src 'self'",
            "script-src 'self'",
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' https: data:",
            "font-src 'self'",
            "form-action 'self'",
            "base-uri 'self'",
            "frame-ancestors 'self'",
        ];

        header('Content-Security-Policy: ' . implode('; ', $directives));
    }

    private static function sendStrictTransportSecurityIfHttps(): void
    {
        if (!self::isHttpsRequest()) {
            return;
        }

        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }

    public static function isHttpsRequest(): bool
    {
        if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
            return true;
        }

        $forwarded = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
        if (is_string($forwarded) && strtolower($forwarded) === 'https') {
            return true;
        }

        return isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443;
    }

    private static function currentPath(): string
    {
        return parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';
    }
}
