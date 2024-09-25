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

namespace Bitrix24\SDK\Tests\Unit\Services\CRM;

use Bitrix24\SDK\Services\CRM\CRMServiceBuilder;
use Bitrix24\SDK\Services\RemoteEventsFactory;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Unit\Stubs\NullBatch;
use Bitrix24\SDK\Tests\Unit\Stubs\NullBulkItemsReader;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(CRMServiceBuilder::class)]
class CRMServiceBuilderTest extends TestCase
{
    private CRMServiceBuilder $serviceBuilder;

    public function testGetSettingsService(): void
    {
        $this::assertSame($this->serviceBuilder->settings(), $this->serviceBuilder->settings());
    }

    public function testGetDealContactService(): void
    {
        $this::assertSame($this->serviceBuilder->dealContact(), $this->serviceBuilder->dealContact());
    }

    public function testGetDealCategoryService(): void
    {
        $this::assertSame($this->serviceBuilder->dealCategory(), $this->serviceBuilder->dealCategory());
    }

    public function testDealService(): void
    {
        $this::assertSame($this->serviceBuilder->deal(), $this->serviceBuilder->deal());
    }

    public function testContactService(): void
    {
        $this::assertSame($this->serviceBuilder->contact(), $this->serviceBuilder->contact());
    }

    public function testDealProductRowsService(): void
    {
        $this::assertSame($this->serviceBuilder->dealProductRows(), $this->serviceBuilder->dealProductRows());
    }

    public function testDealCategoryStageService(): void
    {
        $this::assertSame($this->serviceBuilder->dealCategoryStage(), $this->serviceBuilder->dealCategoryStage());
    }

    protected function setUp(): void
    {
        $this->serviceBuilder = (new ServiceBuilder(
            new NullCore(),
            new NullBatch(),
            new NullBulkItemsReader(),
            new NullLogger()
        ))->getCRMScope();
    }
}