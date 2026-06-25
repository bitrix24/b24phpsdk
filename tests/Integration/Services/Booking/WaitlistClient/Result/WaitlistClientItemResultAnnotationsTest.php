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

namespace Bitrix24\SDK\Tests\Integration\Services\Booking\WaitlistClient\Result;

use Bitrix24\SDK\Services\Booking\WaitlistClient\Result\WaitlistClientItemResult;
use Bitrix24\SDK\Services\Booking\WaitlistClient\Service\WaitlistClient;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Services\Booking\Support\BookingScopeTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Depends;

#[CoversClass(WaitlistClientItemResult::class)]
#[CoversMethod(WaitlistClient::class, 'list')]
class WaitlistClientItemResultAnnotationsTest extends BookingScopeTestCase
{
    use CustomBitrix24Assertions;

    /**
     * @var array<string, array{type: string}>
     */
    private const array WAITLIST_CLIENT_FIELD_TYPES = [
        'id' => ['type' => 'int'],
        'type' => ['type' => 'array'],
    ];

    private WaitlistClient $waitlistClientService;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->waitlistClientService = $this->serviceBuilder->getBookingScope()->waitlistClient();
    }

    /**
     * @return string[]
     */
    public function testAllSystemFieldsAnnotated(): array
    {
        $waitlistId = $this->createWaitlist();
        $contactId = $this->createCrmContact();
        $fieldCodes = array_keys($this->getWaitlistClientPayload($waitlistId, $contactId));

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            $fieldCodes,
            WaitlistClientItemResult::class
        );

        return $fieldCodes;
    }

    #[Depends('testAllSystemFieldsAnnotated')]
    public function testAllSystemFieldsHasValidTypeAnnotation(array $fieldCodes): void
    {
        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $this->mapFieldTypes($fieldCodes),
            WaitlistClientItemResult::class
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function getWaitlistClientPayload(int $waitlistId, int $contactId): array
    {
        $this->ensureContactClientTypeIsAvailable();

        self::assertTrue($this->waitlistClientService->set($waitlistId, [[
            'id' => $contactId,
            'type' => [
                'module' => 'crm',
                'code' => 'CONTACT',
            ],
        ]])->isSuccess());

        $items = $this->waitlistClientService
            ->list($waitlistId)
            ->getCoreResponse()
            ->getResponseData()
            ->getResult()['waitListClient'];

        self::assertNotSame([], $items, 'booking.v1.waitlist.client.list returned no clients after set().');

        return $items[0];
    }

    private function ensureContactClientTypeIsAvailable(): void
    {
        $clientTypeCodes = array_map(
            static fn(object $clientType): ?string => $clientType->code,
            $this->serviceBuilder->getBookingScope()->clientType()->list()->getClientTypes()
        );

        if (!in_array('CONTACT', $clientTypeCodes, true)) {
            self::markTestSkipped('Portal has no CONTACT booking client type configured.');
        }
    }

    /**
     * @param string[] $fieldCodes
     *
     * @return array<string, array{type: string}>
     */
    private function mapFieldTypes(array $fieldCodes): array
    {
        $unknownFieldCodes = array_values(array_diff($fieldCodes, array_keys(self::WAITLIST_CLIENT_FIELD_TYPES)));
        self::assertSame([], $unknownFieldCodes, 'Add type mappings for new WaitlistClient fields before asserting annotations.');

        return array_intersect_key(self::WAITLIST_CLIENT_FIELD_TYPES, array_flip($fieldCodes));
    }
}