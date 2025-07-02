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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Status\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Status\Service\Status;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\TestCase;

/**
 * Class BatchTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\Status\Service
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Status\Service\Batch::class)]
class BatchTest extends TestCase
{
    protected Status $statusService;
    
    
    protected function setUp(): void
    {
        $this->statusService = Fabric::getServiceBuilder()->getCRMScope()->status();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch list statuses')]
    public function testBatchList(): void
    {
        $newStatus = [
            'ENTITY_ID' => 'SOURCE',
            'STATUS_ID' => 'Test',
            'SORT' => 1,
            'NAME' => 'Test status',
        ];
        $newId = $this->statusService->add($newStatus)->getId();
        
        $cnt = 0;
        foreach ($this->statusService->batch->list([], ['ID' => $newId], ['ID', 'NAME'], 1) as $item) {
            $cnt++;
        }

        self::assertGreaterThanOrEqual(1, $cnt);

        $this->statusService->delete($newId);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch add status')]
    public function testBatchAdd(): void
    {
        $newStatus = [
            'ENTITY_ID' => 'SOURCE',
            'STATUS_ID' => 'Test',
            'SORT' => 1,
            'NAME' => 'Test status',
        ];
        $items = [];
        for ($i = 1; $i < 10; $i++) {
            $copy = $newStatus;
            $copy['STATUS_ID'] .= $i;
            $copy['SORT'] += $i;
            $copy['NAME'] .= ' '.$i;
            $items[] = $copy;
        }

        $cnt = 0;
        $itemId = [];
        foreach ($this->statusService->batch->add($items) as $item) {
            $cnt++;
            $itemId[] = $item->getId();
        }

        self::assertEquals(count($items), $cnt);

        $cnt = 0;
        foreach ($this->statusService->batch->delete($itemId) as $cnt => $deleteResult) {
            $cnt++;
        }

        self::assertEquals(count($items), $cnt);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch delete statuses')]
    public function testBatchDelete(): void
    {
        $newStatus = [
            'ENTITY_ID' => 'SOURCE',
            'STATUS_ID' => 'Test',
            'SORT' => 1,
            'NAME' => 'Test status',
        ];
        $items = [];
        for ($i = 1; $i < 10; $i++) {
            $copy = $newStatus;
            $copy['STATUS_ID'] .= $i;
            $copy['SORT'] += $i;
            $copy['NAME'] .= ' '.$i;
            $items[] = $copy;
        }

        $itemId = [];
        foreach ($this->statusService->batch->add($items) as $item) {
            $itemId[] = $item->getId();
        }

        $cnt = 0;
        foreach ($this->statusService->batch->delete($itemId) as $cnt => $deleteResult) {
            $cnt++;
        }

        self::assertEquals(count($items), $cnt);
    }
    
    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Exception
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch update statuses')]
    public function testBatchUpdate(): void
    {
        $newStatus = [
            'ENTITY_ID' => 'SOURCE',
            'STATUS_ID' => 'Test',
            'SORT' => 1,
            'NAME' => 'Test status',
        ];
        $items = [];
        for ($i = 1; $i < 10; $i++) {
            $copy = $newStatus;
            $copy['STATUS_ID'] .= $i;
            $copy['SORT'] += $i;
            $copy['NAME'] .= ' '.$i;
            $items[] = $copy;
        }

        $updateStatuses = [];
        foreach ($this->statusService->batch->add($items) as $item) {
            $id = $item->getId();
            $updateStatuses[$id] = [
                'fields' => ['NAME' => 'Updated '.$id]
            ];
        }
        
        foreach ($this->statusService->batch->update($updateStatuses) as $statusUpdateResult) {
            $this->assertTrue($statusUpdateResult->isSuccess());
        }
        
        $cnt = 0;
        foreach ($this->statusService->batch->delete(array_keys($updateStatuses)) as $cnt => $deleteResult) {
            $cnt++;
        }
    }

}
