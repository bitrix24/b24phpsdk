<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Veronica Akhmetova <264936994+fatestr1ngs@users.noreply.github.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Booking\Booking\Service;

use Bitrix24\SDK\Services\Booking\Booking\Service\Booking;
use Bitrix24\SDK\Tests\Integration\Services\Booking\Support\BookingScopeTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversClass(Booking::class)]
#[CoversMethod(Booking::class, 'add')]
#[CoversMethod(Booking::class, 'createFromWaitlist')]
#[CoversMethod(Booking::class, 'get')]
#[CoversMethod(Booking::class, 'list')]
#[CoversMethod(Booking::class, 'update')]
#[CoversMethod(Booking::class, 'delete')]
class BookingTest extends BookingScopeTestCase
{
    private Booking $bookingService;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->bookingService = $this->serviceBuilder->getBookingScope()->booking();
    }

    public function testAddGetListUpdateDelete(): void
    {
        $resourceTypeId = $this->createResourceType();
        $resourceId = $this->createResource($resourceTypeId);
        $bookingName = 'Booking name ' . $this->uniqueSuffix();
        $bookingDescription = 'Booking description ' . $this->uniqueSuffix();
        $bookingId = $this->createBooking($resourceId, [
            'name' => $bookingName,
            'description' => $bookingDescription,
        ]);

        self::assertGreaterThan(0, $bookingId);

        $bookingItemResult = $this->bookingService->get($bookingId)->getBooking();
        self::assertSame($bookingId, $bookingItemResult->id);
        self::assertSame($bookingName, $bookingItemResult->name);
        self::assertSame($bookingDescription, $bookingItemResult->description);
        self::assertSame([$resourceId], $bookingItemResult->resourceIds);

        $updatedName = 'Booking updated name ' . $this->uniqueSuffix();
        $updatedDescription = 'Booking updated description ' . $this->uniqueSuffix();
        self::assertTrue($this->bookingService->update($bookingId, [
            'name' => $updatedName,
            'description' => $updatedDescription,
        ])->isSuccess());

        $updatedBooking = $this->bookingService->get($bookingId)->getBooking();
        self::assertSame($updatedName, $updatedBooking->name);
        self::assertSame($updatedDescription, $updatedBooking->description);

        $listedBooking = $this->findItemById($this->bookingService->list(['id' => $bookingId], ['id' => 'desc'])->getBookings(), $bookingId);
        self::assertNotNull($listedBooking);
        self::assertSame($updatedName, $listedBooking->name);

        self::assertTrue($this->bookingService->delete($bookingId)->isSuccess());
    }

    public function testCreateFromWaitlist(): void
    {
        $resourceTypeId = $this->createResourceType();
        $resourceId = $this->createResource($resourceTypeId);
        $waitlistId = $this->createWaitlist();

        $bookingId = $this->bookingService->createFromWaitlist($waitlistId, [
            'resourceIds' => [$resourceId],
            'datePeriod' => $this->buildDatePeriod(),
        ])->getId();
        $this->createdBookingIds[] = $bookingId;

        self::assertGreaterThan(0, $bookingId);
        self::assertSame($bookingId, $this->bookingService->get($bookingId)->getBooking()->id);
    }
}