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

namespace Bitrix24\SDK\Tests\Integration\Services\Booking\ResourceType\Service;

use Bitrix24\SDK\Services\Booking\ResourceType\Service\Batch;
use Bitrix24\SDK\Services\Booking\ResourceType\Service\ResourceType;
use Bitrix24\SDK\Tests\Integration\Services\Booking\Support\BookingScopeTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(Batch::class)]
#[CoversMethod(Batch::class, 'add')]
#[CoversMethod(Batch::class, 'list')]
#[CoversMethod(Batch::class, 'update')]
#[CoversMethod(Batch::class, 'delete')]
class BatchTest extends BookingScopeTestCase
{
    private ResourceType $resourceTypeService;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->resourceTypeService = $this->serviceBuilder->getBookingScope()->resourceType();
    }

    #[TestDox('test Batch::add, Batch::list, Batch::update, Batch::delete')]
    public function testAddListUpdateDelete(): void
    {
        $suffix = $this->uniqueSuffix();

        $addedIds = [];
        foreach (
            $this->resourceTypeService->batch->add([
                [
                    'code' => 'b24phpsdk-batch-1-' . $suffix,
                    'name' => 'Batch resource type 1 ' . $suffix,
                ],
                [
                    'code' => 'b24phpsdk-batch-2-' . $suffix,
                    'name' => 'Batch resource type 2 ' . $suffix,
                ],
            ]) as $addedItemResult
        ) {
            $addedIds[] = $addedItemResult->getId();
        }

        self::assertCount(2, $addedIds);
        $this->createdResourceTypeIds = array_merge($this->createdResourceTypeIds, $addedIds);

        $listedIds = [];
        foreach ($this->resourceTypeService->batch->list(['moduleId' => 'booking']) as $resourceTypeItemResult) {
            $listedIds[] = $resourceTypeItemResult->id;
        }

        foreach ($addedIds as $addedId) {
            self::assertContains($addedId, $listedIds);
        }

        // booking.v1.resourceType.update requires the code even when only the name changes
        $updatePayload = [];
        foreach ($addedIds as $cnt => $addedId) {
            $updatePayload[$addedId] = [
                'code' => sprintf('b24phpsdk-batch-%s-%s', $cnt, $suffix),
                'name' => sprintf('Batch resource type updated %s %s', $cnt, $suffix),
            ];
        }

        $updatedCount = 0;
        foreach ($this->resourceTypeService->batch->update($updatePayload) as $updatedItemResult) {
            self::assertTrue($updatedItemResult->isSuccess());
            ++$updatedCount;
        }

        self::assertSame(2, $updatedCount);

        foreach ($addedIds as $cnt => $addedId) {
            self::assertSame(
                sprintf('Batch resource type updated %s %s', $cnt, $suffix),
                $this->resourceTypeService->get($addedId)->getResourceType()->name
            );
        }

        $deletedCount = 0;
        foreach ($this->resourceTypeService->batch->delete($addedIds) as $deletedItemResult) {
            self::assertTrue($deletedItemResult->isSuccess());
            ++$deletedCount;
        }

        self::assertSame(2, $deletedCount);
        $this->createdResourceTypeIds = array_values(array_diff($this->createdResourceTypeIds, $addedIds));
    }
}
