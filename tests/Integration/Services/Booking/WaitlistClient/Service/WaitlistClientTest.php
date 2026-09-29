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

namespace Bitrix24\SDK\Tests\Integration\Services\Booking\WaitlistClient\Service;

use Bitrix24\SDK\Services\Booking\WaitlistClient\Service\WaitlistClient;
use Bitrix24\SDK\Tests\Integration\Services\Booking\Support\BookingScopeTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversClass(WaitlistClient::class)]
#[CoversMethod(WaitlistClient::class, 'list')]
#[CoversMethod(WaitlistClient::class, 'set')]
#[CoversMethod(WaitlistClient::class, 'unset')]
class WaitlistClientTest extends BookingScopeTestCase
{
    private WaitlistClient $waitlistClientService;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->waitlistClientService = $this->serviceBuilder->getBookingScope()->waitlistClient();
    }

    public function testSetListUnset(): void
    {
        $clientTypeCodes = array_map(
            static fn(object $clientType): ?string => $clientType->code,
            $this->serviceBuilder->getBookingScope()->clientType()->list()->getClientTypes()
        );

        if (!in_array('CONTACT', $clientTypeCodes, true)) {
            self::markTestSkipped('Portal has no CONTACT booking client type configured.');
        }

        $waitlistId = $this->createWaitlist();
        $contactId = $this->createCrmContact();

        self::assertTrue($this->waitlistClientService->set($waitlistId, [[
            'id' => $contactId,
            'type' => [
                'module' => 'crm',
                'code' => 'CONTACT',
            ],
        ]])->isSuccess());

        $clients = $this->waitlistClientService->list($waitlistId)->getClients();
        self::assertCount(1, $clients);
        self::assertSame($contactId, $clients[0]->id);
        self::assertSame('crm', $clients[0]->type['module']);
        self::assertSame('CONTACT', $clients[0]->type['code']);

        self::assertTrue($this->waitlistClientService->unset($waitlistId)->isSuccess());
        self::assertCount(0, $this->waitlistClientService->list($waitlistId)->getClients());
    }
}