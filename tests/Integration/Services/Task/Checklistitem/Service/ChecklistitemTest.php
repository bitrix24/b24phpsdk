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

namespace Bitrix24\SDK\Tests\Integration\Services\Task\Checklistitem\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\Task\Checklistitem\Service\Checklistitem;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class ChecklistitemTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Task\Checklistitem\Service
 */
#[CoversMethod(Checklistitem::class,'add')]
#[CoversMethod(Checklistitem::class,'delete')]
#[CoversMethod(Checklistitem::class,'get')]
#[CoversMethod(Checklistitem::class,'list')]
#[CoversMethod(Checklistitem::class,'update')]
#[CoversMethod(Checklistitem::class,'moveAfterItem')]
#[CoversMethod(Checklistitem::class,'complete')]
#[CoversMethod(Checklistitem::class,'renew')]
#[CoversMethod(Checklistitem::class,'isActionAllowed')]
#[CoversMethod(Checklistitem::class,'getManifest')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Task\Service\Checklistitem::class)]
class ChecklistitemTest extends TestCase
{
    use CustomBitrix24Assertions;
    
    protected Checklistitem $checklistitemService;
    
    protected int $itemId = 0;
    
    
    protected function setUp(): void
    {
        $this->checklistitemService = Fabric::getServiceBuilder()->getTaskScope()->checklistitem();
        $this->userService = Fabric::getServiceBuilder()->getUserScope()->user();
        $this->taskId = $this->getTaskId();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $itemId = $this->checklistitemService->add($this->taskId, 'Test checkbox')->getId();
        self::assertGreaterThan(1, $itemId);
        
        $this->checklistitemService->delete($itemId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $itemId = $this->checklistitemService->add($this->taskId, 'Test checkbox')->getId();
        self::assertTrue($this->checklistitemService->delete($itemId)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $itemId = $this->checklistitemService->add($this->taskId, 'Test checkbox')->getId();
        self::assertGreaterThan(
            1,
            $this->checklistitemService->get($this->taskId, $itemId)->checklistitem()->ID
        );
        
        $this->checklistitemService->delete($itemId);
    }
    
    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetList(): void
    {
        $itemId = $this->checklistitemService->add($this->taskId, 'Test checkbox', 10)->getId();
        $item2Id = $this->checklistitemService->add($this->taskId, 'Test 2 checkbox', 20)->getId();
        $this->assertEquals(
            $item2Id,
            $this->checklistitemService->list($this->taskId, ['SORT_INDEX'=> 'asc'])->getChecklistitems()[1]->ID
        );
        
        $this->checklistitemService->delete($item2Id);
        $this->checklistitemService->delete($itemId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $itemId = $this->checklistitemService->add($this->taskId, 'Test checkbox', 10)->getId();
        $newTitle = 'Test second checkbox';

        self::assertTrue($this->checklistitemService->update($this->taskId, $itemId, ['TITLE' => $newTitle])->isSuccess());
        self::assertEquals($newTitle, $this->checklistitemService->get($itemId)->checklistitem()->TITLE);
        
        $this->checklistitemService->delete($itemId);
    }

    
    
    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    public function testMoveAfterItem(): void
    {
        $itemId = $this->checklistitemService->add($this->taskId, 'Test checkbox', 10)->getId();
        $item2Id = $this->checklistitemService->add($this->taskId, 'Test 2 checkbox', 20)->getId();
        
        self::assertTrue($this->checklistitemService->addDependence($this->taskId, $itemId, $item2Id)->isSuccess());
        $this->assertEquals(
            $itemId,
            $this->checklistitemService->list($this->taskId, ['SORT_INDEX'=> 'asc'])->getChecklistitems()[1]->ID
        );
        
        $this->checklistitemService->delete($item2Id);
        $this->checklistitemService->delete($itemId);
    }
    
    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testComplete(): void
    {
        $itemId = $this->checklistitemService->add($this->taskId, 'Test checkbox', 10)->getId();

        self::assertTrue($this->checklistitemService->complete($this->taskId, $itemId)->isSuccess());
        self::assertEquals('Y', $this->checklistitemService->get($itemId)->checklistitem()->IS_COMPLETE);
        
        $this->checklistitemService->delete($itemId);
    }
    
    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testRenew(): void
    {
        $itemId = $this->checklistitemService->add($this->taskId, 'Test checkbox', 10)->getId();
        
        self::assertTrue($this->checklistitemService->complete($this->taskId, $itemId)->isSuccess());
        self::assertTrue($this->checklistitemService->renew($this->taskId, $itemId)->isSuccess());
        self::assertEquals('N', $this->checklistitemService->get($itemId)->checklistitem()->IS_COMPLETE);
        
        $this->checklistitemService->delete($itemId);
    }
    
    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testIsActionAllowed(): void
    {
        $itemId = $this->checklistitemService->add($this->taskId, 'Test checkbox', 10)->getId();
        /*
         * 1 - ACTION_TIME_ADD
         * 2 - ACTION_MODIFY
         * 3 - ACTION_REMOVE
         * 4 - ACTION_TOGGLE
         */
        
        for ($i=1;$i<5;$i++) {
            self::assertTrue($this->checklistitemService->isActionAllowed($this->taskId, $itemId, $i)->isSuccess());
        }
        
        $this->checklistitemService->delete($itemId);
    }
    
    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetManifest(): void
    {
        self::assertIsArray(
            $this->checklistitemService->getManifest()
        );
    }
    
    protected function getTaskId(string $title = 'Test task for checklists'): int {
        static $taskId;
        
        if (intval($taskId) > 0) {
            
            return $taskId;
        }
        
        $userId = Fabric::getServiceBuilder()->getUserScope()->user()->current()->user()->ID;
        $taskId = Fabric::getServiceBuilder()->getTaskScope()->task()->add(
            [
                'TITLE' => $title,
                'RESPONSIBLE_ID' => $userId,
            ]
        )->getId();
        
        return $taskId;
    }
}
