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

namespace Bitrix24\SDK\Tests\Integration\Services\Booking\ResourceSlots\Service;

use Bitrix24\SDK\Services\Booking\ResourceSlots\Service\Batch;
use Bitrix24\SDK\Services\Booking\ResourceSlots\Service\ResourceSlots;
use Bitrix24\SDK\Tests\Integration\Services\Booking\Support\BookingScopeTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(Batch::class)]
#[CoversMethod(Batch::class, 'set')]
#[CoversMethod(Batch::class, 'list')]
class BatchTest extends BookingScopeTestCase
{
    private ResourceSlots $resourceSlotsService;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->resourceSlotsService = $this->serviceBuilder->getBookingScope()->resourceSlots();
    }

    #[TestDox('test Batch::set, Batch::list')]
    public function testSetList(): void
    {
        $resourceTypeId = $this->createResourceType();
        $firstResourceId = $this->createResource($resourceTypeId);
        $secondResourceId = $this->createResource($resourceTypeId);

        $setPayload = [
            $firstResourceId => [[
                'from' => 540,
                'to' => 1080,
                'timezone' => 'Europe/Berlin',
                'weekDays' => ['Mon', 'Tue'],
                'slotSize' => 30,
            ]],
            $secondResourceId => [[
                'from' => 600,
                'to' => 1200,
                'timezone' => 'Europe/Berlin',
                'weekDays' => ['Wed', 'Thu'],
                'slotSize' => 60,
            ]],
        ];

        $setResourceIds = [];
        foreach ($this->resourceSlotsService->batch->set($setPayload) as $resourceId => $updatedItemResult) {
            self::assertTrue($updatedItemResult->isSuccess());
            $setResourceIds[] = $resourceId;
        }

        self::assertSame([$firstResourceId, $secondResourceId], $setResourceIds);

        $slotsByResourceId = [];
        foreach ($this->resourceSlotsService->batch->list([$firstResourceId, $secondResourceId]) as $resourceId => $slots) {
            self::assertNotEmpty($slots);
            $slotsByResourceId[$resourceId] = $slots[0];
        }

        self::assertSame([$firstResourceId, $secondResourceId], array_keys($slotsByResourceId));
        self::assertSame(540, $slotsByResourceId[$firstResourceId]->from);
        self::assertSame(30, $slotsByResourceId[$firstResourceId]->slotSize);
        self::assertSame(600, $slotsByResourceId[$secondResourceId]->from);
        self::assertSame(60, $slotsByResourceId[$secondResourceId]->slotSize);
    }
}
