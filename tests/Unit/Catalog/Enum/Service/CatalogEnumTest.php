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

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\Enum\Service;

use Bitrix24\SDK\Services\Catalog\Enum\Result\RoundTypesResult;
use Bitrix24\SDK\Services\Catalog\Enum\Result\StoreDocumentTypesResult;
use Bitrix24\SDK\Services\Catalog\Enum\Service\CatalogEnum;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(CatalogEnum::class)]
class CatalogEnumTest extends TestCase
{
    private CatalogEnum $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = new CatalogEnum(new NullCore(), new NullLogger());
    }

    #[Test]
    public function testGetRoundTypesReturnsRoundTypesResult(): void
    {
        $this->assertInstanceOf(RoundTypesResult::class, $this->service->getRoundTypes());
    }

    #[Test]
    public function testGetStoreDocumentTypesReturnsStoreDocumentTypesResult(): void
    {
        $this->assertInstanceOf(StoreDocumentTypesResult::class, $this->service->getStoreDocumentTypes());
    }
}
