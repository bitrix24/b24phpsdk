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

namespace Bitrix24\SDK\Tests\Integration\Services\Booking\Waitlist\Service;

use Bitrix24\SDK\Services\Booking\Waitlist\Service\Waitlist;
use Bitrix24\SDK\Tests\Integration\Services\Booking\Support\BookingScopeTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversClass(Waitlist::class)]
#[CoversMethod(Waitlist::class, 'add')]
#[CoversMethod(Waitlist::class, 'createFromBooking')]
#[CoversMethod(Waitlist::class, 'get')]
#[CoversMethod(Waitlist::class, 'list')]
#[CoversMethod(Waitlist::class, 'update')]
#[CoversMethod(Waitlist::class, 'delete')]
class WaitlistTest extends BookingScopeTestCase
{
    private Waitlist $waitlistService;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->waitlistService = $this->serviceBuilder->getBookingScope()->waitlist();
    }

    public function testAddGetListUpdateDelete(): void
    {
        $note = 'Booking waitlist note ' . $this->uniqueSuffix();
        $waitlistId = $this->createWaitlist([
            'note' => $note,
        ]);

        self::assertGreaterThan(0, $waitlistId);

        $waitlistItemResult = $this->waitlistService->get($waitlistId)->getWaitlist();
        self::assertSame($waitlistId, $waitlistItemResult->id);
        self::assertSame($note, $waitlistItemResult->note);

        $updatedNote = 'Booking waitlist updated note ' . $this->uniqueSuffix();
        self::assertTrue($this->waitlistService->update($waitlistId, [
            'note' => $updatedNote,
        ])->isSuccess());

        $updatedWaitlist = $this->waitlistService->get($waitlistId)->getWaitlist();
        self::assertSame($updatedNote, $updatedWaitlist->note);

        $listedWaitlist = $this->findItemById($this->waitlistService->list(['id' => $waitlistId])->getWaitlists(), $waitlistId);
        self::assertNotNull($listedWaitlist);
        self::assertSame($updatedNote, $listedWaitlist->note);

        self::assertTrue($this->waitlistService->delete($waitlistId)->isSuccess());
    }

    public function testCreateFromBooking(): void
    {
        $resourceTypeId = $this->createResourceType();
        $resourceId = $this->createResource($resourceTypeId);
        $bookingId = $this->createBooking($resourceId);

        $waitlistId = $this->waitlistService->createFromBooking($bookingId)->getId();
        $this->createdWaitlistIds[] = $waitlistId;

        self::assertGreaterThan(0, $waitlistId);
        self::assertSame($waitlistId, $this->waitlistService->get($waitlistId)->getWaitlist()->id);
    }
}