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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Lead\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Lead\Service\Lead;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\TestCase;

/**
 * Class BatchTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\Lead\Service
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Lead\Service\Batch::class)]
class BatchTest extends TestCase
{
    protected Lead $leadService;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch list leads')]
    public function testBatchList(): void
    {
        $itemId = $this->leadService->add(['TITLE' => 'test lead'])->getId();
        $cnt = 0;

        foreach ($this->leadService->batch->list([], ['ID' => $itemId], ['ID', 'NAME'], 1) as $item) {
            $cnt++;
        }

        self::assertGreaterThanOrEqual(1, $cnt);

        $this->leadService->delete($itemId);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch add lead')]
    public function testBatchAdd(): void
    {
        $items = [];
        for ($i = 1; $i < 60; $i++) {
            $items[] = ['TITLE' => 'TITLE-' . $i];
        }

        $cnt = 0;
        $itemId = [];
        foreach ($this->leadService->batch->add($items) as $item) {
            $cnt++;
            $itemId[] = $item->getId();
        }

        self::assertEquals(count($items), $cnt);

        $cnt = 0;
        foreach ($this->leadService->batch->delete($itemId) as $cnt => $deleteResult) {
            $cnt++;
        }

        self::assertEquals(count($items), $cnt);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch delete leads')]
    public function testBatchDelete(): void
    {
        $leads = [];
        for ($i = 1; $i < 60; $i++) {
            $leads[] = ['TITLE' => 'TITLE-' . $i];
        }

        $cnt = 0;
        $dealId = [];
        foreach ($this->leadService->batch->add($leads) as $item) {
            $cnt++;
            $dealId[] = $item->getId();
        }

        self::assertEquals(count($leads), $cnt);

        $cnt = 0;
        foreach ($this->leadService->batch->delete($dealId) as $cnt => $deleteResult) {
            $cnt++;
        }

        self::assertEquals(count($leads), $cnt);
    }

    protected function setUp(): void
    {
        $this->leadService = Factory::getServiceBuilder()->getCRMScope()->lead();
    }
}