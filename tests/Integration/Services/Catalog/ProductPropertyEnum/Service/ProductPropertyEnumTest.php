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
        $result = $this->productPropertyEnumService->add($fields);
        $this->assertEquals($fields['value'], $result->productPropertyEnum()->value);
        $this->assertEquals($fields['xmlId'], $result->productPropertyEnum()->xmlId);
        $this->assertEquals($this->propertyId, $result->productPropertyEnum()->propertyId);

        $this->productPropertyEnumService->delete($result->productPropertyEnum()->id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test ProductPropertyEnum::update')]
    public function testUpdate(): void
    {
        $addResult = $this->productPropertyEnumService->add([
            'propertyId' => $this->propertyId,
            'value' => sprintf('test value %s', time()),
            'xmlId' => sprintf('test-xml-id-%s', time()),
            'def' => 'N',
            'sort' => 100,
        ]);
        $id = $addResult->productPropertyEnum()->id;

        $updatedValue = sprintf('updated value %s', time());
        $this->productPropertyEnumService->update($id, [
            'propertyId' => $this->propertyId,
            'value' => $updatedValue,
            'xmlId' => $addResult->productPropertyEnum()->xmlId,
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
        $addResult = $this->productPropertyEnumService->add([
            'propertyId' => $this->propertyId,
            'value' => sprintf('test value %s', time()),
            'xmlId' => sprintf('test-xml-id-%s', time()),
            'def' => 'N',
            'sort' => 100,
        ]);
        $id = $addResult->productPropertyEnum()->id;

        $getResult = $this->productPropertyEnumService->get($id);
        $this->assertEquals($id, $getResult->productPropertyEnum()->id);
        $this->assertEquals($addResult->productPropertyEnum()->value, $getResult->productPropertyEnum()->value);

        $this->productPropertyEnumService->delete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test ProductPropertyEnum::list')]
    public function testList(): void
    {
        $addResult = $this->productPropertyEnumService->add([
            'propertyId' => $this->propertyId,
            'value' => sprintf('test value %s', time()),
            'xmlId' => sprintf('test-xml-id-%s', time()),
            'def' => 'N',
            'sort' => 100,
        ]);
        $id = $addResult->productPropertyEnum()->id;

        $listResult = $this->productPropertyEnumService->list(
            ['id', 'propertyId', 'value', 'xmlId', 'def', 'sort'],
            ['id' => $id],
            ['id' => 'ASC']
        );
        $this->assertCount(1, $listResult->getProductPropertyEnums());
        $this->assertEquals($id, $listResult->getProductPropertyEnums()[0]->id);

        $this->productPropertyEnumService->delete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test ProductPropertyEnum::delete')]
    public function testDelete(): void
    {
        $addResult = $this->productPropertyEnumService->add([
            'propertyId' => $this->propertyId,
            'value' => sprintf('test value %s', time()),
            'xmlId' => sprintf('test-xml-id-%s', time()),
            'def' => 'N',
            'sort' => 100,
        ]);
        $id = $addResult->productPropertyEnum()->id;

        $this->assertTrue($this->productPropertyEnumService->delete($id)->isSuccess());

        $listResult = $this->productPropertyEnumService->list([], ['id' => $id]);
        $this->assertCount(0, $listResult->getProductPropertyEnums());
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
