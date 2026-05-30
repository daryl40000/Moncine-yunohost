<?php
/**
 * Périmètre d’export PDF (phase 10).
 */

declare(strict_types=1);

namespace Moncine;

final class ExportPdfScope
{
    public const COLLECTION = 'collection';

    public const WISHLIST = 'wishlist';

    public static function normalize(string $raw): string
    {
        $raw = strtolower(trim($raw));

        return match ($raw) {
            self::WISHLIST => self::WISHLIST,
            default => self::COLLECTION,
        };
    }
}
