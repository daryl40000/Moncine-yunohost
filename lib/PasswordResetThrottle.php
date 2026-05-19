<?php
/**
 * Limite les demandes « mot de passe oublié » (anti-abus / spam).
 */

declare(strict_types=1);

namespace Moncine;

final class PasswordResetThrottle
{
    private const SESSION_KEY = 'moncine_password_reset_throttle';

    private const MAX_ATTEMPTS = 5;

    private const WINDOW_SECONDS = 900;

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

    public static function recordAttempt(string $email): void
    {
        $email = self::normalizeEmail($email);
        if ($email === '') {
            return;
        }

        QuizSession::start();
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
            $entry['attempts'] = [];
        } else {
            $entry['locked_until'] = 0;
            $entry['attempts'] = $attempts;
        }

        $_SESSION[self::SESSION_KEY][self::storageKey($email)] = $entry;
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

    private static function clear(string $email): void
    {
        QuizSession::start();
        unset($_SESSION[self::SESSION_KEY][self::storageKey(self::normalizeEmail($email))]);
    }

    private static function normalizeEmail(string $email): string
    {
        return mb_strtolower(trim($email), 'UTF-8');
    }

    private static function storageKey(string $email): string
    {
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');

        return hash('sha256', 'reset|' . $email . '|' . $ip);
    }
}
