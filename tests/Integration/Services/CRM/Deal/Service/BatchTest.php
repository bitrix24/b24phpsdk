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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Deal\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Deal\Service\Deal;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\TestCase;

/**
 * Class DealsTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\Deals\Service
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Contact\Service\Batch::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Deal\Service\Batch::class)]
class BatchTest extends TestCase
{
    protected Deal $dealService;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch list deals')]
    public function testBatchList(): void
    {
        $dealId = $this->dealService->add(['TITLE' => 'test deal'])->getId();
        $cnt = 0;

        foreach ($this->dealService->batch->list([], ['ID' => $dealId], ['ID', 'NAME'], 1) as $item) {
            $cnt++;
        }

        self::assertGreaterThanOrEqual(1, $cnt);

        $this->dealService->delete($dealId);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch add deals')]
    public function testBatchAdd(): void
    {
        $deals = [];
        for ($i = 1; $i < 60; $i++) {
            $deals[] = ['TITLE' => 'TITLE-' . $i];
        }

        $cnt = 0;
        $dealId = [];
        foreach ($this->dealService->batch->add($deals) as $item) {
            $cnt++;
            $dealId[] = $item->getId();
        }

        self::assertEquals(count($deals), $cnt);

        $cnt = 0;
        foreach ($this->dealService->batch->delete($dealId) as $cnt => $deleteResult) {
            $cnt++;
        }

        self::assertEquals(count($deals), $cnt);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch delete deals')]
    public function testBatchDelete(): void
    {
        $deals = [];
        for ($i = 1; $i < 60; $i++) {
            $deals[] = ['TITLE' => 'TITLE-' . $i];
        }

        $cnt = 0;
        $dealId = [];
        foreach ($this->dealService->batch->add($deals) as $item) {
            $cnt++;
            $dealId[] = $item->getId();
        }

        self::assertEquals(count($deals), $cnt);

        $cnt = 0;
        foreach ($this->dealService->batch->delete($dealId) as $cnt => $deleteResult) {
            $cnt++;
        }

        self::assertEquals(count($deals), $cnt);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Exception
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch delete deals')]
    public function testBatchUpdate(): void
    {
        // add deals
        $deals = [];
        for ($i = 1; $i < 60; $i++) {
            $deals[] = ['TITLE' => 'TITLE-' . $i];
        }

        $cnt = 0;
        $dealId = [];
        foreach ($this->dealService->batch->add($deals) as $item) {
            $cnt++;
            $dealId[] = $item->getId();
        }

        self::assertEquals(count($deals), $cnt);

        // read deals and prepare update information
        $dealsToUpdate = [];
        $resultDeals = [];
        foreach ($this->dealService->batch->list([], ['ID' => $dealId], ['ID', 'TITLE', 'OPPORTUNITY']) as $deal) {
            $dealOpportunity = random_int(100, 10000);
            $dealsToUpdate[$deal->ID] = [
                'fields' => [
                    'OPPORTUNITY' => $dealOpportunity,
                ],
                'params' => [],
            ];
            $resultDeals[$deal->ID] = $dealOpportunity;
        }

        // update deals
        foreach ($this->dealService->batch->update($dealsToUpdate) as $dealUpdateResult) {
            $this->assertTrue($dealUpdateResult->isSuccess());
        }

        // list deals
        $updateResult = [];
        foreach ($this->dealService->batch->list([], ['ID' => $dealId], ['ID', 'TITLE', 'OPPORTUNITY']) as $deal) {
            $updateResult[$deal->ID] = $deal->OPPORTUNITY;
        }

        $this->assertEquals($resultDeals, $updateResult);
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->dealService = Factory::getServiceBuilder()->getCRMScope()->deal();
    }
}