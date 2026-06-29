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

namespace Bitrix24\SDK\Tests\Integration\Services\Booking\ResourceSlots\Service;

use Bitrix24\SDK\Services\Booking\ResourceSlots\Service\ResourceSlots;
use Bitrix24\SDK\Tests\Integration\Services\Booking\Support\BookingScopeTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversClass(ResourceSlots::class)]
#[CoversMethod(ResourceSlots::class, 'set')]
#[CoversMethod(ResourceSlots::class, 'list')]
#[CoversMethod(ResourceSlots::class, 'unset')]
class ResourceSlotsTest extends BookingScopeTestCase
{
    private ResourceSlots $resourceSlotsService;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->resourceSlotsService = $this->serviceBuilder->getBookingScope()->resourceSlots();
    }

    public function testSetListUnset(): void
    {
        $resourceTypeId = $this->createResourceType();
        $resourceId = $this->createResource($resourceTypeId);
        $slots = [[
            'from' => 540,
            'to' => 1080,
            'timezone' => 'Europe/Berlin',
            'weekDays' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
            'slotSize' => 30,
        ]];

        self::assertTrue($this->resourceSlotsService->set($resourceId, $slots)->isSuccess());

        $resourceSlots = $this->resourceSlotsService->list($resourceId)->getSlots();
        self::assertNotEmpty($resourceSlots);
        self::assertSame(540, $resourceSlots[0]->from);
        self::assertSame(1080, $resourceSlots[0]->to);
        self::assertSame('Europe/Berlin', $resourceSlots[0]->timezone);
        self::assertSame(30, $resourceSlots[0]->slotSize);
        self::assertContains('Mon', $resourceSlots[0]->weekDays);
        self::assertContains('Fri', $resourceSlots[0]->weekDays);

        self::assertTrue($this->resourceSlotsService->unset($resourceId)->isSuccess());

        self::assertCount(0, $this->resourceSlotsService->list($resourceId)->getSlots());
    }
}