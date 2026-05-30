<?php

declare(strict_types=1);

namespace Moncine\Tests\Unit;

use Moncine\ExportPdf;
use Moncine\ExportPdfScope;
use Moncine\Tests\Support\MoncineTestCase;

final class ExportPdfTest extends MoncineTestCase
{
    public function testScopeNormalize(): void
    {
        $this->assertSame(ExportPdfScope::COLLECTION, ExportPdfScope::normalize('collection'));
        $this->assertSame(ExportPdfScope::WISHLIST, ExportPdfScope::normalize('wishlist'));
        $this->assertSame(ExportPdfScope::COLLECTION, ExportPdfScope::normalize(''));
    }

    public function testFetchCollectionRowsRespectsSearch(): void
    {
        $this->loginAsAdmin();
        $oeuvreId = $this->seedCatalogOeuvre('Film PDF Test');
        $added = (new \Moncine\FilmRepository())->addFromCatalogOeuvre($oeuvreId, \Moncine\LibraryStatut::COLLECTION);
        $this->assertIsInt($added);

        $rows = (new ExportPdf())->fetchRows(ExportPdfScope::COLLECTION, 'titre', 'asc', 'PDF Test', '');
        $this->assertNotEmpty($rows);
        $titles = array_map(static fn (array $r): string => (string) ($r['titre'] ?? ''), $rows);
        $this->assertContains('Film PDF Test', $titles);
    }

    public function testFetchWishlistRows(): void
    {
        $this->loginAsAdmin();
        $oeuvreId = $this->seedCatalogOeuvre('Envie PDF');
        $repo = new \Moncine\FilmRepository();
        $result = $repo->addFromCatalogOeuvre($oeuvreId, \Moncine\LibraryStatut::WISHLIST);
        $this->assertIsInt($result);

        $rows = (new ExportPdf())->fetchRows(ExportPdfScope::WISHLIST, 'titre', 'asc', 'Envie PDF', '');
        $this->assertCount(1, $rows);
        $this->assertSame('Envie PDF', $rows[0]['titre'] ?? '');
    }
}
