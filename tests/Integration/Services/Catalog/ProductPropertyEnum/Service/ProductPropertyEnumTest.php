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
use Bitrix24\SDK\Services\Catalog\ProductPropertyEnum\Service\ProductPropertyEnum;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProductPropertyEnum::class)]
class ProductPropertyEnumTest extends TestCase
{
    private ProductPropertyEnum $productPropertyEnumService;

    private int $propertyId;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test ProductPropertyEnum::add')]
    public function testAdd(): void
    {
        $fields = [
            'propertyId' => $this->propertyId,
            'value' => sprintf('test value %s', time()),
            'xmlId' => sprintf('test-xml-id-%s', time()),
            'def' => 'Y',
            'sort' => 100,
        ];
        $productPropertyEnumResult = $this->productPropertyEnumService->add($fields);
        $this->assertEquals($fields['value'], $productPropertyEnumResult->productPropertyEnum()->value);
        $this->assertEquals($fields['xmlId'], $productPropertyEnumResult->productPropertyEnum()->xmlId);
        $this->assertEquals($this->propertyId, $productPropertyEnumResult->productPropertyEnum()->propertyId);

        $this->productPropertyEnumService->delete($productPropertyEnumResult->productPropertyEnum()->id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test ProductPropertyEnum::update')]
    public function testUpdate(): void
    {
        $productPropertyEnumResult = $this->productPropertyEnumService->add([
            'propertyId' => $this->propertyId,
            'value' => sprintf('test value %s', time()),
            'xmlId' => sprintf('test-xml-id-%s', time()),
            'def' => 'N',
            'sort' => 100,
        ]);
        $id = $productPropertyEnumResult->productPropertyEnum()->id;

        $updatedValue = sprintf('updated value %s', time());
        $this->productPropertyEnumService->update($id, [
            'propertyId' => $this->propertyId,
            'value' => $updatedValue,
            'xmlId' => $productPropertyEnumResult->productPropertyEnum()->xmlId,
            'def' => 'N',
            'sort' => 200,
        ]);

        $getResult = $this->productPropertyEnumService->get($id);
        $this->assertEquals($updatedValue, $getResult->productPropertyEnum()->value);
        $this->assertEquals(200, $getResult->productPropertyEnum()->sort);

        $this->productPropertyEnumService->delete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test ProductPropertyEnum::get')]
    public function testGet(): void
    {
        $productPropertyEnumResult = $this->productPropertyEnumService->add([
            'propertyId' => $this->propertyId,
            'value' => sprintf('test value %s', time()),
            'xmlId' => sprintf('test-xml-id-%s', time()),
            'def' => 'N',
            'sort' => 100,
        ]);
        $id = $productPropertyEnumResult->productPropertyEnum()->id;

        $getResult = $this->productPropertyEnumService->get($id);
        $this->assertEquals($id, $getResult->productPropertyEnum()->id);
        $this->assertEquals($productPropertyEnumResult->productPropertyEnum()->value, $getResult->productPropertyEnum()->value);

        $this->productPropertyEnumService->delete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test ProductPropertyEnum::list')]
    public function testList(): void
    {
        $productPropertyEnumResult = $this->productPropertyEnumService->add([
            'propertyId' => $this->propertyId,
            'value' => sprintf('test value %s', time()),
            'xmlId' => sprintf('test-xml-id-%s', time()),
            'def' => 'N',
            'sort' => 100,
        ]);
        $id = $productPropertyEnumResult->productPropertyEnum()->id;

        $productPropertyEnumsResult = $this->productPropertyEnumService->list(
            ['id', 'propertyId', 'value', 'xmlId', 'def', 'sort'],
            ['id' => $id],
            ['id' => 'ASC']
        );
        $this->assertCount(1, $productPropertyEnumsResult->getProductPropertyEnums());
        $this->assertEquals($id, $productPropertyEnumsResult->getProductPropertyEnums()[0]->id);

        $this->productPropertyEnumService->delete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test ProductPropertyEnum::delete')]
    public function testDelete(): void
    {
        $productPropertyEnumResult = $this->productPropertyEnumService->add([
            'propertyId' => $this->propertyId,
            'value' => sprintf('test value %s', time()),
            'xmlId' => sprintf('test-xml-id-%s', time()),
            'def' => 'N',
            'sort' => 100,
        ]);
        $id = $productPropertyEnumResult->productPropertyEnum()->id;

        $this->assertTrue($this->productPropertyEnumService->delete($id)->isSuccess());

        $productPropertyEnumsResult = $this->productPropertyEnumService->list([], ['id' => $id]);
        $this->assertCount(0, $productPropertyEnumsResult->getProductPropertyEnums());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test ProductPropertyEnum::getFields')]
    public function testGetFields(): void
    {
        $fields = $this->productPropertyEnumService->getFields()->getFieldsDescription();
        $this->assertArrayHasKey('id', $fields);
        $this->assertArrayHasKey('propertyId', $fields);
        $this->assertArrayHasKey('value', $fields);
        $this->assertArrayHasKey('xmlId', $fields);
        $this->assertArrayHasKey('def', $fields);
        $this->assertArrayHasKey('sort', $fields);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->productPropertyEnumService = Fabric::getServiceBuilder()->getCatalogScope()->productPropertyEnum();

        $catalogService = Fabric::getServiceBuilder()->getCatalogScope()->catalog();
        $iblockId = $catalogService->list([], [], [], 1)->getCatalogs()[0]->iblockId;

        $propertyResponse = Fabric::getCore()->call('catalog.productProperty.add', [
            'fields' => [
                'iblockId' => $iblockId,
                'name' => sprintf('test list property %s', time()),
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
        Fabric::getCore()->call('catalog.productProperty.delete', ['id' => $this->propertyId]);
    }
}
