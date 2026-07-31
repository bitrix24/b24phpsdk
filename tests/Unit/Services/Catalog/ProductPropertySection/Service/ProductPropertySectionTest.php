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

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\ProductPropertySection\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Catalog\ProductPropertySection\Service\ProductPropertySection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(ProductPropertySection::class)]
class ProductPropertySectionTest extends TestCase
{
    #[Test]
    public function getCallsCoreWithPropertyId(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects($this->once())
            ->method('call')
            ->with('catalog.productPropertySection.get', ['propertyId' => 901])
            ->willReturn($this->createStub(Response::class));

        (new ProductPropertySection($core, new NullLogger()))->get(901);
    }

    #[Test]
    public function listCallsCoreWithSelectFilterOrder(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects($this->once())
            ->method('call')
            ->with('catalog.productPropertySection.list', [
                'select' => ['propertyId'],
                'filter' => ['propertyId' => 901],
                'order' => ['propertyId' => 'ASC'],
            ])
            ->willReturn($this->createStub(Response::class));

        (new ProductPropertySection($core, new NullLogger()))->list(
            ['propertyId'],
            ['propertyId' => 901],
            ['propertyId' => 'ASC']
        );
    }

    #[Test]
    public function listCallsCoreWithDefaultEmptyArguments(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects($this->once())
            ->method('call')
            ->with('catalog.productPropertySection.list', [
                'select' => [],
                'filter' => [],
                'order' => [],
            ])
            ->willReturn($this->createStub(Response::class));

        (new ProductPropertySection($core, new NullLogger()))->list();
    }

    #[Test]
    public function setCallsCoreWithPropertyIdAndFields(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $fields = ['smartFilter' => 'Y', 'displayType' => 'F', 'displayExpanded' => 'N', 'filterHint' => 'hint'];
        $core->expects($this->once())
            ->method('call')
            ->with('catalog.productPropertySection.set', ['propertyId' => 901, 'fields' => $fields])
            ->willReturn($this->createStub(Response::class));

        (new ProductPropertySection($core, new NullLogger()))->set(901, $fields);
    }
}
