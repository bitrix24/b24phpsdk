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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Automation\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Automation\Service\Trigger;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\TestCase;

/**
 * Class BatchTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\Automation\Service
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Automation\Service\Batch::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Lead\Service\Batch::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Deal\Service\Batch::class)]
class BatchTest extends TestCase
{
    public const TRIGGER_CODE = 'b24phpsdk';
    
    protected Trigger $triggerService;

    protected function setUp(): void
    {
        $this->triggerService = Fabric::getServiceBuilder(true)->getCRMScope()->trigger();
    }
    
    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch list triggers')]
    public function testBatchList(): void
    {
        $this->triggerService->add(self::TRIGGER_CODE, 'B24phpsdk trigger');
        $cnt = 0;

        foreach ($this->triggerService->batch->list() as $item) {
            $cnt++;
        }

        self::assertGreaterThanOrEqual(1, $cnt);
        $this->triggerService->delete(self::TRIGGER_CODE);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch add lead')]
    public function testBatchAdd(): void
    {
        $items = [];
        $max = 5;
        for ($i = 0; $i < $max; $i++) {
            $items[] = ['CODE' => self::TRIGGER_CODE . $i, 'NAME' => 'B24phpsdk trigger ' . $i];
        }

        $cnt = 0;
        foreach ($this->triggerService->batch->add($items) as $item) {
            $cnt++;
        }

        self::assertEquals(count($items), $cnt);

        for ($i = 0; $i < $max; $i++) {
            $this->triggerService->delete(self::TRIGGER_CODE . $i);
        }
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch delete deals')]
    public function testBatchDelete(): void
    {
        $items = [];
        $max = 5;
        for ($i = 0; $i < $max; $i++) {
            $items[] = ['CODE' => self::TRIGGER_CODE . $i, 'NAME' => 'B24phpsdk trigger ' . $i];
        }

        $cnt = 0;
        foreach ($this->triggerService->batch->add($items) as $item) {
            $cnt++;
        }

        $items = [];
        for ($i = 0; $i < $max; $i++) {
            $items[] = self::TRIGGER_CODE . $i;
        }

        $cnt = 0;
        foreach ($this->triggerService->batch->delete($items) as $deleteResult) {
            $cnt++;
        }

        self::assertEquals(count($items), $cnt);
    }

}