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

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\StoreProduct\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Catalog\StoreProduct\Service\StoreProduct;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(StoreProduct::class)]
class StoreProductTest extends TestCase
{
    #[Test]
    public function getThrowsOnNonPositiveId(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects($this->never())->method('call');

        $this->expectException(InvalidArgumentException::class);
        (new StoreProduct($core, new NullLogger()))->get(0);
    }

    #[Test]
    public function getCallsCoreWithId(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects($this->once())
            ->method('call')
            ->with('catalog.storeproduct.get', ['id' => 13])
            ->willReturn($this->createStub(Response::class));

        (new StoreProduct($core, new NullLogger()))->get(13);
    }

    #[Test]
    public function listCallsCoreWithSelectFilterOrder(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects($this->once())
            ->method('call')
            ->with('catalog.storeproduct.list', [
                'select' => ['id', 'productId', 'storeId', 'amount'],
                'filter' => ['productId' => 6973],
                'order' => ['id' => 'ASC'],
            ])
            ->willReturn($this->createStub(Response::class));

        (new StoreProduct($core, new NullLogger()))->list(
            ['id', 'productId', 'storeId', 'amount'],
            ['productId' => 6973],
            ['id' => 'ASC']
        );
    }

    #[Test]
    public function listCallsCoreWithDefaultEmptyArguments(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects($this->once())
            ->method('call')
            ->with('catalog.storeproduct.list', [
                'select' => [],
                'filter' => [],
                'order' => [],
            ])
            ->willReturn($this->createStub(Response::class));

        (new StoreProduct($core, new NullLogger()))->list();
    }

    #[Test]
    public function getFieldsCallsCoreWithoutArguments(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects($this->once())
            ->method('call')
            ->with('catalog.storeproduct.getFields')
            ->willReturn($this->createStub(Response::class));

        (new StoreProduct($core, new NullLogger()))->getFields();
    }
}
