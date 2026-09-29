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

namespace Bitrix24\SDK\Tests\Integration\Services\Booking\ResourceSlots\Result;

use Bitrix24\SDK\Services\Booking\ResourceSlots\Result\ResourceSlotItemResult;
use Bitrix24\SDK\Services\Booking\ResourceSlots\Service\ResourceSlots;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Services\Booking\Support\BookingScopeTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Depends;

#[CoversClass(ResourceSlotItemResult::class)]
#[CoversMethod(ResourceSlots::class, 'list')]
class ResourceSlotItemResultAnnotationsTest extends BookingScopeTestCase
{
    use CustomBitrix24Assertions;

    /**
     * @var array<string, array{type: string}>
     */
    private const array RESOURCE_SLOT_FIELD_TYPES = [
        'id' => ['type' => 'int'],
        'from' => ['type' => 'int'],
        'to' => ['type' => 'int'],
        'timezone' => ['type' => 'string'],
        'weekDays' => ['type' => 'array'],
        'slotSize' => ['type' => 'int'],
    ];

    private ResourceSlots $resourceSlotsService;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->resourceSlotsService = $this->serviceBuilder->getBookingScope()->resourceSlots();
    }

    /**
     * @return string[]
     */
    public function testAllSystemFieldsAnnotated(): array
    {
        $resourceTypeId = $this->createResourceType();
        $resourceId = $this->createResource($resourceTypeId);
        $fieldCodes = array_keys($this->getResourceSlotPayload($resourceId));

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            $fieldCodes,
            ResourceSlotItemResult::class
        );

        return $fieldCodes;
    }

    #[Depends('testAllSystemFieldsAnnotated')]
    public function testAllSystemFieldsHasValidTypeAnnotation(array $fieldCodes): void
    {
        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $this->mapFieldTypes($fieldCodes),
            ResourceSlotItemResult::class
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function getResourceSlotPayload(int $resourceId): array
    {
        self::assertTrue($this->resourceSlotsService->set($resourceId, [[
            'from' => 540,
            'to' => 1080,
            'timezone' => 'Europe/Berlin',
            'weekDays' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
            'slotSize' => 30,
        ]])->isSuccess());

        $items = $this->resourceSlotsService
            ->list($resourceId)
            ->getCoreResponse()
            ->getResponseData()
            ->getResult()['slots'];

        self::assertNotSame([], $items, 'booking.v1.resource.slots.list returned no slots after set().');

        return $items[0];
    }

    /**
     * @param string[] $fieldCodes
     *
     * @return array<string, array{type: string}>
     */
    private function mapFieldTypes(array $fieldCodes): array
    {
        $unknownFieldCodes = array_values(array_diff($fieldCodes, array_keys(self::RESOURCE_SLOT_FIELD_TYPES)));
        self::assertSame([], $unknownFieldCodes, 'Add type mappings for new ResourceSlot fields before asserting annotations.');

        return array_intersect_key(self::RESOURCE_SLOT_FIELD_TYPES, array_flip($fieldCodes));
    }
}