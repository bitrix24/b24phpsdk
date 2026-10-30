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

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Search\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\Search\Result\SearchChatItemResult;
use Bitrix24\SDK\Services\IM\Search\Result\SearchDepartmentItemResult;
use Bitrix24\SDK\Services\IM\Search\Result\SearchUserItemResult;
use Bitrix24\SDK\Services\IM\Search\Service\Batch;
use Bitrix24\SDK\Services\IM\Search\Service\Search;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private Search $searchService;

    /**
     * @var int[]
     */
    private array $createdChats = [];

    #[\Override]
    protected function setUp(): void
    {
        $this->searchService = Fabric::getServiceBuilder(true)->getIMScope()->search();
    }

    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->createdChats as $createdChat) {
            try {
                $this->searchService->core->call('im.chat.leave', ['CHAT_ID' => $createdChat]);
            } catch (BaseException) {
                // chat may already be left
            }
        }

        $this->createdChats = [];
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('Batch im.search.chat.list returns all found chats')]
    public function testChatList(): void
    {
        $token = 'Srchbatch' . substr(md5(uniqid('', true)), 0, 8);
        $this->createChat($token . ' first');
        $this->createChat($token . ' second');

        $total = $this->searchService->chatList(find: $token)->total();

        $chats = iterator_to_array($this->searchService->batch->chatList(find: $token), false);

        $this->assertCount($total, $chats);
        $this->assertContainsOnlyInstancesOf(SearchChatItemResult::class, $chats);
    }

    /**
     * @throws BaseException
     */
    #[Test]
    #[TestDox('Batch im.search.chat.list respects the limit')]
    public function testChatListWithLimit(): void
    {
        $chats = iterator_to_array($this->searchService->batch->chatList(find: 'IT', limit: 1), false);

        $this->assertLessThanOrEqual(1, count($chats));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('Batch im.search.user.list returns all found users')]
    public function testUserList(): void
    {
        $profile = $this->searchService->core->call('PROFILE')->getResponseData()->getResult();
        $find = (string)($profile['LAST_NAME'] ?? '');
        if (mb_strlen($find) < 2) {
            $find = (string)($profile['NAME'] ?? '');
        }

        if (mb_strlen($find) < 2) {
            $this->markTestSkipped('Current user has no name suitable for im.search.user.list');
        }

        $total = $this->searchService->userList($find)->total();

        $users = iterator_to_array($this->searchService->batch->userList($find), false);

        $this->assertCount($total, $users);
        $this->assertContainsOnlyInstancesOf(SearchUserItemResult::class, $users);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('Batch im.search.department.list returns all departments for an empty search phrase')]
    public function testDepartmentList(): void
    {
        $total = $this->searchService->departmentList('')->total();

        $departments = iterator_to_array($this->searchService->batch->departmentList('', true), false);

        $this->assertCount($total, $departments);
        $this->assertContainsOnlyInstancesOf(SearchDepartmentItemResult::class, $departments);
    }

    /**
     * @throws BaseException
     */
    #[Test]
    #[TestDox('Batch legacy im.search.last.add and im.search.last.delete manage last search history')]
    public function testLastAddAndDelete(): void
    {
        $dialogIds = ['1'];

        foreach ($this->searchService->batch->lastAdd($dialogIds) as $updatedItemBatchResult) {
            $this->assertTrue($updatedItemBatchResult->isSuccess());
        }

        foreach ($this->searchService->batch->lastDelete($dialogIds) as $deletedItemBatchResult) {
            $this->assertTrue($deletedItemBatchResult->isSuccess());
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    private function createChat(string $title): void
    {
        $userId = (int)$this->searchService->core->call('PROFILE')->getResponseData()->getResult()['ID'];

        $this->createdChats[] = (int)$this->searchService->core->call('im.chat.add', [
            'USERS' => [$userId],
            'TYPE' => 'CHAT',
            'TITLE' => $title,
        ])->getResponseData()->getResult()[0];
    }
}
