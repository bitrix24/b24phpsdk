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

namespace Bitrix24\SDK\Tests\Integration\Services\Booking\WaitlistExternalData\Service;

use Bitrix24\SDK\Services\Booking\WaitlistExternalData\Service\WaitlistExternalData;
use Bitrix24\SDK\Tests\Integration\Services\Booking\Support\BookingScopeTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversClass(WaitlistExternalData::class)]
#[CoversMethod(WaitlistExternalData::class, 'list')]
#[CoversMethod(WaitlistExternalData::class, 'set')]
#[CoversMethod(WaitlistExternalData::class, 'unset')]
class WaitlistExternalDataTest extends BookingScopeTestCase
{
    private WaitlistExternalData $waitlistExternalDataService;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->waitlistExternalDataService = $this->serviceBuilder->getBookingScope()->waitlistExternalData();
    }

    public function testSetListUnset(): void
    {
        $waitlistId = $this->createWaitlist();
        $dealId = $this->createCrmDeal();

        self::assertTrue($this->waitlistExternalDataService->set($waitlistId, [[
            'moduleId' => 'crm',
            'entityTypeId' => 'DEAL',
            'value' => (string)$dealId,
        ]])->isSuccess());

        $externalData = $this->waitlistExternalDataService->list($waitlistId)->getExternalData();
        self::assertCount(1, $externalData);
        self::assertSame('crm', $externalData[0]->moduleId);
        self::assertSame('DEAL', $externalData[0]->entityTypeId);
        self::assertSame((string)$dealId, $externalData[0]->value);

        self::assertTrue($this->waitlistExternalDataService->unset($waitlistId)->isSuccess());
        self::assertCount(0, $this->waitlistExternalDataService->list($waitlistId)->getExternalData());
    }
}