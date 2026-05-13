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

namespace Bitrix24\SDK\Tests\Unit\Services\Biconnector;

use Bitrix24\SDK\Services\Biconnector\BiconnectorServiceBuilder;
use Bitrix24\SDK\Tests\Unit\Stubs\NullBatch;
use Bitrix24\SDK\Tests\Unit\Stubs\NullBulkItemsReader;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(BiconnectorServiceBuilder::class)]
class BiconnectorServiceBuilderTest extends TestCase
{
    private BiconnectorServiceBuilder $serviceBuilder;

    #[\Override]
    protected function setUp(): void
    {
        $this->serviceBuilder = new BiconnectorServiceBuilder(
            new NullCore(),
            new NullBatch(),
            new NullBulkItemsReader(),
            new NullLogger()
        );
    }

    public function testConnectorServiceIsCached(): void
    {
        $this::assertSame($this->serviceBuilder->connector(), $this->serviceBuilder->connector());
    }
}
