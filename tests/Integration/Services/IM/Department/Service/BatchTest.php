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

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Department\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\Department\Service\Batch;
use Bitrix24\SDK\Services\IM\Department\Service\Department;
use Bitrix24\SDK\Services\IM\User\Result\UserItemResult;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private Department $departmentService;

    #[\Override]
    protected function setUp(): void
    {
        $this->departmentService = Fabric::getServiceBuilder()->getIMScope()->department();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('Batch im.department.colleagues.list returns all colleagues of the current user')]
    public function testColleaguesList(): void
    {
        $total = $this->departmentService->colleaguesList()->total();

        $users = iterator_to_array($this->departmentService->batch->colleaguesList(), false);

        $this->assertCount($total, $users);
        if ($users === []) {
            $this->markTestSkipped('No colleagues available for im.department.colleagues.list');
        }

        $this->assertContainsOnlyInstancesOf(UserItemResult::class, $users);
        $this->assertGreaterThan(0, $users[0]->id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('Batch im.department.colleagues.list returns user IDs matching the detailed list')]
    public function testColleaguesIdsList(): void
    {
        $userIds = iterator_to_array($this->departmentService->batch->colleaguesIdsList(), false);
        $users = iterator_to_array($this->departmentService->batch->colleaguesList(), false);

        $this->assertContainsOnlyInt($userIds);
        $this->assertEqualsCanonicalizing(
            array_map(static fn (UserItemResult $userItemResult): int => (int)$userItemResult->id, $users),
            $userIds
        );
    }

    /**
     * @throws BaseException
     */
    #[Test]
    #[TestDox('Batch im.department.colleagues.list respects the limit')]
    public function testColleaguesListWithLimit(): void
    {
        $userIds = iterator_to_array($this->departmentService->batch->colleaguesIdsList(1), false);

        $this->assertLessThanOrEqual(1, count($userIds));
    }
}
