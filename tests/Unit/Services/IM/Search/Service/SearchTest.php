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

namespace Bitrix24\SDK\Tests\Unit\Services\IM\Search\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\IM\Search\Result\SearchChatsResult;
use Bitrix24\SDK\Services\IM\Search\Result\SearchDepartmentsResult;
use Bitrix24\SDK\Services\IM\Search\Result\SearchLastItemsResult;
use Bitrix24\SDK\Services\IM\Search\Result\SearchUsersResult;
use Bitrix24\SDK\Services\IM\Search\Service\Search;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Search::class)]
final class SearchTest extends TestCase
{
    private Search $service;

    private CoreInterface&MockObject $coreMock;

    #[\Override]
    protected function setUp(): void
    {
        $this->coreMock = $this->createMock(CoreInterface::class);
        $this->service = new Search($this->coreMock, new NullLogger());
    }

    #[Test]
    public function testChatListMapsSearchAndPaginationArguments(): void
    {
        $response = $this->createStub(Response::class);

        $this->coreMock
            ->expects($this->once())
            ->method('call')
            ->with('im.search.chat.list', [
                'FIND' => 'Test',
                'FIND_LINES' => 'Test line',
                'OFFSET' => 10,
                'LIMIT' => 25,
            ])
            ->willReturn($response);

        $searchChatsResult = $this->service->chatList('Test', 'Test line', 10, 25);

        self::assertInstanceOf(SearchChatsResult::class, $searchChatsResult);
    }

    #[Test]
    public function testChatListOmitsNullArguments(): void
    {
        $response = $this->createStub(Response::class);

        $this->coreMock
            ->expects($this->once())
            ->method('call')
            ->with('im.search.chat.list', [])
            ->willReturn($response);

        $searchChatsResult = $this->service->chatList();

        self::assertInstanceOf(SearchChatsResult::class, $searchChatsResult);
    }

    #[Test]
    public function testUserListMapsSearchAndPaginationArguments(): void
    {
        $response = $this->createStub(Response::class);

        $this->coreMock
            ->expects($this->once())
            ->method('call')
            ->with('im.search.user.list', [
                'FIND' => 'Maksim',
                'OFFSET' => 0,
                'LIMIT' => 1,
            ])
            ->willReturn($response);

        $searchUsersResult = $this->service->userList('Maksim', 0, 1);

        self::assertInstanceOf(SearchUsersResult::class, $searchUsersResult);
    }

    #[Test]
    public function testDepartmentListMapsUserDataFlagAndPaginationArguments(): void
    {
        $response = $this->createStub(Response::class);

        $this->coreMock
            ->expects($this->once())
            ->method('call')
            ->with('im.search.department.list', [
                'FIND' => 'Отд',
                'USER_DATA' => 'Y',
                'OFFSET' => 0,
                'LIMIT' => 3,
            ])
            ->willReturn($response);

        $searchDepartmentsResult = $this->service->departmentList('Отд', true, 0, 3);

        self::assertInstanceOf(SearchDepartmentsResult::class, $searchDepartmentsResult);
    }

    #[Test]
    public function testLastAddCallsLegacyEndpointWithDialogId(): void
    {
        $response = $this->createStub(Response::class);

        $this->coreMock
            ->expects($this->once())
            ->method('call')
            ->with('im.search.last.add', ['DIALOG_ID' => '1'])
            ->willReturn($response);

        $updatedItemResult = $this->service->lastAdd('1');

        self::assertInstanceOf(UpdatedItemResult::class, $updatedItemResult);
    }

    #[Test]
    public function testLastGetMapsSkipFlags(): void
    {
        $response = $this->createStub(Response::class);

        $this->coreMock
            ->expects($this->once())
            ->method('call')
            ->with('im.search.last.get', [
                'SKIP_OPENLINES' => 'Y',
                'SKIP_CHAT' => 'N',
                'SKIP_DIALOG' => 'Y',
            ])
            ->willReturn($response);

        $searchLastItemsResult = $this->service->lastGet(true, false, true);

        self::assertInstanceOf(SearchLastItemsResult::class, $searchLastItemsResult);
    }

    #[Test]
    public function testLastDeleteCallsLegacyEndpointWithDialogId(): void
    {
        $response = $this->createStub(Response::class);

        $this->coreMock
            ->expects($this->once())
            ->method('call')
            ->with('im.search.last.delete', ['DIALOG_ID' => '1'])
            ->willReturn($response);

        $updatedItemResult = $this->service->lastDelete('1');

        self::assertInstanceOf(UpdatedItemResult::class, $updatedItemResult);
    }
}
