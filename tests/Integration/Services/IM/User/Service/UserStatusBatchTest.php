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

namespace Bitrix24\SDK\Tests\Integration\Services\IM\User\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\User\Service\UserStatus;
use Bitrix24\SDK\Services\IM\User\Service\UserStatusBatch;
use Bitrix24\SDK\Services\IM\User\UserStatusType;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserStatusBatch::class)]
class UserStatusBatchTest extends TestCase
{
    private UserStatus $userStatusService;

    private ?UserStatusType $initialStatus = null;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->userStatusService = Factory::getServiceBuilder(true)->getIMScope()->userStatus();
        $this->initialStatus = $this->userStatusService->get()->status()->STATUS;
    }

    #[\Override]
    protected function tearDown(): void
    {
        try {
            $this->userStatusService->set($this->initialStatus ?? UserStatusType::Online);
        } catch (BaseException) {
            // status restore is best effort
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('Batch im.user.status.set applies statuses sequentially, the last one becomes current')]
    public function testSet(): void
    {
        $results = iterator_to_array(
            $this->userStatusService->batch->set([UserStatusType::Dnd, UserStatusType::Away]),
            false
        );

        $this->assertCount(2, $results);
        foreach ($results as $result) {
            $this->assertTrue($result->isSuccess());
        }

        $this->assertSame(UserStatusType::Away, $this->userStatusService->get()->status()->STATUS);
    }
}
