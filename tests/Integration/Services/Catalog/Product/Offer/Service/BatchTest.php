<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\Product\Offer\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Catalog\Service\Catalog;
use Bitrix24\SDK\Services\Catalog\Product\Offer\Result\OfferItemResult;
use Bitrix24\SDK\Services\Catalog\Product\Offer\Service\Batch;
use Bitrix24\SDK\Services\Catalog\Product\Offer\Service\Offer;
use Bitrix24\SDK\Services\Catalog\Product\Sku\Service\Sku;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private Offer $offerService;

    private Sku $skuService;

    private Catalog $catalogService;

    private int $productIblockId;

    private int $offersIblockId;

    private int $skuId;

    /** @var int[] */
    private array $offerIds = [];

    #[\Override]
    protected function setUp(): void
    {
        $this->offerService = Factory::getServiceBuilder()->getCatalogScope()->productOffer();
        $this->skuService = Factory::getServiceBuilder()->getCatalogScope()->productSku();
        $this->catalogService = Factory::getServiceBuilder()->getCatalogScope()->catalog();

        $productCatalog = null;
        $offersCatalog = null;
        foreach ($this->catalogService->list([], [], [], 0)->getCatalogs() as $catalog) {
            if ($catalog->productIblockId === null) {
                $productCatalog = $catalog;
            } else {
                $offersCatalog = $catalog;
            }
        }

        self::assertNotNull($productCatalog, 'products catalog not found');
        self::assertNotNull($offersCatalog, 'offers catalog not found');

        $this->productIblockId = $productCatalog->iblockId;
        $this->offersIblockId = $offersCatalog->iblockId;

        $this->skuId = $this->skuService->add([
            'iblockId' => $this->productIblockId,
            'name' => sprintf('sdk batch sku for offers %s', time()),
        ])->sku()->id;
    }

    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->offerIds as $offerId) {
            $this->offerService->delete($offerId);
        }

        $this->offerIds = [];
        $this->skuService->delete($this->skuId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchAddAndList(): void
    {
        $items = [
            [
                'iblockId' => $this->offersIblockId,
                'name' => sprintf('sdk batch offer 1 %s', time()),
                'parentId' => $this->skuId,
            ],
            [
                'iblockId' => $this->offersIblockId,
                'name' => sprintf('sdk batch offer 2 %s', time()),
                'parentId' => $this->skuId,
            ],
        ];

        foreach ($this->offerService->batch->add($items) as $result) {
            self::assertGreaterThan(0, $result->getId());
            $this->offerIds[] = $result->getId();
        }

        self::assertCount(2, $this->offerIds);

        $found = [];
        foreach (
            $this->offerService->batch->list(
                ['id' => 'ASC'],
                ['iblockId' => $this->offersIblockId, '@id' => $this->offerIds],
                ['id', 'iblockId', 'name']
            ) as $item
        ) {
            self::assertInstanceOf(OfferItemResult::class, $item);
            $found[] = $item->id;
        }

        foreach ($this->offerIds as $offerId) {
            self::assertContains($offerId, $found);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchUpdate(): void
    {
        $offerId = $this->offerService->add([
            'iblockId' => $this->offersIblockId,
            'name' => sprintf('sdk batch offer %s', time()),
            'parentId' => $this->skuId,
        ])->offer()->id;
        $this->offerIds[] = $offerId;

        $updatedName = sprintf('sdk batch offer updated %s', time());
        $entityItems = [
            $offerId => [
                'fields' => [
                    'name' => $updatedName,
                ],
            ],
        ];

        foreach ($this->offerService->batch->update($entityItems) as $result) {
            self::assertEquals($updatedName, $result->offer()->name);
        }

        self::assertEquals($updatedName, $this->offerService->get($offerId)->offer()->name);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchDelete(): void
    {
        $offerIds = [];
        for ($i = 0; $i < 2; ++$i) {
            $offerIds[] = $this->offerService->add([
                'iblockId' => $this->offersIblockId,
                'name' => sprintf('sdk batch offer to delete %s %s', $i, time()),
                'parentId' => $this->skuId,
            ])->offer()->id;
        }

        $deletedCount = 0;
        foreach ($this->offerService->batch->delete($offerIds) as $result) {
            self::assertTrue($result->isSuccess());
            ++$deletedCount;
        }

        self::assertSame(2, $deletedCount);
    }
}
