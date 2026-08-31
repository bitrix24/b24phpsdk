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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\ProductPropertyFeature\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Result\ProductPropertyFeatureItemResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Service\Batch;
use Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Service\ProductPropertyFeature;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private ProductPropertyFeature $service;

    /** @var int[] */
    private array $propertyIds = [];

    #[\Override]
    protected function setUp(): void
    {
        $this->service = Fabric::getServiceBuilder()->getCatalogScope()->productPropertyFeature();
    }

    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->propertyIds as $propertyId) {
            $this->deleteProductProperty($propertyId);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchAddAndList(): void
    {
        $propertyId = $this->createProductProperty();

        $items = [
            [
                'propertyId' => $propertyId,
                'moduleId' => 'iblock',
                'featureId' => 'LIST_PAGE_SHOW',
                'isEnabled' => 'Y',
            ],
            [
                'propertyId' => $propertyId,
                'moduleId' => 'iblock',
                'featureId' => 'DETAIL_PAGE_SHOW',
                'isEnabled' => 'N',
            ],
        ];

        $addedIds = [];
        foreach ($this->service->batch->add($items) as $result) {
            $addedIds[] = $result->getId();
            self::assertGreaterThan(0, $result->getId());
        }

        self::assertCount(2, $addedIds);

        $found = [];
        foreach ($this->service->batch->list(['id' => 'asc'], ['propertyId' => $propertyId]) as $item) {
            self::assertInstanceOf(ProductPropertyFeatureItemResult::class, $item);
            $found[] = $item->id;
        }

        foreach ($addedIds as $addedId) {
            self::assertContains($addedId, $found);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchUpdate(): void
    {
        $propertyId = $this->createProductProperty();

        $addedId = $this->service->add([
            'propertyId' => $propertyId,
            'moduleId' => 'iblock',
            'featureId' => 'LIST_PAGE_SHOW',
            'isEnabled' => 'Y',
        ])->getId();

        $entityItems = [
            $addedId => [
                'fields' => [
                    'propertyId' => $propertyId,
                    'moduleId' => 'iblock',
                    'featureId' => 'LIST_PAGE_SHOW',
                    'isEnabled' => 'N',
                ],
            ],
        ];

        foreach ($this->service->batch->update($entityItems) as $result) {
            self::assertTrue($result->isSuccess());
        }

        self::assertFalse($this->service->get($addedId)->productPropertyFeature()->isEnabled);
    }

    protected function createProductProperty(): int
    {
        $core = Fabric::getCore();
        $iblockId = (int)$core->call('catalog.catalog.list', [
            'select' => ['id', 'iblockId'],
        ])->getResponseData()->getResult()['catalogs'][0]['iblockId'];

        $propertyId = (int)$core->call('catalog.productProperty.add', [
            'fields' => [
                'iblockId' => $iblockId,
                'name' => 'SDK Batch Test Property ' . uniqid('', true),
                'propertyType' => 'S',
                'active' => 'Y',
                'sort' => 100,
            ],
        ])->getResponseData()->getResult()['productProperty']['id'];

        $this->propertyIds[] = $propertyId;

        return $propertyId;
    }

    protected function deleteProductProperty(int $id): void
    {
        $core = Fabric::getCore();
        $core->call('catalog.productProperty.delete', [
            'id' => $id,
        ]);
    }
}
