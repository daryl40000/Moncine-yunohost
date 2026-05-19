<?php
/**
 * Limite les tentatives de connexion (protection contre le devinage de mot de passe).
 */

declare(strict_types=1);

namespace Moncine;

final class LoginThrottle
{
    private const SESSION_KEY = 'moncine_login_throttle';

    /** Nombre d’échecs avant blocage temporaire. */
    private const MAX_ATTEMPTS = 8;

    /** Fenêtre de comptage des échecs (secondes). */
    private const WINDOW_SECONDS = 900;

    /** Durée du blocage après dépassement (secondes). */
    private const LOCKOUT_SECONDS = 900;

    public static function isBlocked(string $email): bool
    {
        $entry = self::getEntry($email);
        if ($entry === null) {
            return false;
        }

        $lockedUntil = (int) ($entry['locked_until'] ?? 0);
        if ($lockedUntil > time()) {
            return true;
        }

        if ($lockedUntil > 0 && $lockedUntil <= time()) {
            self::clear($email);
        }

        return false;
    }

    public static function secondsUntilUnblock(string $email): int
    {
        $entry = self::getEntry($email);
        if ($entry === null) {
            return 0;
        }
        $lockedUntil = (int) ($entry['locked_until'] ?? 0);
        $remaining = $lockedUntil - time();

        return $remaining > 0 ? $remaining : 0;
    }

    public static function recordFailure(string $email): void
    {
        $email = self::normalizeEmail($email);
        if ($email === '') {
            return;
        }

        QuizSession::start();
        $key = self::storageKey($email);
        $now = time();
        $entry = self::getEntry($email) ?? ['attempts' => [], 'locked_until' => 0];

        $attempts = is_array($entry['attempts'] ?? null) ? $entry['attempts'] : [];
        $attempts[] = $now;
        $attempts = array_values(array_filter(
            $attempts,
            static fn (int $ts): bool => $ts >= $now - self::WINDOW_SECONDS
        ));

        if (count($attempts) >= self::MAX_ATTEMPTS) {
            $entry['locked_until'] = $now + self::LOCKOUT_SECONDS;
            $attempts = [];
        } else {
            $entry['locked_until'] = 0;
        }

        $entry['attempts'] = $attempts;
        $_SESSION[self::SESSION_KEY][$key] = $entry;
    }

    public static function clearOnSuccess(string $email): void
    {
        self::clear($email);
    }

    private static function clear(string $email): void
    {
        QuizSession::start();
        $key = self::storageKey(self::normalizeEmail($email));
        unset($_SESSION[self::SESSION_KEY][$key]);
    }

    /** @return array{attempts?: list<int>, locked_until?: int}|null */
    private static function getEntry(string $email): ?array
    {
        $email = self::normalizeEmail($email);
        if ($email === '') {
            return null;
        }

        QuizSession::start();
        $bucket = $_SESSION[self::SESSION_KEY] ?? null;
        if (!is_array($bucket)) {
            return null;
        }

        $entry = $bucket[self::storageKey($email)] ?? null;

        return is_array($entry) ? $entry : null;
    }

    private static function normalizeEmail(string $email): string
    {
        return mb_strtolower(trim($email), 'UTF-8');
    }

    private static function storageKey(string $email): string
    {
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');

        return hash('sha256', $email . '|' . $ip);
    }
}
