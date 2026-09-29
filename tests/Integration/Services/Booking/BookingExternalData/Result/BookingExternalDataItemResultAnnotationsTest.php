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

namespace Bitrix24\SDK\Tests\Integration\Services\Booking\BookingExternalData\Result;

use Bitrix24\SDK\Services\Booking\BookingExternalData\Result\BookingExternalDataItemResult;
use Bitrix24\SDK\Services\Booking\BookingExternalData\Service\BookingExternalData;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Services\Booking\Support\BookingScopeTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Depends;

#[CoversClass(BookingExternalDataItemResult::class)]
#[CoversMethod(BookingExternalData::class, 'list')]
class BookingExternalDataItemResultAnnotationsTest extends BookingScopeTestCase
{
    use CustomBitrix24Assertions;

    /**
     * @var array<string, array{type: string}>
     */
    private const array BOOKING_EXTERNAL_DATA_FIELD_TYPES = [
        'moduleId' => ['type' => 'string'],
        'entityTypeId' => ['type' => 'string'],
        'value' => ['type' => 'string'],
    ];

    private BookingExternalData $bookingExternalDataService;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->bookingExternalDataService = $this->serviceBuilder->getBookingScope()->bookingExternalData();
    }

    /**
     * @return string[]
     */
    public function testAllSystemFieldsAnnotated(): array
    {
        $resourceTypeId = $this->createResourceType();
        $resourceId = $this->createResource($resourceTypeId);
        $bookingId = $this->createBooking($resourceId);
        $dealId = $this->createCrmDeal();
        $fieldCodes = array_keys($this->getBookingExternalDataPayload($bookingId, $dealId));

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            $fieldCodes,
            BookingExternalDataItemResult::class
        );

        return $fieldCodes;
    }

    #[Depends('testAllSystemFieldsAnnotated')]
    public function testAllSystemFieldsHasValidTypeAnnotation(array $fieldCodes): void
    {
        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $this->mapFieldTypes($fieldCodes),
            BookingExternalDataItemResult::class
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function getBookingExternalDataPayload(int $bookingId, int $dealId): array
    {
        self::assertTrue($this->bookingExternalDataService->set($bookingId, [[
            'moduleId' => 'crm',
            'entityTypeId' => 'DEAL',
            'value' => (string)$dealId,
        ]])->isSuccess());

        $items = $this->bookingExternalDataService
            ->list($bookingId)
            ->getCoreResponse()
            ->getResponseData()
            ->getResult()['externalData'];

        self::assertNotSame([], $items, 'booking.v1.booking.externalData.list returned no external data after set().');

        return $items[0];
    }

    /**
     * @param string[] $fieldCodes
     *
     * @return array<string, array{type: string}>
     */
    private function mapFieldTypes(array $fieldCodes): array
    {
        $unknownFieldCodes = array_values(array_diff($fieldCodes, array_keys(self::BOOKING_EXTERNAL_DATA_FIELD_TYPES)));
        self::assertSame([], $unknownFieldCodes, 'Add type mappings for new BookingExternalData fields before asserting annotations.');

        return array_intersect_key(self::BOOKING_EXTERNAL_DATA_FIELD_TYPES, array_flip($fieldCodes));
    }
}