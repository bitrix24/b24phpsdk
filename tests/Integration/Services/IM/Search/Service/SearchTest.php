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

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Search\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\Search\Result\SearchChatItemResult;
use Bitrix24\SDK\Services\IM\Search\Result\SearchDepartmentItemResult;
use Bitrix24\SDK\Services\IM\Search\Result\SearchUserItemResult;
use Bitrix24\SDK\Services\IM\Search\Service\Search;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Search::class)]
class SearchTest extends TestCase
{
    private Search $searchService;

    #[\Override]
    protected function setUp(): void
    {
        $this->searchService = Fabric::getServiceBuilder()->getIMScope()->search();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.search.chat.list returns a paginated list of SearchChatItemResult')]
    public function testChatList(): void
    {
        $searchChatsResult = $this->searchService->chatList(find: 'Test', limit: 1);
        $items = $searchChatsResult->items();

        $this->assertIsArray($items);
        $this->assertGreaterThanOrEqual(0, $searchChatsResult->total());
        $this->assertTrue($searchChatsResult->next() === null || $searchChatsResult->next() >= 0);

        if ($items === []) {
            $this->markTestSkipped('No chat search results available for FIND=Test');
        }

        $this->assertInstanceOf(SearchChatItemResult::class, $items[0]);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.search.user.list returns a list of SearchUserItemResult normalized from user-id keys')]
    public function testUserList(): void
    {
        $searchUsersResult = $this->searchService->userList(find: 'Maksim', limit: 1);
        $items = $searchUsersResult->items();

        $this->assertIsArray($items);
        $this->assertGreaterThanOrEqual(0, $searchUsersResult->total());

        if ($items === []) {
            $this->markTestSkipped('No user search results available for FIND=Maksim');
        }

        $this->assertInstanceOf(SearchUserItemResult::class, $items[0]);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.search.department.list returns a paginated list of SearchDepartmentItemResult')]
    public function testDepartmentList(): void
    {
        $searchDepartmentsResult = $this->searchService->departmentList(find: 'Отд', userData: true, limit: 3);
        $items = $searchDepartmentsResult->items();

        $this->assertIsArray($items);
        $this->assertGreaterThanOrEqual(0, $searchDepartmentsResult->total());
        $this->assertTrue($searchDepartmentsResult->next() === null || $searchDepartmentsResult->next() >= 0);

        if ($items === []) {
            $this->markTestSkipped('No department search results available for FIND=Отд');
        }

        $this->assertInstanceOf(SearchDepartmentItemResult::class, $items[0]);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('legacy im.search.last.add/get/delete endpoints manage last search history')]
    public function testLastSearchCycle(): void
    {
        $dialogId = '1';

        try {
            $this->assertTrue($this->searchService->lastAdd($dialogId)->isSuccess());

            $items = $this->searchService->lastGet()->items();
            $this->assertIsArray($items);
        } finally {
            $this->searchService->lastDelete($dialogId);
        }
    }
}
