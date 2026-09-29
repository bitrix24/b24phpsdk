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

namespace Bitrix24\SDK\Tests\Integration\Services\Booking\ResourceType\Service;

use Bitrix24\SDK\Services\Booking\ResourceType\Service\ResourceType;
use Bitrix24\SDK\Tests\Integration\Services\Booking\Support\BookingScopeTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversClass(ResourceType::class)]
#[CoversMethod(ResourceType::class, 'add')]
#[CoversMethod(ResourceType::class, 'get')]
#[CoversMethod(ResourceType::class, 'list')]
#[CoversMethod(ResourceType::class, 'update')]
#[CoversMethod(ResourceType::class, 'delete')]
class ResourceTypeTest extends BookingScopeTestCase
{
    private ResourceType $resourceTypeService;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->resourceTypeService = $this->serviceBuilder->getBookingScope()->resourceType();
    }

    public function testAddGetListUpdateDelete(): void
    {
        $resourceTypeId = $this->createResourceType();

        self::assertGreaterThan(0, $resourceTypeId);

        $resourceTypeItemResult = $this->resourceTypeService->get($resourceTypeId)->getResourceType();
        self::assertSame($resourceTypeId, $resourceTypeItemResult->id);
        self::assertNotEmpty($resourceTypeItemResult->code);
        self::assertNotEmpty($resourceTypeItemResult->name);

        $updatedCode = 'b24phpsdk-' . $this->uniqueSuffix();
        $updatedName = 'Booking Resource Type Updated ' . $this->uniqueSuffix();
        self::assertTrue($this->resourceTypeService->update($resourceTypeId, [
            'code' => $updatedCode,
            'name' => $updatedName,
        ])->isSuccess());

        $updatedResourceType = $this->resourceTypeService->get($resourceTypeId)->getResourceType();
        self::assertSame($updatedCode, $updatedResourceType->code);
        self::assertSame($updatedName, $updatedResourceType->name);

        $listedResourceType = $this->findItemById(
            $this->resourceTypeService->list(['id' => $resourceTypeId], ['id' => 'desc'])->getResourceTypes(),
            $resourceTypeId
        );
        self::assertNotNull($listedResourceType);
        self::assertSame($updatedName, $listedResourceType->name);

        self::assertTrue($this->resourceTypeService->delete($resourceTypeId)->isSuccess());
    }
}