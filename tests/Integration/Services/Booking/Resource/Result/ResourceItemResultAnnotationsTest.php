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

namespace Bitrix24\SDK\Tests\Integration\Services\Booking\Resource\Result;

use Bitrix24\SDK\Services\Booking\Resource\Result\ResourceItemResult;
use Bitrix24\SDK\Services\Booking\Resource\Service\Resource;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Services\Booking\Support\BookingScopeTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Depends;

#[CoversClass(ResourceItemResult::class)]
#[CoversMethod(Resource::class, 'get')]
#[CoversMethod(Resource::class, 'list')]
class ResourceItemResultAnnotationsTest extends BookingScopeTestCase
{
    use CustomBitrix24Assertions;

    /**
     * @var array<string, array{type: string}>
     */
    private const array RESOURCE_FIELD_TYPES = [
        'id' => ['type' => 'int'],
        'name' => ['type' => 'string'],
        'description' => ['type' => 'string'],
        'typeId' => ['type' => 'int'],
        'isMain' => ['type' => 'string'],
        'isCancellationNotificationOn' => ['type' => 'string'],
        'isInfoNotificationOn' => ['type' => 'string'],
        'templateTypeInfo' => ['type' => 'string'],
        'isConfirmationNotificationOn' => ['type' => 'string'],
        'templateTypeConfirmation' => ['type' => 'string'],
        'isReminderNotificationOn' => ['type' => 'string'],
        'templateTypeReminder' => ['type' => 'string'],
        'isFeedbackNotificationOn' => ['type' => 'string'],
        'templateTypeFeedback' => ['type' => 'string'],
        'isDelayedNotificationOn' => ['type' => 'string'],
        'templateTypeDelayed' => ['type' => 'string'],
        'senderCode' => ['type' => 'string'],
        'cancellationNotificationDelay' => ['type' => 'int'],
        'infoNotificationDelay' => ['type' => 'int'],
        'reminderNotificationDelay' => ['type' => 'int'],
        'delayedNotificationDelay' => ['type' => 'int'],
        'delayedCounterDelay' => ['type' => 'int'],
        'confirmationNotificationDelay' => ['type' => 'int'],
        'confirmationNotificationRepetitions' => ['type' => 'int'],
        'confirmationNotificationRepetitionsInterval' => ['type' => 'int'],
        'confirmationCounterDelay' => ['type' => 'int'],
    ];

    private Resource $resourceService;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->resourceService = $this->serviceBuilder->getBookingScope()->resource();
    }

    /**
     * @return array{get: string[], list: string[]}
     */
    public function testAllSystemFieldsAnnotated(): array
    {
        $resourceTypeId = $this->createResourceType();
        $resourceId = $this->createResource($resourceTypeId);
        $getFieldCodes = array_keys($this->getResourcePayloadFromGet($resourceId));
        $listFieldCodes = array_keys($this->getResourcePayloadFromList($resourceId));

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            $getFieldCodes,
            ResourceItemResult::class
        );

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            $listFieldCodes,
            ResourceItemResult::class
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
            ResourceItemResult::class
        );

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $this->mapFieldTypes($fieldCodesByMethod['list']),
            ResourceItemResult::class
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function getResourcePayloadFromGet(int $resourceId): array
    {
        return $this->resourceService
            ->get($resourceId)
            ->getCoreResponse()
            ->getResponseData()
            ->getResult()['resource'];
    }

    /**
     * @return array<string, mixed>
     */
    private function getResourcePayloadFromList(int $resourceId): array
    {
        $items = $this->resourceService
            ->list(['id' => $resourceId], ['id' => 'desc'])
            ->getCoreResponse()
            ->getResponseData()
            ->getResult()['resource'];

        foreach ($items as $item) {
            if ((int)($item['id'] ?? 0) === $resourceId) {
                return $item;
            }
        }

        self::fail(sprintf('Resource %d was not found in booking.v1.resource.list response.', $resourceId));
    }

    /**
     * @param string[] $fieldCodes
     *
     * @return array<string, array{type: string}>
     */
    private function mapFieldTypes(array $fieldCodes): array
    {
        $unknownFieldCodes = array_values(array_diff($fieldCodes, array_keys(self::RESOURCE_FIELD_TYPES)));
        self::assertSame([], $unknownFieldCodes, 'Add type mappings for new Booking resource fields before asserting annotations.');

        return array_intersect_key(self::RESOURCE_FIELD_TYPES, array_flip($fieldCodes));
    }
}