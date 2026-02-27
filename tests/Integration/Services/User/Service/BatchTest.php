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

namespace Bitrix24\SDK\Tests\Integration\Services\User\Service;

use Bitrix24\SDK\Services\User\Service\Batch;
use Bitrix24\SDK\Services\User\Service\User;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
#[CoversMethod(Batch::class, 'add')]
class BatchTest extends TestCase
{
    private User $userService;

    #[TestDox('test add user')]
    public function testAdd(): void
    {
        $users = [];
        for ($i = 0; $i < 60; $i++) {
            $users[] = [
                'NAME' => sprintf('Test user #%d', time()),
                'EMAIL' => sprintf('%s.test@test.com', \Symfony\Component\Uid\Uuid::v7()->toBase32()),
                'EXTRANET' => 'N',
                'UF_DEPARTMENT' => [1]
            ];
        }

        $addedUserId = [];
        foreach ($this->userService->batch->add($users) as $addedItemBatchResult) {
            $addedUserId[] = $addedItemBatchResult->getId();
        }

        $this->assertEquals(count($users), count($addedUserId));
    }

    #[TestDox('test user get by batch')]
    public function testGet(): void
    {
        $users = [];
        foreach ($this->userService->batch->get([], [], true, 160) as $user) {
            $users[] = $user;
        }

        $this->assertGreaterThan(1, count($users));
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->userService = Factory::getServiceBuilder()->getUserScope()->user();
    }
}