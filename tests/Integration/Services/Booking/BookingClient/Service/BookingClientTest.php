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

namespace Bitrix24\SDK\Tests\Integration\Services\Booking\BookingClient\Service;

use Bitrix24\SDK\Services\Booking\BookingClient\Service\BookingClient;
use Bitrix24\SDK\Tests\Integration\Services\Booking\Support\BookingScopeTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversClass(BookingClient::class)]
#[CoversMethod(BookingClient::class, 'list')]
#[CoversMethod(BookingClient::class, 'set')]
#[CoversMethod(BookingClient::class, 'unset')]
class BookingClientTest extends BookingScopeTestCase
{
    private BookingClient $bookingClientService;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->bookingClientService = $this->serviceBuilder->getBookingScope()->bookingClient();
    }

    public function testSetListUnset(): void
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
        $bookingId = $this->createBooking($resourceId);
        $contactId = $this->createCrmContact();

        self::assertTrue($this->bookingClientService->set($bookingId, [[
            'id' => $contactId,
            'type' => [
                'module' => 'crm',
                'code' => 'CONTACT',
            ],
        ]])->isSuccess());

        $clients = $this->bookingClientService->list($bookingId)->getClients();
        self::assertCount(1, $clients);
        self::assertSame($contactId, $clients[0]->id);
        self::assertSame('crm', $clients[0]->type['module']);
        self::assertSame('CONTACT', $clients[0]->type['code']);

        self::assertTrue($this->bookingClientService->unset($bookingId)->isSuccess());
        self::assertCount(0, $this->bookingClientService->list($bookingId)->getClients());
    }
}