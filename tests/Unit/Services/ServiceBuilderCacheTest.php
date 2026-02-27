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

namespace Bitrix24\SDK\Tests\Unit\Services;

use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Unit\Stubs\NullBatch;
use Bitrix24\SDK\Tests\Unit\Stubs\NullBulkItemsReader;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(ServiceBuilder::class)]
class ServiceBuilderCacheTest extends TestCase
{
    private ServiceBuilder $serviceBuilder;
    
    #[\Override]
    protected function setUp(): void
    {
        $this->serviceBuilder = new ServiceBuilder(
            new NullCore(),
            new NullBatch(),
            new NullBulkItemsReader(),
            new NullLogger()
        );
    }

    public function testGetMainScopeBuilder(): void
    {
        $this::assertSame($this->serviceBuilder->getMainScope(), $this->serviceBuilder->getMainScope());
    }

    public function testGetCrmScopeBuilder(): void
    {
        $this::assertSame($this->serviceBuilder->getCRMScope(), $this->serviceBuilder->getCRMScope());
    }

    public function testGetImScopeBuilder(): void
    {
        $this::assertSame($this->serviceBuilder->getIMScope(), $this->serviceBuilder->getIMScope());
    }

    public function testGetImOpenLinesBuilder(): void
    {
        $this::assertSame($this->serviceBuilder->getIMOpenLinesScope(), $this->serviceBuilder->getIMOpenLinesScope());
    }

    public function testGetUserConsentBuilder(): void
    {
        $this::assertSame($this->serviceBuilder->getUserConsentScope(), $this->serviceBuilder->getUserConsentScope());
    }

    public function testGetUserBuilder(): void
    {
        $this::assertSame($this->serviceBuilder->getUserScope(), $this->serviceBuilder->getUserScope());
    }

    public function testGetPlacementBuilder(): void
    {
        $this::assertSame($this->serviceBuilder->getPlacementScope(), $this->serviceBuilder->getPlacementScope());
    }

    public function testGetCatalogBuilder(): void
    {
        $this::assertSame($this->serviceBuilder->getCatalogScope(), $this->serviceBuilder->getCatalogScope());
    }

    public function testGetBizProcBuilder(): void
    {
        $this::assertSame($this->serviceBuilder->getBizProcScope(), $this->serviceBuilder->getBizProcScope());
    }

    public function testGetTelephonyBuilder(): void
    {
        $this::assertSame($this->serviceBuilder->getTelephonyScope(), $this->serviceBuilder->getTelephonyScope());
    }

}
