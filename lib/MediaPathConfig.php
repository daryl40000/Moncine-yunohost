<?php
/**
 * Racine médias : variable d’environnement + surcharge optionnelle en base (admin).
 */

declare(strict_types=1);

namespace Moncine;

final class MediaPathConfig
{
    public const META_ROOT_PATH = 'media_path_root';

    private static ?string $cachedRoot = null;

    public static function defaultRootPath(): string
    {
        return MONCINE_MEDIA_PATH;
    }

    public static function effectiveRootPath(): string
    {
        if (self::$cachedRoot === null) {
            self::$cachedRoot = self::loadRootPathFromSources();
        }

        return self::$cachedRoot;
    }

    public static function forgetCachedRoot(): void
    {
        self::$cachedRoot = null;
    }

    private static function loadRootPathFromSources(): string
    {
        try {
            $migrator = new SchemaMigrator(Database::getInstance());
            $override = trim($migrator->getMetadata(self::META_ROOT_PATH));
            if ($override !== '') {
                return rtrim($override, '/\\');
            }
        } catch (\Throwable) {
            // Base pas encore prête (install).
        }

        return self::defaultRootPath();
    }

    /**
     * @return true|string
     */
    public static function saveRootPath(string $path): bool|string
    {
        $path = rtrim(trim($path), '/\\');
        if ($path === '') {
            return 'Le chemin ne peut pas être vide.';
        }
        if (str_contains($path, '..')) {
            return 'Chemin invalide.';
        }

        $migrator = new SchemaMigrator(Database::getInstance());
        $migrator->setMetadata(self::META_ROOT_PATH, $path);
        self::forgetCachedRoot();

        return true;
    }

    public static function clearOverride(): void
    {
        $migrator = new SchemaMigrator(Database::getInstance());
        $migrator->setMetadata(self::META_ROOT_PATH, '');
        self::forgetCachedRoot();
    }

    /**
     * @return array{ok: bool, message: string, details: list<string>}
     */
    public static function runSelfTest(): array
    {
        $details = [];
        $root = self::effectiveRootPath();
        $details[] = 'Racine : ' . $root;

        $layout = MediaStorage::ensureLayout();
        if ($layout !== true) {
            return ['ok' => false, 'message' => (string) $layout, 'details' => $details];
        }

        $testName = '.moncine_write_test_' . bin2hex(random_bytes(4)) . '.txt';
        $relative = MediaStorage::relativePath('tmp', $testName);
        $storage = new LocalFilesystemObjectStorage();
        $payload = 'Moncine media test ' . date('c');

        try {
            $meta = $storage->put($relative, $payload, 'text/plain');
            $details[] = 'Écriture OK : ' . $meta['relative_path'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Écriture impossible : ' . $e->getMessage(), 'details' => $details];
        }

        $read = $storage->get($relative);
        if ($read !== $payload) {
            $storage->delete($relative);

            return ['ok' => false, 'message' => 'Lecture incorrecte après écriture.', 'details' => $details];
        }
        $details[] = 'Lecture OK (' . strlen($payload) . ' octets)';

        if (!$storage->delete($relative)) {
            return ['ok' => false, 'message' => 'Suppression du fichier test impossible.', 'details' => $details];
        }
        $details[] = 'Suppression OK';

        return ['ok' => true, 'message' => 'Stockage médias opérationnel.', 'details' => $details];
    }
}
