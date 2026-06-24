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

namespace Bitrix24\SDK\Tests\Integration\Services\Booking\Resource\Service;

use Bitrix24\SDK\Services\Booking\Resource\Service\Resource;
use Bitrix24\SDK\Tests\Integration\Services\Booking\Support\BookingScopeTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversClass(Resource::class)]
#[CoversMethod(Resource::class, 'add')]
#[CoversMethod(Resource::class, 'get')]
#[CoversMethod(Resource::class, 'list')]
#[CoversMethod(Resource::class, 'update')]
#[CoversMethod(Resource::class, 'delete')]
class ResourceTest extends BookingScopeTestCase
{
    private Resource $resourceService;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->resourceService = $this->serviceBuilder->getBookingScope()->resource();
    }

    public function testAddGetListUpdateDelete(): void
    {
        $resourceTypeId = $this->createResourceType();
        $resourceId = $this->createResource($resourceTypeId);

        self::assertGreaterThan(0, $resourceId);

        $resourceItemResult = $this->resourceService->get($resourceId)->getResource();
        self::assertSame($resourceId, $resourceItemResult->id);
        self::assertSame($resourceTypeId, $resourceItemResult->typeId);
        self::assertNotEmpty($resourceItemResult->name);

        $updatedName = 'Booking Resource Updated ' . $this->uniqueSuffix();
        $updatedDescription = 'Booking resource updated description ' . $this->uniqueSuffix();
        self::assertTrue($this->resourceService->update($resourceId, [
            'name' => $updatedName,
            'description' => $updatedDescription,
        ])->isSuccess());

        $updatedResource = $this->resourceService->get($resourceId)->getResource();
        self::assertSame($updatedName, $updatedResource->name);
        self::assertSame($updatedDescription, $updatedResource->description);

        $listedResource = $this->findItemById($this->resourceService->list(['id' => $resourceId], ['id' => 'desc'])->getResources(), $resourceId);
        self::assertNotNull($listedResource);
        self::assertSame($updatedName, $listedResource->name);

        self::assertTrue($this->resourceService->delete($resourceId)->isSuccess());
    }
}