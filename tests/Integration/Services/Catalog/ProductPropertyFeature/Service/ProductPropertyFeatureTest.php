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
use Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Service\ProductPropertyFeature;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(ProductPropertyFeature::class, 'add')]
#[CoversMethod(ProductPropertyFeature::class, 'update')]
#[CoversMethod(ProductPropertyFeature::class, 'get')]
#[CoversMethod(ProductPropertyFeature::class, 'list')]
#[CoversMethod(ProductPropertyFeature::class, 'getAvailableFeaturesByProperty')]
#[CoversMethod(ProductPropertyFeature::class, 'getFields')]
#[CoversClass(ProductPropertyFeature::class)]
class ProductPropertyFeatureTest extends TestCase
{
    use CustomBitrix24Assertions;

    private ProductPropertyFeature $service;

    private int $propertyId;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = Factory::getServiceBuilder()->getCatalogScope()->productPropertyFeature();
        $this->propertyId = $this->createProductProperty();
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->deleteProductProperty($this->propertyId);
    }

    public function testFields(): void
    {
        $fields = $this->service->getFields()->getFieldsDescription();
        self::assertIsArray($fields);
        $this->assertNotEmpty($fields);
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($fields), ProductPropertyFeatureItemResult::class);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAddGetUpdateList(): void
    {
        $addedResult = $this->service->add([
            'propertyId' => $this->propertyId,
            'moduleId' => 'iblock',
            'featureId' => 'LIST_PAGE_SHOW',
            'isEnabled' => 'Y',
        ]);
        $id = $addedResult->getId();
        self::assertGreaterThan(0, $id);

        $item = $this->service->get($id)->productPropertyFeature();
        self::assertEquals($this->propertyId, $item->propertyId);
        self::assertEquals('iblock', $item->moduleId);
        self::assertEquals('LIST_PAGE_SHOW', $item->featureId);
        self::assertTrue($item->isEnabled);

        $updatedResult = $this->service->update($id, [
            'propertyId' => $this->propertyId,
            'moduleId' => 'iblock',
            'featureId' => 'LIST_PAGE_SHOW',
            'isEnabled' => 'N',
        ]);
        self::assertTrue($updatedResult->isSuccess());
        self::assertFalse($this->service->get($id)->productPropertyFeature()->isEnabled);

        $list = $this->service->list(
            ['id', 'propertyId', 'moduleId', 'featureId', 'isEnabled'],
            ['propertyId' => $this->propertyId],
            ['id' => 'asc']
        )->productPropertyFeatures();
        self::assertNotEmpty($list);
        self::assertEquals($id, $list[0]->id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetAvailableFeaturesByProperty(): void
    {
        $features = $this->service->getAvailableFeaturesByProperty($this->propertyId)->features();
        self::assertNotEmpty($features);
        self::assertEquals('iblock', $features[0]->moduleId);
    }

    protected function createProductProperty(): int
    {
        $core = Factory::getCore();
        $iblockId = (int)$core->call('catalog.catalog.list', [
            'select' => ['id', 'iblockId'],
        ])->getResponseData()->getResult()['catalogs'][0]['iblockId'];

        return (int)$core->call('catalog.productProperty.add', [
            'fields' => [
                'iblockId' => $iblockId,
                'name' => 'SDK Test Property ' . uniqid('', true),
                'propertyType' => 'S',
                'active' => 'Y',
                'sort' => 100,
            ],
        ])->getResponseData()->getResult()['productProperty']['id'];
    }

    protected function deleteProductProperty(int $id): void
    {
        $core = Factory::getCore();
        $core->call('catalog.productProperty.delete', [
            'id' => $id,
        ]);
    }
}
