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

namespace Bitrix24\SDK\Tests\Integration\Services\Booking\WaitlistExternalData\Result;

use Bitrix24\SDK\Services\Booking\WaitlistExternalData\Result\WaitlistExternalDataItemResult;
use Bitrix24\SDK\Services\Booking\WaitlistExternalData\Service\WaitlistExternalData;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Services\Booking\Support\BookingScopeTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Depends;

#[CoversClass(WaitlistExternalDataItemResult::class)]
#[CoversMethod(WaitlistExternalData::class, 'list')]
class WaitlistExternalDataItemResultAnnotationsTest extends BookingScopeTestCase
{
    use CustomBitrix24Assertions;

    /**
     * @var array<string, array{type: string}>
     */
    private const array WAITLIST_EXTERNAL_DATA_FIELD_TYPES = [
        'moduleId' => ['type' => 'string'],
        'entityTypeId' => ['type' => 'string'],
        'value' => ['type' => 'string'],
    ];

    private WaitlistExternalData $waitlistExternalDataService;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->waitlistExternalDataService = $this->serviceBuilder->getBookingScope()->waitlistExternalData();
    }

    /**
     * @return string[]
     */
    public function testAllSystemFieldsAnnotated(): array
    {
        $waitlistId = $this->createWaitlist();
        $dealId = $this->createCrmDeal();
        $fieldCodes = array_keys($this->getWaitlistExternalDataPayload($waitlistId, $dealId));

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            $fieldCodes,
            WaitlistExternalDataItemResult::class
        );

        return $fieldCodes;
    }

    #[Depends('testAllSystemFieldsAnnotated')]
    public function testAllSystemFieldsHasValidTypeAnnotation(array $fieldCodes): void
    {
        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $this->mapFieldTypes($fieldCodes),
            WaitlistExternalDataItemResult::class
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function getWaitlistExternalDataPayload(int $waitlistId, int $dealId): array
    {
        self::assertTrue($this->waitlistExternalDataService->set($waitlistId, [[
            'moduleId' => 'crm',
            'entityTypeId' => 'DEAL',
            'value' => (string)$dealId,
        ]])->isSuccess());

        $items = $this->waitlistExternalDataService
            ->list($waitlistId)
            ->getCoreResponse()
            ->getResponseData()
            ->getResult()['externalData'];

        self::assertNotSame([], $items, 'booking.v1.waitlist.externalData.list returned no external data after set().');

        return $items[0];
    }

    /**
     * @param string[] $fieldCodes
     *
     * @return array<string, array{type: string}>
     */
    private function mapFieldTypes(array $fieldCodes): array
    {
        $unknownFieldCodes = array_values(array_diff($fieldCodes, array_keys(self::WAITLIST_EXTERNAL_DATA_FIELD_TYPES)));
        self::assertSame([], $unknownFieldCodes, 'Add type mappings for new WaitlistExternalData fields before asserting annotations.');

        return array_intersect_key(self::WAITLIST_EXTERNAL_DATA_FIELD_TYPES, array_flip($fieldCodes));
    }
}