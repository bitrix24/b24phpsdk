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

namespace Bitrix24\SDK\Tests\Integration\Services\Booking\Resource\Service;

use Bitrix24\SDK\Services\Booking\Resource\Service\Batch;
use Bitrix24\SDK\Services\Booking\Resource\Service\Resource;
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
    private Resource $resourceService;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->resourceService = $this->serviceBuilder->getBookingScope()->resource();
    }

    #[TestDox('test Batch::add, Batch::list, Batch::update, Batch::delete')]
    public function testAddListUpdateDelete(): void
    {
        $resourceTypeId = $this->createResourceType();
        $suffix = $this->uniqueSuffix();

        $addedIds = [];
        foreach (
            $this->resourceService->batch->add([
                [
                    'typeId' => $resourceTypeId,
                    'name' => 'Batch resource 1 ' . $suffix,
                    'description' => 'Batch resource 1 ' . $suffix,
                ],
                [
                    'typeId' => $resourceTypeId,
                    'name' => 'Batch resource 2 ' . $suffix,
                    'description' => 'Batch resource 2 ' . $suffix,
                ],
            ]) as $addedItemResult
        ) {
            $addedIds[] = $addedItemResult->getId();
        }

        self::assertCount(2, $addedIds);
        $this->createdResourceIds = array_merge($this->createdResourceIds, $addedIds);

        $listedIds = [];
        foreach ($this->resourceService->batch->list(['typeId' => $resourceTypeId]) as $resourceItemResult) {
            $listedIds[] = $resourceItemResult->id;
        }

        foreach ($addedIds as $addedId) {
            self::assertContains($addedId, $listedIds);
        }

        $updatePayload = [];
        foreach ($addedIds as $cnt => $addedId) {
            $updatePayload[$addedId] = ['name' => sprintf('Batch resource updated %s %s', $cnt, $suffix)];
        }

        $updatedCount = 0;
        foreach ($this->resourceService->batch->update($updatePayload) as $updatedItemResult) {
            self::assertTrue($updatedItemResult->isSuccess());
            ++$updatedCount;
        }

        self::assertSame(2, $updatedCount);

        foreach ($addedIds as $cnt => $addedId) {
            self::assertSame(
                sprintf('Batch resource updated %s %s', $cnt, $suffix),
                $this->resourceService->get($addedId)->getResource()->name
            );
        }

        $deletedCount = 0;
        foreach ($this->resourceService->batch->delete($addedIds) as $deletedItemResult) {
            self::assertTrue($deletedItemResult->isSuccess());
            ++$deletedCount;
        }

        self::assertSame(2, $deletedCount);
        $this->createdResourceIds = array_values(array_diff($this->createdResourceIds, $addedIds));
    }
}
