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

namespace Bitrix24\SDK\Tests\Integration\Services\Booking\BookingClient\Service;

use Bitrix24\SDK\Services\Booking\BookingClient\Service\Batch;
use Bitrix24\SDK\Services\Booking\BookingClient\Service\BookingClient;
use Bitrix24\SDK\Tests\Integration\Services\Booking\Support\BookingScopeTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(Batch::class)]
#[CoversMethod(Batch::class, 'set')]
#[CoversMethod(Batch::class, 'list')]
class BatchTest extends BookingScopeTestCase
{
    private BookingClient $bookingClientService;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->bookingClientService = $this->serviceBuilder->getBookingScope()->bookingClient();
    }

    #[TestDox('test Batch::set, Batch::list')]
    public function testSetList(): void
    {
        $clientTypeCodes = array_map(
            static fn(object $clientType): ?string => $clientType->code,
            $this->serviceBuilder->getBookingScope()->clientType()->list()->getClientTypes()
        );

        if (!in_array('CONTACT', $clientTypeCodes, true)) {
            self::markTestSkipped('Portal has no CONTACT booking client type configured.');
        }

        $resourceTypeId = $this->createResourceType();
        $resourceId = $this->createResource($resourceTypeId);
        $firstBookingId = $this->createBooking($resourceId, ['datePeriod' => $this->buildDatePeriod('Europe/Berlin', 7200)]);
        $secondBookingId = $this->createBooking($resourceId, ['datePeriod' => $this->buildDatePeriod('Europe/Berlin', 14400)]);
        $firstContactId = $this->createCrmContact();
        $secondContactId = $this->createCrmContact();

        $setPayload = [
            $firstBookingId => [['id' => $firstContactId, 'type' => ['module' => 'crm', 'code' => 'CONTACT']]],
            $secondBookingId => [['id' => $secondContactId, 'type' => ['module' => 'crm', 'code' => 'CONTACT']]],
        ];

        $setBookingIds = [];
        foreach ($this->bookingClientService->batch->set($setPayload) as $bookingId => $updatedItemResult) {
            self::assertTrue($updatedItemResult->isSuccess());
            $setBookingIds[] = $bookingId;
        }

        self::assertSame([$firstBookingId, $secondBookingId], $setBookingIds);

        $clientIdsByBookingId = [];
        foreach ($this->bookingClientService->batch->list([$firstBookingId, $secondBookingId]) as $bookingId => $clients) {
            $clientIdsByBookingId[$bookingId] = array_map(
                static fn(object $client): ?int => $client->id,
                $clients
            );
        }

        self::assertSame(
            [
                $firstBookingId => [$firstContactId],
                $secondBookingId => [$secondContactId],
            ],
            $clientIdsByBookingId
        );
    }
}
