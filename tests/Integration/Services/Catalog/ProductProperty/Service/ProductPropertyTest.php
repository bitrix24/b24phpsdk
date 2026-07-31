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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\ProductProperty\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Catalog\Service\Catalog;
use Bitrix24\SDK\Services\Catalog\ProductProperty\Result\ProductPropertyItemResult;
use Bitrix24\SDK\Services\Catalog\ProductProperty\Service\ProductProperty;
use Bitrix24\SDK\Tests\Integration\Factory;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class ProductPropertyTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Catalog\ProductProperty\Service
 */
#[CoversMethod(ProductProperty::class, 'add')]
#[CoversMethod(ProductProperty::class, 'delete')]
#[CoversMethod(ProductProperty::class, 'get')]
#[CoversMethod(ProductProperty::class, 'list')]
#[CoversMethod(ProductProperty::class, 'update')]
#[CoversMethod(ProductProperty::class, 'getFields')]
#[\PHPUnit\Framework\Attributes\CoversClass(ProductProperty::class)]
class ProductPropertyTest extends TestCase
{
    private ProductProperty $productPropertyService;

    private Catalog $catalogService;

    private Generator $faker;

    private int $iblockId;

    /**
     * @throws InvalidArgumentException
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->productPropertyService = Factory::getServiceBuilder()->getCatalogScope()->productProperty();
        $this->catalogService = Factory::getServiceBuilder()->getCatalogScope()->catalog();
        $this->faker = FakerFactory::create();
        $this->iblockId = $this->catalogService->list([], [], [], 1)->getCatalogs()[0]->iblockId;
    }

    /**
     * Helper: silently delete a product property.
     */
    private function safeDelete(int $id): void
    {
        try {
            $this->productPropertyService->delete($id);
        } catch (BaseException) {
            // Server-side error; ignored during cleanup
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $id = $this->productPropertyService->add([
            'iblockId' => $this->iblockId,
            'name' => 'SDK_TEST_' . $this->faker->uuid(),
            'propertyType' => 'S',
            'active' => 'Y',
        ])->productProperty()->id;
        self::assertGreaterThanOrEqual(1, $id);

        $this->safeDelete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $id = $this->productPropertyService->add([
            'iblockId' => $this->iblockId,
            'name' => 'SDK_TEST_' . $this->faker->uuid(),
            'propertyType' => 'S',
            'active' => 'Y',
        ])->productProperty()->id;

        $productPropertyItemResult = $this->productPropertyService->get($id)->productProperty();
        self::assertInstanceOf(ProductPropertyItemResult::class, $productPropertyItemResult);
        self::assertEquals($id, $productPropertyItemResult->id);

        $this->safeDelete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $id = $this->productPropertyService->add([
            'iblockId' => $this->iblockId,
            'name' => 'SDK_TEST_' . $this->faker->uuid(),
            'propertyType' => 'S',
            'active' => 'Y',
        ])->productProperty()->id;

        $list = $this->productPropertyService->list([], ['id' => $id])->getProductProperties();
        self::assertCount(1, $list);
        self::assertEquals($id, $list[0]->id);

        $this->safeDelete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $id = $this->productPropertyService->add([
            'iblockId' => $this->iblockId,
            'name' => 'SDK_TEST_' . $this->faker->uuid(),
            'propertyType' => 'S',
            'active' => 'Y',
        ])->productProperty()->id;

        $updatedName = 'SDK_TEST_UPDATED_' . $this->faker->uuid();
        $updated = $this->productPropertyService->update($id, [
            'iblockId' => $this->iblockId,
            'name' => $updatedName,
        ])->productProperty();
        self::assertEquals($updatedName, $updated->name);

        $this->safeDelete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $id = $this->productPropertyService->add([
            'iblockId' => $this->iblockId,
            'name' => 'SDK_TEST_' . $this->faker->uuid(),
            'propertyType' => 'S',
            'active' => 'Y',
        ])->productProperty()->id;

        $deletedProductPropertyResult = $this->productPropertyService->delete($id);
        self::assertTrue($deletedProductPropertyResult->isSuccess());

        $list = $this->productPropertyService->list([], ['id' => $id])->getProductProperties();
        self::assertCount(0, $list);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetFields(): void
    {
        $fieldsDescription = $this->productPropertyService->getFields()->getFieldsDescription();
        self::assertIsArray($fieldsDescription);
        self::assertArrayHasKey('id', $fieldsDescription);
        self::assertArrayHasKey('iblockId', $fieldsDescription);
        self::assertArrayHasKey('propertyType', $fieldsDescription);
    }
}
