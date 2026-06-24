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

namespace Bitrix24\SDK\Tests\Integration\Services\Booking\BookingExternalData\Service;

use Bitrix24\SDK\Services\Booking\BookingExternalData\Service\BookingExternalData;
use Bitrix24\SDK\Tests\Integration\Services\Booking\Support\BookingScopeTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversClass(BookingExternalData::class)]
#[CoversMethod(BookingExternalData::class, 'list')]
#[CoversMethod(BookingExternalData::class, 'set')]
#[CoversMethod(BookingExternalData::class, 'unset')]
class BookingExternalDataTest extends BookingScopeTestCase
{
    private BookingExternalData $bookingExternalDataService;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->bookingExternalDataService = $this->serviceBuilder->getBookingScope()->bookingExternalData();
    }

    public function testSetListUnset(): void
    {
        $resourceTypeId = $this->createResourceType();
        $resourceId = $this->createResource($resourceTypeId);
        $bookingId = $this->createBooking($resourceId);
        $dealId = $this->createCrmDeal();

        self::assertTrue($this->bookingExternalDataService->set($bookingId, [[
            'moduleId' => 'crm',
            'entityTypeId' => 'DEAL',
            'value' => (string)$dealId,
        ]])->isSuccess());

        $externalData = $this->bookingExternalDataService->list($bookingId)->getExternalData();
        self::assertCount(1, $externalData);
        self::assertSame('crm', $externalData[0]->moduleId);
        self::assertSame('DEAL', $externalData[0]->entityTypeId);
        self::assertSame((string)$dealId, $externalData[0]->value);

        self::assertTrue($this->bookingExternalDataService->unset($bookingId)->isSuccess());
        self::assertCount(0, $this->bookingExternalDataService->list($bookingId)->getExternalData());
    }
}