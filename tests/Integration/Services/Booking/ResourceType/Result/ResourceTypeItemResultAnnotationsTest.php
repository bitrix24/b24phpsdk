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

namespace Bitrix24\SDK\Tests\Integration\Services\Booking\ResourceType\Result;

use Bitrix24\SDK\Services\Booking\ResourceType\Result\ResourceTypeItemResult;
use Bitrix24\SDK\Services\Booking\ResourceType\Service\ResourceType;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Services\Booking\Support\BookingScopeTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Depends;

#[CoversClass(ResourceTypeItemResult::class)]
#[CoversMethod(ResourceType::class, 'get')]
#[CoversMethod(ResourceType::class, 'list')]
class ResourceTypeItemResultAnnotationsTest extends BookingScopeTestCase
{
    use CustomBitrix24Assertions;

    /**
     * @var array<string, array{type: string}>
     */
    private const array RESOURCE_TYPE_FIELD_TYPES = [
        'id' => ['type' => 'int'],
        'code' => ['type' => 'string'],
        'name' => ['type' => 'string'],
        'cancellationNotificationDelay' => ['type' => 'int'],
        'confirmationCounterDelay' => ['type' => 'int'],
        'confirmationNotificationDelay' => ['type' => 'int'],
        'confirmationNotificationRepetitions' => ['type' => 'int'],
        'confirmationNotificationRepetitionsInterval' => ['type' => 'int'],
        'delayedCounterDelay' => ['type' => 'int'],
        'delayedNotificationDelay' => ['type' => 'int'],
        'infoNotificationDelay' => ['type' => 'int'],
        'isCancellationNotificationOn' => ['type' => 'string'],
        'isConfirmationNotificationOn' => ['type' => 'string'],
        'isDelayedNotificationOn' => ['type' => 'string'],
        'isFeedbackNotificationOn' => ['type' => 'string'],
        'isInfoNotificationOn' => ['type' => 'string'],
        'isReminderNotificationOn' => ['type' => 'string'],
        'reminderNotificationDelay' => ['type' => 'int'],
        'senderCode' => ['type' => 'string'],
        'templateTypeConfirmation' => ['type' => 'string'],
        'templateTypeDelayed' => ['type' => 'string'],
        'templateTypeFeedback' => ['type' => 'string'],
        'templateTypeReminder' => ['type' => 'string'],
    ];

    private ResourceType $resourceTypeService;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->resourceTypeService = $this->serviceBuilder->getBookingScope()->resourceType();
    }

    /**
     * @return array{get: string[], list: string[]}
     */
    public function testAllSystemFieldsAnnotated(): array
    {
        $resourceTypeId = $this->createResourceType();
        $getFieldCodes = array_keys($this->getResourceTypePayloadFromGet($resourceTypeId));
        $listFieldCodes = array_keys($this->getResourceTypePayloadFromList($resourceTypeId));

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            $getFieldCodes,
            ResourceTypeItemResult::class
        );

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            $listFieldCodes,
            ResourceTypeItemResult::class
        );

        return [
            'get' => $getFieldCodes,
            'list' => $listFieldCodes,
        ];
    }

    #[Depends('testAllSystemFieldsAnnotated')]
    public function testAllSystemFieldsHasValidTypeAnnotation(array $fieldCodesByMethod): void
    {
        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $this->mapFieldTypes($fieldCodesByMethod['get']),
            ResourceTypeItemResult::class
        );

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $this->mapFieldTypes($fieldCodesByMethod['list']),
            ResourceTypeItemResult::class
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function getResourceTypePayloadFromGet(int $resourceTypeId): array
    {
        return $this->resourceTypeService
            ->get($resourceTypeId)
            ->getCoreResponse()
            ->getResponseData()
            ->getResult()['resourceType'];
    }

    /**
     * @return array<string, mixed>
     */
    private function getResourceTypePayloadFromList(int $resourceTypeId): array
    {
        $items = $this->resourceTypeService
            ->list(['id' => $resourceTypeId], ['id' => 'desc'])
            ->getCoreResponse()
            ->getResponseData()
            ->getResult()['resourceType'];

        foreach ($items as $item) {
            if ((int)($item['id'] ?? 0) === $resourceTypeId) {
                return $item;
            }
        }

        self::fail(sprintf('Resource type %d was not found in booking.v1.resourceType.list response.', $resourceTypeId));
    }

    /**
     * @param string[] $fieldCodes
     *
     * @return array<string, array{type: string}>
     */
    private function mapFieldTypes(array $fieldCodes): array
    {
        $unknownFieldCodes = array_values(array_diff($fieldCodes, array_keys(self::RESOURCE_TYPE_FIELD_TYPES)));
        self::assertSame([], $unknownFieldCodes, 'Add type mappings for new Booking resource type fields before asserting annotations.');

        return array_intersect_key(self::RESOURCE_TYPE_FIELD_TYPES, array_flip($fieldCodes));
    }
}