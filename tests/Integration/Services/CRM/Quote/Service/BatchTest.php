<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Quote\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Quote\Service\Quote;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\TestCase;

/**
 * Class BatchTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\Quote\Service
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Quote\Service\Batch::class)]
class BatchTest extends TestCase
{
    protected Quote $quoteService;
    
    
    protected function setUp(): void
    {
        $this->quoteService = Factory::getServiceBuilder()->getCRMScope()->quote();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch list quotes')]
    public function testBatchList(): void
    {
        $itemId = $this->quoteService->add(['TITLE' => 'test quote'])->getId();
        $cnt = 0;

        foreach ($this->quoteService->batch->list([], ['ID' => $itemId], ['ID', 'NAME'], 1) as $item) {
            $cnt++;
        }

        self::assertGreaterThanOrEqual(1, $cnt);

        $this->quoteService->delete($itemId);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch add quote')]
    public function testBatchAdd(): void
    {
        $items = [];
        for ($i = 1; $i < 60; $i++) {
            $items[] = ['TITLE' => 'TITLE-' . $i];
        }

        $cnt = 0;
        $itemId = [];
        foreach ($this->quoteService->batch->add($items) as $item) {
            $cnt++;
            $itemId[] = $item->getId();
        }

        self::assertEquals(count($items), $cnt);

        $cnt = 0;
        foreach ($this->quoteService->batch->delete($itemId) as $cnt => $deleteResult) {
            $cnt++;
        }

        self::assertEquals(count($items), $cnt);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch delete quotes')]
    public function testBatchDelete(): void
    {
        $quotes = [];
        for ($i = 1; $i < 60; $i++) {
            $quotes[] = ['TITLE' => 'TITLE-' . $i];
        }

        $cnt = 0;
        $itemId = [];
        foreach ($this->quoteService->batch->add($quotes) as $item) {
            $cnt++;
            $itemId[] = $item->getId();
        }

        self::assertEquals(count($quotes), $cnt);

        $cnt = 0;
        foreach ($this->quoteService->batch->delete($itemId) as $cnt => $deleteResult) {
            $cnt++;
        }

        self::assertEquals(count($quotes), $cnt);
    }

}
