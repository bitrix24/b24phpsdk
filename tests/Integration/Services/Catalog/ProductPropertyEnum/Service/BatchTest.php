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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\ProductPropertyEnum\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\ProductPropertyEnum\Result\ProductPropertyEnumItemResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertyEnum\Service\Batch;
use Bitrix24\SDK\Services\Catalog\ProductPropertyEnum\Service\ProductPropertyEnum;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private ProductPropertyEnum $productPropertyEnumService;

    private int $propertyId;

    /** @var int[] */
    private array $enumIds = [];

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->productPropertyEnumService = Fabric::getServiceBuilder()->getCatalogScope()->productPropertyEnum();

        $catalogService = Fabric::getServiceBuilder()->getCatalogScope()->catalog();
        $iblockId = $catalogService->list([], [], [], 0)->getCatalogs()[0]->iblockId;

        $propertyResponse = Fabric::getCore()->call('catalog.productProperty.add', [
            'fields' => [
                'iblockId' => $iblockId,
                'name' => sprintf('sdk batch list property %s', time()),
                'propertyType' => 'L',
                'listType' => 'L',
            ],
        ]);
        $this->propertyId = (int)$propertyResponse->getResponseData()->getResult()['productProperty']['id'];
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->enumIds as $enumId) {
            $this->productPropertyEnumService->delete($enumId);
        }

        $this->enumIds = [];
        Fabric::getCore()->call('catalog.productProperty.delete', ['id' => $this->propertyId]);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchAddAndList(): void
    {
        $items = [
            [
                'propertyId' => $this->propertyId,
                'value' => sprintf('sdk batch value 1 %s', time()),
                'xmlId' => sprintf('sdk-batch-xml-id-1-%s', time()),
                'def' => 'N',
                'sort' => 100,
            ],
            [
                'propertyId' => $this->propertyId,
                'value' => sprintf('sdk batch value 2 %s', time()),
                'xmlId' => sprintf('sdk-batch-xml-id-2-%s', time()),
                'def' => 'N',
                'sort' => 200,
            ],
        ];

        foreach ($this->productPropertyEnumService->batch->add($items) as $result) {
            self::assertGreaterThan(0, $result->getId());
            $this->enumIds[] = $result->getId();
        }

        self::assertCount(2, $this->enumIds);

        $found = [];
        foreach (
            $this->productPropertyEnumService->batch->list(
                ['id' => 'ASC'],
                ['propertyId' => $this->propertyId],
                ['id', 'propertyId', 'value', 'xmlId', 'def', 'sort']
            ) as $item
        ) {
            self::assertInstanceOf(ProductPropertyEnumItemResult::class, $item);
            $found[] = $item->id;
        }

        foreach ($this->enumIds as $enumId) {
            self::assertContains($enumId, $found);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchUpdate(): void
    {
        $productPropertyEnumResult = $this->productPropertyEnumService->add([
            'propertyId' => $this->propertyId,
            'value' => sprintf('sdk batch value %s', time()),
            'xmlId' => sprintf('sdk-batch-xml-id-%s', time()),
            'def' => 'N',
            'sort' => 100,
        ]);
        $enumId = $productPropertyEnumResult->productPropertyEnum()->id;
        $this->enumIds[] = $enumId;

        $updatedValue = sprintf('sdk batch value updated %s', time());
        $entityItems = [
            $enumId => [
                'fields' => [
                    'propertyId' => $this->propertyId,
                    'value' => $updatedValue,
                    'xmlId' => $productPropertyEnumResult->productPropertyEnum()->xmlId,
                    'def' => 'N',
                    'sort' => 200,
                ],
            ],
        ];

        foreach ($this->productPropertyEnumService->batch->update($entityItems) as $result) {
            self::assertEquals($updatedValue, $result->productPropertyEnum()->value);
        }

        self::assertEquals(
            $updatedValue,
            $this->productPropertyEnumService->get($enumId)->productPropertyEnum()->value
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchDelete(): void
    {
        $enumIds = [];
        for ($i = 0; $i < 2; ++$i) {
            $enumIds[] = $this->productPropertyEnumService->add([
                'propertyId' => $this->propertyId,
                'value' => sprintf('sdk batch value to delete %s %s', $i, time()),
                'xmlId' => sprintf('sdk-batch-xml-id-to-delete-%s-%s', $i, time()),
                'def' => 'N',
                'sort' => 100,
            ])->productPropertyEnum()->id;
        }

        $deletedCount = 0;
        foreach ($this->productPropertyEnumService->batch->delete($enumIds) as $result) {
            self::assertTrue($result->isSuccess());
            ++$deletedCount;
        }

        self::assertSame(2, $deletedCount);
    }
}
