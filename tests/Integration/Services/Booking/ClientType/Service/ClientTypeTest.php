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

namespace Bitrix24\SDK\Tests\Integration\Services\Booking\ClientType\Service;

use Bitrix24\SDK\Services\Booking\ClientType\Service\ClientType;
use Bitrix24\SDK\Tests\Integration\Services\Booking\Support\BookingScopeTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversClass(ClientType::class)]
#[CoversMethod(ClientType::class, 'list')]
class ClientTypeTest extends BookingScopeTestCase
{
    private ClientType $clientTypeService;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->clientTypeService = $this->serviceBuilder->getBookingScope()->clientType();
    }

    public function testList(): void
    {
        $clientTypes = $this->clientTypeService->list()->getClientTypes();

        self::assertIsArray($clientTypes);

        foreach ($clientTypes as $clientType) {
            self::assertNotEmpty($clientType->code);
            self::assertSame('crm', $clientType->module);
        }
    }
}