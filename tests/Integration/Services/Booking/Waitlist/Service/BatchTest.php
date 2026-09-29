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

namespace Bitrix24\SDK\Tests\Integration\Services\Booking\Waitlist\Service;

use Bitrix24\SDK\Services\Booking\Waitlist\Service\Batch;
use Bitrix24\SDK\Services\Booking\Waitlist\Service\Waitlist;
use Bitrix24\SDK\Tests\Integration\Services\Booking\Support\BookingScopeTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(Batch::class)]
#[CoversMethod(Batch::class, 'add')]
#[CoversMethod(Batch::class, 'list')]
#[CoversMethod(Batch::class, 'update')]
#[CoversMethod(Batch::class, 'delete')]
class BatchTest extends BookingScopeTestCase
{
    private Waitlist $waitlistService;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->waitlistService = $this->serviceBuilder->getBookingScope()->waitlist();
    }

    #[TestDox('test Batch::add, Batch::list, Batch::update, Batch::delete')]
    public function testAddListUpdateDelete(): void
    {
        $suffix = $this->uniqueSuffix();

        $addedIds = [];
        foreach (
            $this->waitlistService->batch->add([
                ['note' => 'Batch waitlist 1 ' . $suffix],
                ['note' => 'Batch waitlist 2 ' . $suffix],
            ]) as $addedItemResult
        ) {
            $addedIds[] = $addedItemResult->getId();
        }

        self::assertCount(2, $addedIds);
        $this->createdWaitlistIds = array_merge($this->createdWaitlistIds, $addedIds);

        $listedIds = [];
        foreach ($this->waitlistService->batch->list() as $waitlistItemResult) {
            $listedIds[] = $waitlistItemResult->id;
        }

        foreach ($addedIds as $addedId) {
            self::assertContains($addedId, $listedIds);
        }

        $updatePayload = [];
        foreach ($addedIds as $cnt => $addedId) {
            $updatePayload[$addedId] = ['note' => sprintf('Batch waitlist updated %s %s', $cnt, $suffix)];
        }

        $updatedCount = 0;
        foreach ($this->waitlistService->batch->update($updatePayload) as $updatedItemResult) {
            self::assertTrue($updatedItemResult->isSuccess());
            ++$updatedCount;
        }

        self::assertSame(2, $updatedCount);

        foreach ($addedIds as $cnt => $addedId) {
            self::assertSame(
                sprintf('Batch waitlist updated %s %s', $cnt, $suffix),
                $this->waitlistService->get($addedId)->getWaitlist()->note
            );
        }

        $deletedCount = 0;
        foreach ($this->waitlistService->batch->delete($addedIds) as $deletedItemResult) {
            self::assertTrue($deletedItemResult->isSuccess());
            ++$deletedCount;
        }

        self::assertSame(2, $deletedCount);
        $this->createdWaitlistIds = array_values(array_diff($this->createdWaitlistIds, $addedIds));
    }
}
