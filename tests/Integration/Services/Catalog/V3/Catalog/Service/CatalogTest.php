<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\V3\Catalog\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\Catalog\V3\Catalog\Service\Catalog;
use Bitrix24\SDK\Services\Catalog\V3\Catalog\Service\CatalogFilter;
use Bitrix24\SDK\Services\Catalog\V3\Catalog\Service\CatalogItemBuilder;
use Bitrix24\SDK\Services\Catalog\V3\Catalog\Service\CatalogListPagination;
use Bitrix24\SDK\Services\Catalog\V3\Catalog\Service\CatalogSelectBuilder;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Catalog::class)]
class CatalogTest extends TestCase
{
    private Catalog $catalogService;

    private CoreInterface $core;

    private ?int $temporaryIblockId = null;

    #[\Override]
    protected function setUp(): void
    {
        $this->catalogService = Factory::getServiceBuilder()->getCatalogScope()->v3()->catalog();
        $this->core = Factory::getCore();
    }

    #[\Override]
    protected function tearDown(): void
    {
        if ($this->temporaryIblockId === null) {
            return;
        }

        try {
            $this->catalogService->delete($this->temporaryIblockId);
        } catch (BaseException) {
            // the catalog flag was already removed by the test
        }

        $this->core->call('lists.delete', ['IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => $this->temporaryIblockId]);
    }

    #[Test]
    public function testListAndGetReturnExistingCatalogs(): void
    {
        $catalogs = $this->catalogService->list([], [], ['iblockId' => 'ASC'], new CatalogListPagination(limit: 10))->getCatalogs();
        self::assertNotEmpty($catalogs, 'test portal must have a trade catalog');

        $catalog = $this->catalogService->get($catalogs[0]->id, (new CatalogSelectBuilder())->iblockId()->name())->catalog();

        $this->assertSame($catalogs[0]->id, $catalog->id);
        $this->assertSame($catalog->id, $catalog->iblockId);
        $this->assertNotSame('', $catalog->name);
    }

    #[Test]
    public function testIsOffersDistinguishesProductAndOffersCatalogs(): void
    {
        foreach ($this->catalogService->list()->getCatalogs() as $catalog) {
            $this->assertSame(
                $catalog->productIblockId > 0,
                $this->catalogService->isOffers($catalog->id)->isOffers(),
                sprintf('catalog %d', $catalog->id)
            );
        }
    }

    #[Test]
    public function testAddUpdateAndDeleteKeepTheInformationBlock(): void
    {
        $iblockId = $this->createTemporaryIblock();

        $added = $this->catalogService->add(
            (new CatalogItemBuilder())->iblockId($iblockId)->subscription(false)->yandexExport(false)
        )->catalog();
        $this->assertSame($iblockId, $added->id);
        $this->assertFalse($added->subscription);
        $this->assertSame(0, $added->productIblockId);
        $this->assertFalse($this->catalogService->isOffers($iblockId)->isOffers());

        $updated = $this->catalogService->update($iblockId, (new CatalogItemBuilder())->subscription(true))->catalog();
        $this->assertTrue($updated->subscription);

        $filter = new CatalogFilter();
        $filter->id()->eq($iblockId);
        $filter->subscription()->eq(true);
        $this->assertCount(1, $this->catalogService->list($filter)->getCatalogs());

        $this->assertTrue($this->catalogService->delete($iblockId)->isSuccess());

        $filter = new CatalogFilter();
        $filter->id()->eq($iblockId);
        $this->assertSame([], $this->catalogService->list($filter)->getCatalogs());

        // deleting the catalog flag must not delete the information block itself
        $iblocks = $this->core->call('lists.get', ['IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => $iblockId])
            ->getResponseData()->getResult();
        $this->assertSame((string)$iblockId, $iblocks[0]['ID']);
    }

    private function createTemporaryIblock(): int
    {
        $this->temporaryIblockId = (int)$this->core->call('lists.add', [
            'IBLOCK_TYPE_ID' => 'lists',
            'IBLOCK_CODE' => 'sdk_v3_catalog_' . random_int(100000, 999999),
            'FIELDS' => ['NAME' => 'sdk v3 catalog integration test'],
        ])->getResponseData()->getResult()[0];

        return $this->temporaryIblockId;
    }
}
