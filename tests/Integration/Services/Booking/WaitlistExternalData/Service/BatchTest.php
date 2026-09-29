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

namespace Bitrix24\SDK\Tests\Integration\Services\Booking\WaitlistExternalData\Service;

use Bitrix24\SDK\Services\Booking\WaitlistExternalData\Service\Batch;
use Bitrix24\SDK\Services\Booking\WaitlistExternalData\Service\WaitlistExternalData;
use Bitrix24\SDK\Tests\Integration\Services\Booking\Support\BookingScopeTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(Batch::class)]
#[CoversMethod(Batch::class, 'set')]
#[CoversMethod(Batch::class, 'list')]
class BatchTest extends BookingScopeTestCase
{
    private WaitlistExternalData $waitlistExternalDataService;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->waitlistExternalDataService = $this->serviceBuilder->getBookingScope()->waitlistExternalData();
    }

    #[TestDox('test Batch::set, Batch::list')]
    public function testSetList(): void
    {
        $firstWaitlistId = $this->createWaitlist();
        $secondWaitlistId = $this->createWaitlist();
        $firstDealId = $this->createCrmDeal();
        $secondDealId = $this->createCrmDeal();

        $setPayload = [
            $firstWaitlistId => [['moduleId' => 'crm', 'entityTypeId' => 'DEAL', 'value' => (string)$firstDealId]],
            $secondWaitlistId => [['moduleId' => 'crm', 'entityTypeId' => 'DEAL', 'value' => (string)$secondDealId]],
        ];

        $setWaitlistIds = [];
        foreach ($this->waitlistExternalDataService->batch->set($setPayload) as $waitListId => $updatedItemResult) {
            self::assertTrue($updatedItemResult->isSuccess());
            $setWaitlistIds[] = $waitListId;
        }

        self::assertSame([$firstWaitlistId, $secondWaitlistId], $setWaitlistIds);

        $valuesByWaitlistId = [];
        foreach ($this->waitlistExternalDataService->batch->list([$firstWaitlistId, $secondWaitlistId]) as $waitListId => $externalData) {
            $valuesByWaitlistId[$waitListId] = array_map(
                static fn(object $item): ?string => $item->value,
                $externalData
            );
        }

        self::assertSame(
            [
                $firstWaitlistId => [(string)$firstDealId],
                $secondWaitlistId => [(string)$secondDealId],
            ],
            $valuesByWaitlistId
        );
    }
}
