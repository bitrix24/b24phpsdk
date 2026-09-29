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

namespace Bitrix24\SDK\Tests\Integration\Services\Booking\BookingExternalData\Service;

use Bitrix24\SDK\Services\Booking\BookingExternalData\Service\Batch;
use Bitrix24\SDK\Services\Booking\BookingExternalData\Service\BookingExternalData;
use Bitrix24\SDK\Tests\Integration\Services\Booking\Support\BookingScopeTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(Batch::class)]
#[CoversMethod(Batch::class, 'set')]
#[CoversMethod(Batch::class, 'list')]
class BatchTest extends BookingScopeTestCase
{
    private BookingExternalData $bookingExternalDataService;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->bookingExternalDataService = $this->serviceBuilder->getBookingScope()->bookingExternalData();
    }

    #[TestDox('test Batch::set, Batch::list')]
    public function testSetList(): void
    {
        $resourceTypeId = $this->createResourceType();
        $resourceId = $this->createResource($resourceTypeId);
        $firstBookingId = $this->createBooking($resourceId, ['datePeriod' => $this->buildDatePeriod('Europe/Berlin', 7200)]);
        $secondBookingId = $this->createBooking($resourceId, ['datePeriod' => $this->buildDatePeriod('Europe/Berlin', 14400)]);
        $firstDealId = $this->createCrmDeal();
        $secondDealId = $this->createCrmDeal();

        $setPayload = [
            $firstBookingId => [['moduleId' => 'crm', 'entityTypeId' => 'DEAL', 'value' => (string)$firstDealId]],
            $secondBookingId => [['moduleId' => 'crm', 'entityTypeId' => 'DEAL', 'value' => (string)$secondDealId]],
        ];

        $setBookingIds = [];
        foreach ($this->bookingExternalDataService->batch->set($setPayload) as $bookingId => $updatedItemResult) {
            self::assertTrue($updatedItemResult->isSuccess());
            $setBookingIds[] = $bookingId;
        }

        self::assertSame([$firstBookingId, $secondBookingId], $setBookingIds);

        $valuesByBookingId = [];
        foreach ($this->bookingExternalDataService->batch->list([$firstBookingId, $secondBookingId]) as $bookingId => $externalData) {
            $valuesByBookingId[$bookingId] = array_map(
                static fn(object $item): ?string => $item->value,
                $externalData
            );
        }

        self::assertSame(
            [
                $firstBookingId => [(string)$firstDealId],
                $secondBookingId => [(string)$secondDealId],
            ],
            $valuesByBookingId
        );
    }
}
