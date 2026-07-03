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

namespace Bitrix24\SDK\Tests\Integration\Services\IM\User\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\User\Result\UserItemResult;
use Bitrix24\SDK\Services\IM\User\Service\User;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(User::class)]
final class UserTest extends TestCase
{
    private User $userService;

    #[\Override]
    protected function setUp(): void
    {
        $this->userService = Factory::getServiceBuilder()->getIMScope()->user();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.user.get without ID returns the current user profile')]
    public function testGetCurrentUser(): void
    {
        $user = $this->userService->get()->user();

        $this->assertInstanceOf(UserItemResult::class, $user);
        $this->assertGreaterThan(0, $user->id);
        $this->assertNotEmpty($user->name);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.user.get with ID returns the specified user profile')]
    public function testGetUserById(): void
    {
        $currentUser = $this->userService->get()->user();
        $this->assertInstanceOf(UserItemResult::class, $currentUser);

        $user = $this->userService->get($currentUser->id)->user();

        $this->assertInstanceOf(UserItemResult::class, $user);
        $this->assertSame($currentUser->id, $user->id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.user.list.get returns profiles for all requested user IDs')]
    public function testListGet(): void
    {
        $currentUser = $this->userService->get()->user();
        $this->assertInstanceOf(UserItemResult::class, $currentUser);

        $users = $this->userService->listGet([$currentUser->id])->users();

        $this->assertNotEmpty($users);
        $this->assertContainsOnlyInstancesOf(UserItemResult::class, $users);

        $ids = array_map(static fn(UserItemResult $userItemResult): int => $userItemResult->id, $users);
        $this->assertContains($currentUser->id, $ids);
    }
}
