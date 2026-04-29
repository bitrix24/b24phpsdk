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

namespace Bitrix24\SDK\Tests\Unit\Services\IM\User\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\IM\User\Result\UserResult;
use Bitrix24\SDK\Services\IM\User\Result\UsersResult;
use Bitrix24\SDK\Services\IM\User\Service\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(User::class)]
final class UserTest extends TestCase
{
    private User $service;

    private CoreInterface&MockObject $coreMock;

    #[\Override]
    protected function setUp(): void
    {
        $this->coreMock = $this->createMock(CoreInterface::class);
        $this->service = new User($this->coreMock, new NullLogger());
    }

    #[Test]
    public function testGetWithoutUserIdCallsApiWithEmptyPayload(): void
    {
        $response = $this->createStub(Response::class);

        $this->coreMock
            ->expects($this->once())
            ->method('call')
            ->with('im.user.get', [])
            ->willReturn($response);

        $userResult = $this->service->get();

        self::assertInstanceOf(UserResult::class, $userResult);
    }

    #[Test]
    public function testGetWithUserIdPassesIdToApi(): void
    {
        $response = $this->createStub(Response::class);

        $this->coreMock
            ->expects($this->once())
            ->method('call')
            ->with('im.user.get', ['ID' => 42])
            ->willReturn($response);

        $userResult = $this->service->get(42);

        self::assertInstanceOf(UserResult::class, $userResult);
    }

    #[Test]
    public function testListGetPassesUserIdsToApi(): void
    {
        $response = $this->createStub(Response::class);

        $this->coreMock
            ->expects($this->once())
            ->method('call')
            ->with('im.user.list.get', ['ID' => [1, 2, 3]])
            ->willReturn($response);

        $usersResult = $this->service->listGet([1, 2, 3]);

        self::assertInstanceOf(UsersResult::class, $usersResult);
    }
}
