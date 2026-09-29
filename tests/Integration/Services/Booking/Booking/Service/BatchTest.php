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

namespace Bitrix24\SDK\Tests\Integration\Services\Booking\Booking\Service;

use Bitrix24\SDK\Services\Booking\Booking\Service\Batch;
use Bitrix24\SDK\Services\Booking\Booking\Service\Booking;
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
    private Booking $bookingService;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->bookingService = $this->serviceBuilder->getBookingScope()->booking();
    }

    #[TestDox('test Batch::add, Batch::list, Batch::update, Batch::delete')]
    public function testAddListUpdateDelete(): void
    {
        $resourceTypeId = $this->createResourceType();
        $resourceId = $this->createResource($resourceTypeId);
        $suffix = $this->uniqueSuffix();

        $addedIds = [];
        foreach (
            $this->bookingService->batch->add([
                [
                    'name' => 'Batch booking 1 ' . $suffix,
                    'description' => 'Batch booking 1 ' . $suffix,
                    'resourceIds' => [$resourceId],
                    'datePeriod' => $this->buildDatePeriod('Europe/Berlin', 7200),
                ],
                [
                    'name' => 'Batch booking 2 ' . $suffix,
                    'description' => 'Batch booking 2 ' . $suffix,
                    'resourceIds' => [$resourceId],
                    'datePeriod' => $this->buildDatePeriod('Europe/Berlin', 14400),
                ],
            ]) as $addedItemResult
        ) {
            $addedIds[] = $addedItemResult->getId();
        }

        self::assertCount(2, $addedIds);
        $this->createdBookingIds = array_merge($this->createdBookingIds, $addedIds);

        $listedIds = [];
        foreach ($this->bookingService->batch->list() as $bookingItemResult) {
            $listedIds[] = $bookingItemResult->id;
        }

        foreach ($addedIds as $addedId) {
            self::assertContains($addedId, $listedIds);
        }

        $updatePayload = [];
        foreach ($addedIds as $cnt => $addedId) {
            $updatePayload[$addedId] = ['name' => sprintf('Batch booking updated %s %s', $cnt, $suffix)];
        }

        $updatedCount = 0;
        foreach ($this->bookingService->batch->update($updatePayload) as $updatedItemResult) {
            self::assertTrue($updatedItemResult->isSuccess());
            ++$updatedCount;
        }

        self::assertSame(2, $updatedCount);

        foreach ($addedIds as $cnt => $addedId) {
            self::assertSame(
                sprintf('Batch booking updated %s %s', $cnt, $suffix),
                $this->bookingService->get($addedId)->getBooking()->name
            );
        }

        $deletedCount = 0;
        foreach ($this->bookingService->batch->delete($addedIds) as $deletedItemResult) {
            self::assertTrue($deletedItemResult->isSuccess());
            ++$deletedCount;
        }

        self::assertSame(2, $deletedCount);
        $this->createdBookingIds = array_values(array_diff($this->createdBookingIds, $addedIds));
    }
}
