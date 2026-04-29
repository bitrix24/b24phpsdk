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
use Bitrix24\SDK\Services\IM\User\Result\UserStatusItemResult;
use Bitrix24\SDK\Services\IM\User\Service\UserStatus;
use Bitrix24\SDK\Services\IM\User\UserStatusType;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserStatus::class)]
class UserStatusTest extends TestCase
{
    private UserStatus $userStatusService;

    #[\Override]
    protected function setUp(): void
    {
        $this->userStatusService = Factory::getServiceBuilder()->getIMScope()->userStatus();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.user.status.get returns a status item result')]
    public function testGet(): void
    {
        $userStatusItemResult = $this->userStatusService->get()->status();

        $this->assertInstanceOf(UserStatusItemResult::class, $userStatusItemResult);
        $this->assertTrue($userStatusItemResult->STATUS === null || $userStatusItemResult->STATUS instanceof UserStatusType);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.user.status.set returns success for UserStatusType::Online')]
    public function testSet(): void
    {
        $updatedItemResult = $this->userStatusService->set(UserStatusType::Online);
        $this->assertTrue($updatedItemResult->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.user.status.idle.start returns success')]
    public function testIdleStart(): void
    {
        $updatedItemResult = $this->userStatusService->idleStart();
        $this->assertTrue($updatedItemResult->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.user.status.idle.end returns success')]
    public function testIdleEnd(): void
    {
        $updatedItemResult = $this->userStatusService->idleEnd();
        $this->assertTrue($updatedItemResult->isSuccess());
    }
}
