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

namespace Bitrix24\SDK\Tests\Integration\Services\Task\Elapseditem\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\Task\Elapseditem\Service\Elapseditem;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class ElapseditemTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Task\Elapseditem\Service
 */
#[CoversMethod(Elapseditem::class,'add')]
#[CoversMethod(Elapseditem::class,'delete')]
#[CoversMethod(Elapseditem::class,'get')]
#[CoversMethod(Elapseditem::class,'getList')]
#[CoversMethod(Elapseditem::class,'update')]
#[CoversMethod(Elapseditem::class,'isActionAllowed')]
#[CoversMethod(Elapseditem::class,'getManifest')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Task\Elapseditem\Service\Elapseditem::class)]
class ElapseditemTest extends TestCase
{
    use CustomBitrix24Assertions;
    
    protected Elapseditem $elapseditemService;
    
    protected int $taskId = 0;
    
    
    protected function setUp(): void
    {
        $this->elapseditemService = Fabric::getServiceBuilder()->getTaskScope()->elapseditem();
        $this->taskId = $this->getTaskId();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $seconds = 3600;
        $commentText = 'Test elapsed time';
        $itemId = $this->elapseditemService->add($this->taskId, $seconds, $commentText)->getId();
        self::assertGreaterThanOrEqual(1, $itemId);
        
        $this->elapseditemService->delete($this->taskId, $itemId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $seconds = 3600;
        $commentText = 'Test elapsed time';
        $itemId = $this->elapseditemService->add($this->taskId, $seconds, $commentText)->getId();
        
        self::assertTrue($this->elapseditemService->delete($this->taskId, $itemId)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $seconds = 3600;
        $commentText = 'Test elapsed time';
        $itemId = $this->elapseditemService->add($this->taskId, $seconds, $commentText)->getId();
        
        $this->assertEquals(
            $itemId,
            $this->elapseditemService->get($this->taskId, $itemId)->elapseditem()->ID
        );
        
        $this->elapseditemService->delete($this->taskId, $itemId);
    }
    
    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetList(): void
    {
        $seconds = 3600;
        $commentText = 'Test elapsed time';
        $itemId = $this->elapseditemService->add($this->taskId, $seconds, $commentText)->getId();
        $seconds = 7200;
        $commentText = 'Test elapsed time 2';
        $item2Id = $this->elapseditemService->add($this->taskId, $seconds, $commentText)->getId();
        $this->assertEquals(
            $item2Id,
            $this->elapseditemService->getList($this->taskId, ['ID'=> 'asc'])->getElapseditems()[1]->ID
        );
        
        $this->elapseditemService->delete($this->taskId, $item2Id);
        $this->elapseditemService->delete($this->taskId, $itemId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $seconds = 3600;
        $commentText = 'Test elapsed time';
        $itemId = $this->elapseditemService->add($this->taskId, $seconds, $commentText)->getId();
        $newText = 'Test second checkbox';

        self::assertTrue($this->elapseditemService->update($this->taskId, $itemId, ['COMMENT_TEXT' => $newText])->isSuccess());
        self::assertEquals($newText, $this->elapseditemService->get($this->taskId, $itemId)->elapseditem()->COMMENT_TEXT);
        
        $this->elapseditemService->delete($this->taskId, $itemId);
    }
    
    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testIsActionAllowed(): void
    {
        $seconds = 3600;
        $commentText = 'Test elapsed time';
        $itemId = $this->elapseditemService->add($this->taskId, $seconds, $commentText)->getId();
        /*
         * 1 - add a new record (ACTION_ELAPSED_TIME_ADD)
         * 2 - modify a record (ACTION_ELAPSED_TIME_MODIFY)
         * 3 - delete a record (ACTION_ELAPSED_TIME_REMOVE)
         */
        
        for ($i=1;$i<4;$i++) {
            self::assertTrue($this->elapseditemService->isActionAllowed($this->taskId, $itemId, $i)->isSuccess());
        }
        
        $this->elapseditemService->delete($this->taskId, $itemId);
    }
    
    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetManifest(): void
    {
        self::assertIsArray(
            $this->elapseditemService->getManifest()
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
