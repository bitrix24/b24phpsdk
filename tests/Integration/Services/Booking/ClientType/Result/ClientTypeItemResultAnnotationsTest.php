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

namespace Bitrix24\SDK\Tests\Integration\Services\Booking\ClientType\Result;

use Bitrix24\SDK\Services\Booking\ClientType\Result\ClientTypeItemResult;
use Bitrix24\SDK\Services\Booking\ClientType\Service\ClientType;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Services\Booking\Support\BookingScopeTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Depends;

#[CoversClass(ClientTypeItemResult::class)]
#[CoversMethod(ClientType::class, 'list')]
class ClientTypeItemResultAnnotationsTest extends BookingScopeTestCase
{
    use CustomBitrix24Assertions;

    /**
     * @var array<string, array{type: string}>
     */
    private const array CLIENT_TYPE_FIELD_TYPES = [
        'code' => ['type' => 'string'],
        'module' => ['type' => 'string'],
    ];

    private ClientType $clientTypeService;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->clientTypeService = $this->serviceBuilder->getBookingScope()->clientType();
    }

    /**
     * @return string[]
     */
    public function testAllSystemFieldsAnnotated(): array
    {
        $fieldCodes = array_keys($this->getClientTypePayload());

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            $fieldCodes,
            ClientTypeItemResult::class
        );

        return $fieldCodes;
    }

    #[Depends('testAllSystemFieldsAnnotated')]
    public function testAllSystemFieldsHasValidTypeAnnotation(array $fieldCodes): void
    {
        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $this->mapFieldTypes($fieldCodes),
            ClientTypeItemResult::class
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function getClientTypePayload(): array
    {
        $items = $this->clientTypeService
            ->list()
            ->getCoreResponse()
            ->getResponseData()
            ->getResult()['clientType'];

        if ($items === []) {
            self::markTestSkipped('Portal has no booking client types configured.');
        }

        return $items[0];
    }

    /**
     * @param string[] $fieldCodes
     *
     * @return array<string, array{type: string}>
     */
    private function mapFieldTypes(array $fieldCodes): array
    {
        $unknownFieldCodes = array_values(array_diff($fieldCodes, array_keys(self::CLIENT_TYPE_FIELD_TYPES)));
        self::assertSame([], $unknownFieldCodes, 'Add type mappings for new ClientType fields before asserting annotations.');

        return array_intersect_key(self::CLIENT_TYPE_FIELD_TYPES, array_flip($fieldCodes));
    }
}