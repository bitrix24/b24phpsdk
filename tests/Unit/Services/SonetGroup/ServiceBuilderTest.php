<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\SonetGroup;

use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Contracts\BulkItemsReaderInterface;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Services\SonetGroup\SonetGroupServiceBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Class ServiceBuilderTest
 *
 * @package Bitrix24\SDK\Tests\Unit\Services\SonetGroup
 */
class ServiceBuilderTest extends TestCase
{
    private ServiceBuilder $serviceBuilder;

    protected function setUp(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $batch = $this->createMock(BatchOperationsInterface::class);
        $bulkItemsReader = $this->createMock(BulkItemsReaderInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $this->serviceBuilder = new ServiceBuilder($core, $batch, $bulkItemsReader, $logger);
    }

    public function testGetSonetGroupScope(): void
    {
        $sonetGroupServiceBuilder = $this->serviceBuilder->getSonetGroupScope();
        
        $this->assertInstanceOf(SonetGroupServiceBuilder::class, $sonetGroupServiceBuilder);
    }

    public function testGetSonetGroupScopeReturnsTheSameInstance(): void
    {
        $sonetGroupServiceBuilder1 = $this->serviceBuilder->getSonetGroupScope();
        $sonetGroupServiceBuilder2 = $this->serviceBuilder->getSonetGroupScope();
        
        $this->assertSame($sonetGroupServiceBuilder1, $sonetGroupServiceBuilder2);
    }
}