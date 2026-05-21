<?php

declare(strict_types=1);

namespace Moncine\Tests\Integration;

use Moncine\Auth;
use Moncine\BibliothequeRepository;
use Moncine\FilmRepository;
use Moncine\FoyerRepository;
use Moncine\LibraryStatut;
use Moncine\OeuvreEanRepository;
use Moncine\SchemaMigrator;
use Moncine\SupportPhysique;
use Moncine\Tests\Support\MoncineTestCase;
use Moncine\WishlistTargetRepository;

final class WishlistTargetsTest extends MoncineTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        (new SchemaMigrator(\Moncine\Database::getInstance()))->runPendingMigrations();
    }

    public function testAddMultipleSupportsAndPromoteClearsTargets(): void
    {
        $this->loginAsAdmin();
        $userId = \Moncine\UserContext::currentUserId();
        $foyerId = (new FoyerRepository())->currentFoyerIdForUser($userId);

        $oeuvreId = $this->seedCatalogOeuvre('Film Cible Envie', 'Réal Cible');
        $filmId = (new FilmRepository())->addFromCatalogOeuvre($oeuvreId, LibraryStatut::WISHLIST);
        $this->assertIsInt($filmId);

        $targets = new WishlistTargetRepository();
        $this->assertTrue(WishlistTargetRepository::tableExists());

        $dvd = $targets->add($filmId, SupportPhysique::DVD, '3760061234567');
        $this->assertIsInt($dvd);

        $bluray = $targets->add($filmId, SupportPhysique::BLURAY, '');
        $this->assertIsInt($bluray);

        $dup = $targets->add($filmId, SupportPhysique::DVD, '9999999999999');
        $this->assertIsString($dup);

        $list = $targets->listForBibliothequeId($filmId);
        $this->assertCount(2, $list);

        $map = $targets->mapByBibliothequeIds([$filmId]);
        $this->assertCount(2, $map[$filmId] ?? []);

        $this->assertTrue((new BibliothequeRepository())->promoteToCollection(
            $filmId,
            $userId,
            $foyerId,
            SupportPhysique::DVD
        ));
        $this->assertSame([], $targets->listForBibliothequeId($filmId));
    }

    public function testAddFromCatalogOeuvreEan(): void
    {
        $this->loginAsAdmin();

        $oeuvreId = $this->seedCatalogOeuvre('Film EAN Envie', 'Réal EAN');
        if (!OeuvreEanRepository::tableExists()) {
            $this->markTestSkipped('Table oeuvre_eans absente.');
        }

        $eanId = (new OeuvreEanRepository())->add(
            $oeuvreId,
            '4012345678901',
            SupportPhysique::BLURAY_4K,
            'Édition test'
        );
        $this->assertIsInt($eanId);

        $filmId = (new FilmRepository())->addFromCatalogOeuvre($oeuvreId, LibraryStatut::WISHLIST);
        $this->assertIsInt($filmId);

        $targets = new WishlistTargetRepository();
        $result = $targets->addFromCatalogEan($filmId, $eanId, $oeuvreId);
        $this->assertIsInt($result);

        $row = $targets->listForBibliothequeId($filmId)[0] ?? [];
        $this->assertSame(SupportPhysique::BLURAY_4K, $row['support_physique'] ?? '');
        $this->assertSame('4012345678901', $row['ean'] ?? '');
        $this->assertSame($eanId, (int) ($row['oeuvre_ean_id'] ?? 0));
    }
}
