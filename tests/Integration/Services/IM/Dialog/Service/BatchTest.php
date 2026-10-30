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

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Dialog\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\Dialog\Result\DialogUserItemResult;
use Bitrix24\SDK\Services\IM\Dialog\Service\Batch;
use Bitrix24\SDK\Services\IM\Dialog\Service\Dialog;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private Dialog $dialogService;

    private int $currentUserId = 0;

    private ?int $chatId = null;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->dialogService = Fabric::getServiceBuilder()->getIMScope()->dialog();
        $this->currentUserId = (int)$this->dialogService->core
            ->call('PROFILE')->getResponseData()->getResult()['ID'];
    }

    #[\Override]
    protected function tearDown(): void
    {
        if ($this->chatId !== null) {
            try {
                $this->dialogService->core->call('im.chat.leave', ['CHAT_ID' => $this->chatId]);
            } catch (BaseException) {
                // chat may already be left
            }
        }

        $this->chatId = null;
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('Batch im.dialog.users.list returns all participants of a dialog')]
    public function testUsersList(): void
    {
        $users = $this->findActiveUserIds(5);
        $dialogId = 'chat' . $this->createChat($users);

        $total = $this->dialogService->usersList($dialogId)->total();

        $participants = iterator_to_array($this->dialogService->batch->usersList($dialogId), false);

        $this->assertCount($total, $participants);
        $this->assertContainsOnlyInstancesOf(DialogUserItemResult::class, $participants);
        $this->assertEqualsCanonicalizing(
            $users,
            array_map(static fn (DialogUserItemResult $dialogUserItemResult): int => (int)$dialogUserItemResult->id, $participants)
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('Batch im.dialog.users.list respects the limit')]
    public function testUsersListWithLimit(): void
    {
        $dialogId = 'chat' . $this->createChat($this->findActiveUserIds(3));

        $participants = iterator_to_array($this->dialogService->batch->usersList($dialogId, limit: 1), false);

        $this->assertCount(1, $participants);
    }

    /**
     * @param int[] $users
     *
     * @throws BaseException
     * @throws TransportException
     */
    private function createChat(array $users): int
    {
        $this->chatId = (int)$this->dialogService->core->call('im.chat.add', [
            'USERS' => $users,
            'TYPE' => 'CHAT',
            'TITLE' => sprintf('IT IM Dialog Batch %s', uniqid('', true)),
        ])->getResponseData()->getResult()[0];

        return $this->chatId;
    }

    /**
     * Returns the current user ID and up to $count - 1 other active user IDs
     *
     * @return int[]
     *
     * @throws BaseException
     * @throws TransportException
     */
    private function findActiveUserIds(int $count): array
    {
        $userIds = [$this->currentUserId];
        $response = $this->dialogService->core->call('user.get', [
            'FILTER' => ['ACTIVE' => true, 'USER_TYPE' => 'employee'],
        ])->getResponseData()->getResult();

        foreach ($response as $user) {
            $id = (int)($user['ID'] ?? 0);
            if ($id > 0 && !in_array($id, $userIds, true)) {
                $userIds[] = $id;
            }

            if (count($userIds) >= $count) {
                break;
            }
        }

        return $userIds;
    }
}
