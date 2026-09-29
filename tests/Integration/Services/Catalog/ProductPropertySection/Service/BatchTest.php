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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\ProductPropertySection\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\ProductPropertySection\Result\ProductPropertySectionItemResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertySection\Service\Batch;
use Bitrix24\SDK\Services\Catalog\ProductPropertySection\Service\ProductPropertySection;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private ProductPropertySection $productPropertySectionService;

    /** @var int[] */
    private array $propertyIds = [];

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->productPropertySectionService = Factory::getServiceBuilder()
            ->getCatalogScope()
            ->productPropertySection();

        $iblockId = Factory::getServiceBuilder()->getCatalogScope()->catalog()
            ->list([], [], [], 0)->getCatalogs()[0]->iblockId;

        for ($i = 0; $i < 2; ++$i) {
            $propertyAddResult = Factory::getCore()->call('catalog.productProperty.add', [
                'fields' => [
                    'iblockId' => $iblockId,
                    'name' => sprintf('sdk batch property %s %s', $i, time()),
                    'propertyType' => 'S',
                    'code' => sprintf('SDK_BATCH_PROP_%s_%s', $i, time()),
                    'active' => 'Y',
                ],
            ]);
            $this->propertyIds[] = (int)$propertyAddResult
                ->getResponseData()
                ->getResult()['productProperty']['id'];
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->propertyIds as $propertyId) {
            Factory::getCore()->call('catalog.productProperty.delete', ['id' => $propertyId]);
        }

        $this->propertyIds = [];
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchSetAndList(): void
    {
        $items = [];
        foreach ($this->propertyIds as $propertyId) {
            $items[$propertyId] = [
                'smartFilter' => 'Y',
                'displayType' => 'F',
                'displayExpanded' => 'N',
                'filterHint' => sprintf('sdk batch hint %s', $propertyId),
            ];
        }

        $setPropertyIds = [];
        foreach ($this->productPropertySectionService->batch->set($items) as $result) {
            $setPropertyIds[] = $result->getPropertyId();
            self::assertEquals(
                sprintf('sdk batch hint %s', $result->getPropertyId()),
                $result->productPropertySection()->filterHint
            );
        }

        self::assertCount(count($this->propertyIds), $setPropertyIds);
        foreach ($this->propertyIds as $propertyId) {
            self::assertContains($propertyId, $setPropertyIds);
        }

        $found = [];
        foreach (
            $this->productPropertySectionService->batch->list(
                ['propertyId' => 'ASC'],
                ['@propertyId' => $this->propertyIds],
                ['propertyId', 'smartFilter', 'displayType', 'displayExpanded', 'filterHint']
            ) as $item
        ) {
            self::assertInstanceOf(ProductPropertySectionItemResult::class, $item);
            $found[] = $item->propertyId;
        }

        foreach ($this->propertyIds as $propertyId) {
            self::assertContains($propertyId, $found);
        }
    }
}
