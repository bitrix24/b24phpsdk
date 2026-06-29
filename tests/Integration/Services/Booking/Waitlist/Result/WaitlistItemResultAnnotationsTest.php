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

namespace Bitrix24\SDK\Tests\Integration\Services\Booking\Waitlist\Result;

use Bitrix24\SDK\Services\Booking\Waitlist\Result\WaitlistItemResult;
use Bitrix24\SDK\Services\Booking\Waitlist\Service\Waitlist;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Services\Booking\Support\BookingScopeTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Depends;

#[CoversClass(WaitlistItemResult::class)]
#[CoversMethod(Waitlist::class, 'get')]
#[CoversMethod(Waitlist::class, 'list')]
class WaitlistItemResultAnnotationsTest extends BookingScopeTestCase
{
    use CustomBitrix24Assertions;

    /**
     * @var array<string, array{type: string}>
     */
    private const array WAITLIST_FIELD_TYPES = [
        'id' => ['type' => 'int'],
        'note' => ['type' => 'string'],
    ];

    private Waitlist $waitlistService;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->waitlistService = $this->serviceBuilder->getBookingScope()->waitlist();
    }

    /**
     * @return array{get: string[], list: string[]}
     */
    public function testAllSystemFieldsAnnotated(): array
    {
        $waitlistId = $this->createWaitlist();
        $getFieldCodes = array_keys($this->getWaitlistPayloadFromGet($waitlistId));
        $listFieldCodes = array_keys($this->getWaitlistPayloadFromList($waitlistId));

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            $getFieldCodes,
            WaitlistItemResult::class
        );

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            $listFieldCodes,
            WaitlistItemResult::class
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
            WaitlistItemResult::class
        );

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $this->mapFieldTypes($fieldCodesByMethod['list']),
            WaitlistItemResult::class
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function getWaitlistPayloadFromGet(int $waitlistId): array
    {
        return $this->waitlistService
            ->get($waitlistId)
            ->getCoreResponse()
            ->getResponseData()
            ->getResult()['waitList'];
    }

    /**
     * @return array<string, mixed>
     */
    private function getWaitlistPayloadFromList(int $waitlistId): array
    {
        $items = $this->waitlistService
            ->list(['id' => $waitlistId])
            ->getCoreResponse()
            ->getResponseData()
            ->getResult()['waitList'];

        foreach ($items as $item) {
            if ((int)($item['id'] ?? 0) === $waitlistId) {
                return $item;
            }
        }

        self::fail(sprintf('Waitlist entry %d was not found in booking.v1.waitlist.list response.', $waitlistId));
    }

    /**
     * @param string[] $fieldCodes
     *
     * @return array<string, array{type: string}>
     */
    private function mapFieldTypes(array $fieldCodes): array
    {
        $unknownFieldCodes = array_values(array_diff($fieldCodes, array_keys(self::WAITLIST_FIELD_TYPES)));
        self::assertSame([], $unknownFieldCodes, 'Add type mappings for new Booking waitlist fields before asserting annotations.');

        return array_intersect_key(self::WAITLIST_FIELD_TYPES, array_flip($fieldCodes));
    }
}