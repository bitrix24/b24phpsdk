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

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\ProductPropertyEnum\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertyEnum\Result\ProductPropertyEnumFieldsResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertyEnum\Result\ProductPropertyEnumResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertyEnum\Result\ProductPropertyEnumsResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertyEnum\Service\Batch;
use Bitrix24\SDK\Services\Catalog\ProductPropertyEnum\Service\ProductPropertyEnum;
use Bitrix24\SDK\Tests\Unit\Stubs\NullBatch;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(ProductPropertyEnum::class)]
class ProductPropertyEnumTest extends TestCase
{
    private function createService(CoreInterface $core): ProductPropertyEnum
    {
        return new ProductPropertyEnum(new Batch(new NullBatch(), new NullLogger()), $core, new NullLogger());
    }

    #[Test]
    public function testAddCallsProductPropertyEnumAdd(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);

        $fields = [
            'propertyId' => 431,
            'value' => 'Medium',
            'xmlId' => 'M',
            'def' => 'Y',
            'sort' => 100,
        ];

        $core->expects($this->once())
            ->method('call')
            ->with('catalog.productPropertyEnum.add', ['fields' => $fields])
            ->willReturn($response);

        $this->assertInstanceOf(
            ProductPropertyEnumResult::class,
            $this->createService($core)->add($fields)
        );
    }

    #[Test]
    public function testUpdateCallsProductPropertyEnumUpdate(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);

        $fields = [
            'propertyId' => 431,
            'value' => 'Medium',
            'xmlId' => 'M',
            'def' => 'N',
            'sort' => 110,
        ];

        $core->expects($this->once())
            ->method('call')
            ->with('catalog.productPropertyEnum.update', ['id' => 1739, 'fields' => $fields])
            ->willReturn($response);

        $this->assertInstanceOf(
            ProductPropertyEnumResult::class,
            $this->createService($core)->update(1739, $fields)
        );
    }

    #[Test]
    public function testGetCallsProductPropertyEnumGet(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);

        $core->expects($this->once())
            ->method('call')
            ->with('catalog.productPropertyEnum.get', ['id' => 1739])
            ->willReturn($response);

        $this->assertInstanceOf(
            ProductPropertyEnumResult::class,
            $this->createService($core)->get(1739)
        );
    }

    #[Test]
    public function testListCallsProductPropertyEnumListWithoutOptionalParams(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);

        $core->expects($this->once())
            ->method('call')
            ->with('catalog.productPropertyEnum.list', [])
            ->willReturn($response);

        $this->assertInstanceOf(
            ProductPropertyEnumsResult::class,
            $this->createService($core)->list()
        );
    }

    #[Test]
    public function testListCallsProductPropertyEnumListWithAllParams(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);

        $select = ['id', 'propertyId', 'value', 'def', 'sort', 'xmlId'];
        $filter = ['propertyId' => 431];
        $order = ['id' => 'ASC'];

        $core->expects($this->once())
            ->method('call')
            ->with('catalog.productPropertyEnum.list', [
                'select' => $select,
                'filter' => $filter,
                'order' => $order,
            ])
            ->willReturn($response);

        $this->assertInstanceOf(
            ProductPropertyEnumsResult::class,
            $this->createService($core)->list($select, $filter, $order)
        );
    }

    #[Test]
    public function testDeleteCallsProductPropertyEnumDelete(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);

        $core->expects($this->once())
            ->method('call')
            ->with('catalog.productPropertyEnum.delete', ['id' => 122])
            ->willReturn($response);

        $this->assertInstanceOf(
            DeletedItemResult::class,
            $this->createService($core)->delete(122)
        );
    }

    #[Test]
    public function testGetFieldsCallsProductPropertyEnumGetFields(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);

        $core->expects($this->once())
            ->method('call')
            ->with('catalog.productPropertyEnum.getFields', [])
            ->willReturn($response);

        $this->assertInstanceOf(
            ProductPropertyEnumFieldsResult::class,
            $this->createService($core)->getFields()
        );
    }
}
