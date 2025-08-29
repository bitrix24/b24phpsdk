<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\Sale;

use Bitrix24\SDK\Services\Sale\SaleServiceBuilder;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Unit\Stubs\NullBatch;
use Bitrix24\SDK\Tests\Unit\Stubs\NullBulkItemsReader;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(SaleServiceBuilder::class)]
class SaleServiceBuilderTest extends TestCase
{
    private SaleServiceBuilder $serviceBuilder;

    public function testGetTradePlatformService(): void
    {
        $this::assertSame($this->serviceBuilder->tradePlatform(), $this->serviceBuilder->tradePlatform());
    }

    protected function setUp(): void
    {
        $this->serviceBuilder = (new ServiceBuilder(
            new NullCore(),
            new NullBatch(),
            new NullBulkItemsReader(),
            new NullLogger()
        ))->getSaleScope();
    }
}
