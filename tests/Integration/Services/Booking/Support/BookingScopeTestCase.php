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

namespace Bitrix24\SDK\Tests\Integration\Services\Booking\Support;

use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\TestCase;

abstract class BookingScopeTestCase extends TestCase
{
    protected ServiceBuilder $serviceBuilder;

    /**
     * @var int[]
     */
    protected array $createdBookingIds = [];

    /**
     * @var int[]
     */
    protected array $createdDealIds = [];

    /**
     * @var int[]
     */
    protected array $createdContactIds = [];

    /**
     * @var int[]
     */
    protected array $createdWaitlistIds = [];

    /**
     * @var int[]
     */
    protected array $createdResourceIds = [];

    /**
     * @var int[]
     */
    protected array $createdResourceTypeIds = [];

    #[\Override]
    protected function setUp(): void
    {
        $this->serviceBuilder = Fabric::getServiceBuilder();
    }

    #[\Override]
    protected function tearDown(): void
    {
        if (!isset($this->serviceBuilder)) {
            return;
        }

        foreach (array_reverse(array_values(array_unique($this->createdBookingIds))) as $bookingId) {
            try {
                $this->serviceBuilder->getBookingScope()->booking()->delete($bookingId);
            } catch (\Throwable) {
            }
        }

        foreach (array_reverse(array_values(array_unique($this->createdWaitlistIds))) as $waitlistId) {
            try {
                $this->serviceBuilder->getBookingScope()->waitlist()->delete($waitlistId);
            } catch (\Throwable) {
            }
        }

        foreach (array_reverse(array_values(array_unique($this->createdDealIds))) as $dealId) {
            try {
                $this->serviceBuilder->getCRMScope()->deal()->delete($dealId);
            } catch (\Throwable) {
            }
        }

        foreach (array_reverse(array_values(array_unique($this->createdContactIds))) as $contactId) {
            try {
                $this->serviceBuilder->getCRMScope()->contact()->delete($contactId);
            } catch (\Throwable) {
            }
        }

        foreach (array_reverse(array_values(array_unique($this->createdResourceIds))) as $resourceId) {
            try {
                $this->serviceBuilder->getBookingScope()->resource()->delete($resourceId);
            } catch (\Throwable) {
            }
        }

        foreach (array_reverse(array_values(array_unique($this->createdResourceTypeIds))) as $resourceTypeId) {
            for ($attempt = 0; $attempt < 3; $attempt++) {
                try {
                    $this->serviceBuilder->getBookingScope()->resourceType()->delete($resourceTypeId);
                    break;
                } catch (\Throwable) {
                }
            }
        }
    }

    protected function createResourceType(array $fields = []): int
    {
        $suffix = $this->uniqueSuffix();
        $resourceTypeId = $this->serviceBuilder->getBookingScope()->resourceType()->add(array_merge([
            'code' => 'b24phpsdk-' . $suffix,
            'name' => 'Booking Resource Type ' . $suffix,
        ], $fields))->getId();

        $this->createdResourceTypeIds[] = $resourceTypeId;

        return $resourceTypeId;
    }

    protected function createResource(int $resourceTypeId, array $fields = []): int
    {
        $suffix = $this->uniqueSuffix();
        $resourceId = $this->serviceBuilder->getBookingScope()->resource()->add(array_merge([
            'typeId' => $resourceTypeId,
            'name' => 'Booking Resource ' . $suffix,
            'description' => 'Booking resource ' . $suffix,
        ], $fields))->getId();

        $this->createdResourceIds[] = $resourceId;

        return $resourceId;
    }

    protected function createWaitlist(array $fields = []): int
    {
        $suffix = $this->uniqueSuffix();
        $waitlistId = $this->serviceBuilder->getBookingScope()->waitlist()->add(array_merge([
            'note' => 'Booking waitlist ' . $suffix,
        ], $fields))->getId();

        $this->createdWaitlistIds[] = $waitlistId;

        return $waitlistId;
    }

    protected function createBooking(int $resourceId, array $fields = []): int
    {
        $suffix = $this->uniqueSuffix();
        $bookingId = $this->serviceBuilder->getBookingScope()->booking()->add(array_merge([
            'name' => 'Booking ' . $suffix,
            'description' => 'Booking description ' . $suffix,
            'resourceIds' => [$resourceId],
            'datePeriod' => $this->buildDatePeriod(),
        ], $fields))->getId();

        $this->createdBookingIds[] = $bookingId;

        return $bookingId;
    }

    protected function createCrmContact(): int
    {
        $suffix = $this->uniqueSuffix();
        $contactId = $this->serviceBuilder->getCRMScope()->contact()->add([
            'NAME' => 'Booking contact ' . $suffix,
        ])->getId();

        $this->createdContactIds[] = $contactId;

        return $contactId;
    }

    protected function createCrmDeal(): int
    {
        $suffix = $this->uniqueSuffix();
        $dealId = $this->serviceBuilder->getCRMScope()->deal()->add([
            'TITLE' => 'Booking deal ' . $suffix,
        ])->getId();

        $this->createdDealIds[] = $dealId;

        return $dealId;
    }

    /**
     * @return array<string, array<string, int|string>>
     */
    protected function buildDatePeriod(string $timezone = 'Europe/Berlin', int $startOffsetSeconds = 7200, int $durationSeconds = 1800): array
    {
        $fromTimestamp = time() + $startOffsetSeconds;
        $toTimestamp = $fromTimestamp + $durationSeconds;

        return [
            'from' => [
                'timestamp' => $fromTimestamp,
                'timezone' => $timezone,
            ],
            'to' => [
                'timestamp' => $toTimestamp,
                'timezone' => $timezone,
            ],
        ];
    }

    /**
     * @param object[] $items
     */
    protected function findItemById(array $items, int $id): ?object
    {
        foreach ($items as $item) {
            if ((int)$item->id === $id) {
                return $item;
            }
        }

        return null;
    }

    protected function uniqueSuffix(): string
    {
        return str_replace('.', '', uniqid('', true));
    }
}