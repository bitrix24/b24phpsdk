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

namespace Bitrix24\SDK\Tests\Integration\Services\Booking\ClientType\Service;

use Bitrix24\SDK\Services\Booking\ClientType\Service\Batch;
use Bitrix24\SDK\Services\Booking\ClientType\Service\ClientType;
use Bitrix24\SDK\Tests\Integration\Services\Booking\Support\BookingScopeTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(Batch::class)]
#[CoversMethod(Batch::class, 'list')]
class BatchTest extends BookingScopeTestCase
{
    private ClientType $clientTypeService;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->clientTypeService = $this->serviceBuilder->getBookingScope()->clientType();
    }

    #[TestDox('test Batch::list')]
    public function testList(): void
    {
        $expectedCodes = array_map(
            static fn(object $clientType): ?string => $clientType->code,
            $this->clientTypeService->list()->getClientTypes()
        );

        $batchCodes = [];
        foreach ($this->clientTypeService->batch->list() as $clientTypeItemResult) {
            $batchCodes[] = $clientTypeItemResult->code;
        }

        self::assertSame($expectedCodes, $batchCodes);
    }
}
