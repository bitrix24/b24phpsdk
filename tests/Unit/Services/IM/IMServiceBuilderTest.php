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

namespace Bitrix24\SDK\Tests\Unit\Services\IM;

use Bitrix24\SDK\Services\RemoteEventsFabric;
use Bitrix24\SDK\Services\IM\Chat\Service\Batch as ChatBatch;
use Bitrix24\SDK\Services\IM\Chat\Service\ChatUserBatch;
use Bitrix24\SDK\Services\IM\Counters\Service\Counters;
use Bitrix24\SDK\Services\IM\Department\Service\Batch as DepartmentBatch;
use Bitrix24\SDK\Services\IM\Department\Service\Department;
use Bitrix24\SDK\Services\IM\Dialog\Service\Batch as DialogBatch;
use Bitrix24\SDK\Services\IM\Dialog\Service\Dialog;
use Bitrix24\SDK\Services\IM\Disk\Service\Batch as DiskBatch;
use Bitrix24\SDK\Services\IM\Disk\Service\Disk;
use Bitrix24\SDK\Services\IM\IMServiceBuilder;
use Bitrix24\SDK\Services\IM\Message\Service\Batch as MessageBatch;
use Bitrix24\SDK\Services\IM\Notify\Service\Batch as NotifyBatch;
use Bitrix24\SDK\Services\IM\Recent\Service\Batch as RecentBatch;
use Bitrix24\SDK\Services\IM\Recent\Service\Recent;
use Bitrix24\SDK\Services\IM\Search\Service\Batch as SearchBatch;
use Bitrix24\SDK\Services\IM\User\Service\User;
use Bitrix24\SDK\Services\IM\Placements\Placements;
use Bitrix24\SDK\Services\IM\User\Service\UserStatus;
use Bitrix24\SDK\Services\IM\User\Service\UserStatusBatch;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Unit\Stubs\NullBatch;
use Bitrix24\SDK\Tests\Unit\Stubs\NullBulkItemsReader;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(IMServiceBuilder::class)]
class IMServiceBuilderTest extends TestCase
{
    private IMServiceBuilder $serviceBuilder;

    public function testGetIMService(): void
    {
        $this::assertSame($this->serviceBuilder->notify(), $this->serviceBuilder->notify());
    }

    public function testGetDiskService(): void
    {
        $this->assertInstanceOf(Disk::class, $this->serviceBuilder->disk());
        $this->assertSame($this->serviceBuilder->disk(), $this->serviceBuilder->disk());
    }

    public function testGetChatService(): void
    {
        $this::assertSame($this->serviceBuilder->chat(), $this->serviceBuilder->chat());
    }

    public function testGetMessageService(): void
    {
        $this::assertSame($this->serviceBuilder->message(), $this->serviceBuilder->message());
    }

    public function testGetDialogService(): void
    {
        $this->assertInstanceOf(Dialog::class, $this->serviceBuilder->dialog());
        $this->assertSame($this->serviceBuilder->dialog(), $this->serviceBuilder->dialog());
    }

    public function testGetRevisionService(): void
    {
        $this::assertSame($this->serviceBuilder->revision(), $this->serviceBuilder->revision());
    }

    public function testGetCountersService(): void
    {
        $this->assertInstanceOf(Counters::class, $this->serviceBuilder->counters());
        $this->assertSame($this->serviceBuilder->counters(), $this->serviceBuilder->counters());
    }

    public function testGetDepartmentService(): void
    {
        $this->assertInstanceOf(Department::class, $this->serviceBuilder->department());
        $this->assertSame($this->serviceBuilder->department(), $this->serviceBuilder->department());
    }

    public function testGetUserStatusService(): void
    {
        $this->assertInstanceOf(UserStatus::class, $this->serviceBuilder->userStatus());
        $this->assertSame($this->serviceBuilder->userStatus(), $this->serviceBuilder->userStatus());
    }

    public function testGetUserService(): void
    {
        $this->assertInstanceOf(User::class, $this->serviceBuilder->user());
        $this->assertSame($this->serviceBuilder->user(), $this->serviceBuilder->user());
    }

    public function testGetPlacementsService(): void
    {
        $this->assertInstanceOf(Placements::class, $this->serviceBuilder->placements());
        $this->assertSame($this->serviceBuilder->placements(), $this->serviceBuilder->placements());
    }

    public function testServicesExposeBatch(): void
    {
        $this->assertInstanceOf(ChatBatch::class, $this->serviceBuilder->chat()->batch);
        $this->assertInstanceOf(ChatUserBatch::class, $this->serviceBuilder->chatUser()->batch);
        $this->assertInstanceOf(DepartmentBatch::class, $this->serviceBuilder->department()->batch);
        $this->assertInstanceOf(DialogBatch::class, $this->serviceBuilder->dialog()->batch);
        $this->assertInstanceOf(DiskBatch::class, $this->serviceBuilder->disk()->batch);
        $this->assertInstanceOf(MessageBatch::class, $this->serviceBuilder->message()->batch);
        $this->assertInstanceOf(NotifyBatch::class, $this->serviceBuilder->notify()->batch);
        $this->assertInstanceOf(RecentBatch::class, $this->serviceBuilder->recent()->batch);
        $this->assertInstanceOf(SearchBatch::class, $this->serviceBuilder->search()->batch);
        $this->assertInstanceOf(UserStatusBatch::class, $this->serviceBuilder->userStatus()->batch);
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->serviceBuilder = (new ServiceBuilder(
            new NullCore(),
            new NullBatch(),
            new NullBulkItemsReader(),
            new NullLogger()
        ))->getIMScope();
    }
}
