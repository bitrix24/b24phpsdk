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

namespace Bitrix24\SDK\Tests\Integration\Services\Booking\BookingClient\Result;

use Bitrix24\SDK\Services\Booking\BookingClient\Result\BookingClientItemResult;
use Bitrix24\SDK\Services\Booking\BookingClient\Service\BookingClient;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Services\Booking\Support\BookingScopeTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Depends;

#[CoversClass(BookingClientItemResult::class)]
#[CoversMethod(BookingClient::class, 'list')]
class BookingClientItemResultAnnotationsTest extends BookingScopeTestCase
{
    use CustomBitrix24Assertions;

    /**
     * @var array<string, array{type: string}>
     */
    private const array BOOKING_CLIENT_FIELD_TYPES = [
        'id' => ['type' => 'int'],
        'type' => ['type' => 'array'],
    ];

    private BookingClient $bookingClientService;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->bookingClientService = $this->serviceBuilder->getBookingScope()->bookingClient();
    }

    /**
     * @return string[]
     */
    public function testAllSystemFieldsAnnotated(): array
    {
        $resourceTypeId = $this->createResourceType();
        $resourceId = $this->createResource($resourceTypeId);
        $bookingId = $this->createBooking($resourceId);
        $contactId = $this->createCrmContact();
        $fieldCodes = array_keys($this->getBookingClientPayload($bookingId, $contactId));

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            $fieldCodes,
            BookingClientItemResult::class
        );

        return $fieldCodes;
    }

    #[Depends('testAllSystemFieldsAnnotated')]
    public function testAllSystemFieldsHasValidTypeAnnotation(array $fieldCodes): void
    {
        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $this->mapFieldTypes($fieldCodes),
            BookingClientItemResult::class
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function getBookingClientPayload(int $bookingId, int $contactId): array
    {
        $this->ensureContactClientTypeIsAvailable();

        self::assertTrue($this->bookingClientService->set($bookingId, [[
            'id' => $contactId,
            'type' => [
                'module' => 'crm',
                'code' => 'CONTACT',
            ],
        ]])->isSuccess());

        $items = $this->bookingClientService
            ->list($bookingId)
            ->getCoreResponse()
            ->getResponseData()
            ->getResult()['bookingClient'];

        self::assertNotSame([], $items, 'booking.v1.booking.client.list returned no clients after set().');

        return $items[0];
    }

    private function ensureContactClientTypeIsAvailable(): void
    {
        $clientTypeCodes = array_map(
            static fn(object $clientType): ?string => $clientType->code,
            $this->serviceBuilder->getBookingScope()->clientType()->list()->getClientTypes()
        );

        if (!in_array('CONTACT', $clientTypeCodes, true)) {
            self::markTestSkipped('Portal has no CONTACT booking client type configured.');
        }
    }

    /**
     * @param string[] $fieldCodes
     *
     * @return array<string, array{type: string}>
     */
    private function mapFieldTypes(array $fieldCodes): array
    {
        $unknownFieldCodes = array_values(array_diff($fieldCodes, array_keys(self::BOOKING_CLIENT_FIELD_TYPES)));
        self::assertSame([], $unknownFieldCodes, 'Add type mappings for new BookingClient fields before asserting annotations.');

        return array_intersect_key(self::BOOKING_CLIENT_FIELD_TYPES, array_flip($fieldCodes));
    }
}