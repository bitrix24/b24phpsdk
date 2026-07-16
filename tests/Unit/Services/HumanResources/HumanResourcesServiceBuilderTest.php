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

namespace Bitrix24\SDK\Tests\Unit\Services\HumanResources;

use Bitrix24\SDK\Services\HumanResources\HumanResourcesServiceBuilder;
use Bitrix24\SDK\Tests\Unit\Stubs\NullBatch;
use Bitrix24\SDK\Tests\Unit\Stubs\NullBulkItemsReader;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(HumanResourcesServiceBuilder::class)]
class HumanResourcesServiceBuilderTest extends TestCase
{
    private HumanResourcesServiceBuilder $serviceBuilder;

    #[\Override]
    protected function setUp(): void
    {
        $this->serviceBuilder = new HumanResourcesServiceBuilder(
            new NullCore(),
            new NullBatch(),
            new NullBulkItemsReader(),
            new NullLogger()
        );
    }

    public function testEmployeeServiceIsCached(): void
    {
        self::assertSame($this->serviceBuilder->employee(), $this->serviceBuilder->employee());
    }

    public function testEmployeeFieldServiceIsCached(): void
    {
        self::assertSame($this->serviceBuilder->employeeField(), $this->serviceBuilder->employeeField());
    }

    public function testNodeServiceIsCached(): void
    {
        self::assertSame($this->serviceBuilder->node(), $this->serviceBuilder->node());
    }

    public function testNodeFieldServiceIsCached(): void
    {
        self::assertSame($this->serviceBuilder->nodeField(), $this->serviceBuilder->nodeField());
    }

    public function testNodeCommunicationServiceIsCached(): void
    {
        self::assertSame($this->serviceBuilder->nodeCommunication(), $this->serviceBuilder->nodeCommunication());
    }

    public function testNodeMemberServiceIsCached(): void
    {
        self::assertSame($this->serviceBuilder->nodeMember(), $this->serviceBuilder->nodeMember());
    }

    public function testNodeMemberFieldServiceIsCached(): void
    {
        self::assertSame($this->serviceBuilder->nodeMemberField(), $this->serviceBuilder->nodeMemberField());
    }
}
