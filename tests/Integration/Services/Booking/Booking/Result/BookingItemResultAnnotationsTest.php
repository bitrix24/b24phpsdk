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

namespace Bitrix24\SDK\Tests\Integration\Services\Booking\Booking\Result;

use Bitrix24\SDK\Services\Booking\Booking\Result\BookingItemResult;
use Bitrix24\SDK\Services\Booking\Booking\Service\Booking;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Services\Booking\Support\BookingScopeTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Depends;

#[CoversClass(BookingItemResult::class)]
#[CoversMethod(Booking::class, 'get')]
#[CoversMethod(Booking::class, 'list')]
class BookingItemResultAnnotationsTest extends BookingScopeTestCase
{
    use CustomBitrix24Assertions;

    /**
     * @var array<string, array{type: string}>
     */
    private const array BOOKING_FIELD_TYPES = [
        'id' => ['type' => 'int'],
        'name' => ['type' => 'string'],
        'description' => ['type' => 'string'],
        'datePeriod' => ['type' => 'array'],
        'resourceIds' => ['type' => 'array'],
    ];

    private Booking $bookingService;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->bookingService = $this->serviceBuilder->getBookingScope()->booking();
    }

    /**
     * @return array{get: string[], list: string[]}
     */
    public function testAllSystemFieldsAnnotated(): array
    {
        $resourceTypeId = $this->createResourceType();
        $resourceId = $this->createResource($resourceTypeId);
        $bookingId = $this->createBooking($resourceId);
        $getFieldCodes = array_keys($this->getBookingPayloadFromGet($bookingId));
        $listFieldCodes = array_keys($this->getBookingPayloadFromList($bookingId));

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            $getFieldCodes,
            BookingItemResult::class
        );

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            $listFieldCodes,
            BookingItemResult::class
        );

        return [
            'get' => $getFieldCodes,
            'list' => $listFieldCodes,
        ];
    }

    #[Depends('testAllSystemFieldsAnnotated')]
    public function testAllSystemFieldsHasValidTypeAnnotation(array $fieldCodesByMethod): void
    {
        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $this->mapFieldTypes($fieldCodesByMethod['get']),
            BookingItemResult::class
        );

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $this->mapFieldTypes($fieldCodesByMethod['list']),
            BookingItemResult::class
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function getBookingPayloadFromGet(int $bookingId): array
    {
        return $this->bookingService
            ->get($bookingId)
            ->getCoreResponse()
            ->getResponseData()
            ->getResult()['booking'];
    }

    /**
     * @return array<string, mixed>
     */
    private function getBookingPayloadFromList(int $bookingId): array
    {
        $items = $this->bookingService
            ->list(['id' => $bookingId], ['id' => 'desc'])
            ->getCoreResponse()
            ->getResponseData()
            ->getResult()['booking'];

        foreach ($items as $item) {
            if ((int)($item['id'] ?? 0) === $bookingId) {
                return $item;
            }
        }

        self::fail(sprintf('Booking %d was not found in booking.v1.booking.list response.', $bookingId));
    }

    /**
     * @param string[] $fieldCodes
     *
     * @return array<string, array{type: string}>
     */
    private function mapFieldTypes(array $fieldCodes): array
    {
        $unknownFieldCodes = array_values(array_diff($fieldCodes, array_keys(self::BOOKING_FIELD_TYPES)));
        self::assertSame([], $unknownFieldCodes, 'Add type mappings for new Booking fields before asserting annotations.');

        return array_intersect_key(self::BOOKING_FIELD_TYPES, array_flip($fieldCodes));
    }
}