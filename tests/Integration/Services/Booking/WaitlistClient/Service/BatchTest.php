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

namespace Bitrix24\SDK\Tests\Integration\Services\Booking\WaitlistClient\Service;

use Bitrix24\SDK\Services\Booking\WaitlistClient\Service\Batch;
use Bitrix24\SDK\Services\Booking\WaitlistClient\Service\WaitlistClient;
use Bitrix24\SDK\Tests\Integration\Services\Booking\Support\BookingScopeTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(Batch::class)]
#[CoversMethod(Batch::class, 'set')]
#[CoversMethod(Batch::class, 'list')]
class BatchTest extends BookingScopeTestCase
{
    private WaitlistClient $waitlistClientService;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->waitlistClientService = $this->serviceBuilder->getBookingScope()->waitlistClient();
    }

    #[TestDox('test Batch::set, Batch::list')]
    public function testSetList(): void
    {
        $clientTypeCodes = array_map(
            static fn(object $clientType): ?string => $clientType->code,
            $this->serviceBuilder->getBookingScope()->clientType()->list()->getClientTypes()
        );

        if (!in_array('CONTACT', $clientTypeCodes, true)) {
            self::markTestSkipped('Portal has no CONTACT booking client type configured.');
        }

        $firstWaitlistId = $this->createWaitlist();
        $secondWaitlistId = $this->createWaitlist();
        $firstContactId = $this->createCrmContact();
        $secondContactId = $this->createCrmContact();

        $setPayload = [
            $firstWaitlistId => [['id' => $firstContactId, 'type' => ['module' => 'crm', 'code' => 'CONTACT']]],
            $secondWaitlistId => [['id' => $secondContactId, 'type' => ['module' => 'crm', 'code' => 'CONTACT']]],
        ];

        $setWaitlistIds = [];
        foreach ($this->waitlistClientService->batch->set($setPayload) as $waitListId => $updatedItemResult) {
            self::assertTrue($updatedItemResult->isSuccess());
            $setWaitlistIds[] = $waitListId;
        }

        self::assertSame([$firstWaitlistId, $secondWaitlistId], $setWaitlistIds);

        $clientIdsByWaitlistId = [];
        foreach ($this->waitlistClientService->batch->list([$firstWaitlistId, $secondWaitlistId]) as $waitListId => $clients) {
            $clientIdsByWaitlistId[$waitListId] = array_map(
                static fn(object $client): ?int => $client->id,
                $clients
            );
        }

        self::assertSame(
            [
                $firstWaitlistId => [$firstContactId],
                $secondWaitlistId => [$secondContactId],
            ],
            $clientIdsByWaitlistId
        );
    }
}
