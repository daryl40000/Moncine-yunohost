<?php
/**
 * Limite les tentatives de validation d’un jeton de partage (anti brute-force).
 */

declare(strict_types=1);

namespace Moncine;

final class ShareLinkRateLimit
{
    private const SESSION_KEY = 'moncine_share_rate';

    private const MAX_ATTEMPTS = 40;

    private const WINDOW_SECONDS = 300;

    public static function allowAttempt(): bool
    {
        return self::countRecent() < self::MAX_ATTEMPTS;
    }

    public static function recordFailure(): void
    {
        self::record();
    }

    public static function resetForTests(): void
    {
        QuizSession::start();
        unset($_SESSION[self::SESSION_KEY]);
    }

    private static function record(): void
    {
        QuizSession::start();
        $bucket = $_SESSION[self::SESSION_KEY] ?? [];
        if (!is_array($bucket)) {
            $bucket = [];
        }
        $attempts = $bucket['failures'] ?? [];
        if (!is_array($attempts)) {
            $attempts = [];
        }
        $attempts[] = time();
        $bucket['failures'] = $attempts;
        $_SESSION[self::SESSION_KEY] = $bucket;
    }

    private static function countRecent(): int
    {
        QuizSession::start();
        $bucket = $_SESSION[self::SESSION_KEY] ?? null;
        if (!is_array($bucket)) {
            return 0;
        }
        $attempts = $bucket['failures'] ?? null;
        if (!is_array($attempts)) {
            return 0;
        }
        $now = time();
        $recent = array_values(array_filter(
            $attempts,
            static fn ($ts): bool => is_int($ts) && $ts >= $now - self::WINDOW_SECONDS
        ));
        if ($recent !== $attempts) {
            $bucket['failures'] = $recent;
            $_SESSION[self::SESSION_KEY] = $bucket;
        }

        return count($recent);
    }
}
