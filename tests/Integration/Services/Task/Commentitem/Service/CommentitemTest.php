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

namespace Bitrix24\SDK\Tests\Integration\Services\Task\Commentitem\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\Task\Commentitem\Service\Commentitem;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class CommentitemTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Task\Commentitem\Service
 */
#[CoversMethod(Commentitem::class,'add')]
#[CoversMethod(Commentitem::class,'delete')]
#[CoversMethod(Commentitem::class,'get')]
#[CoversMethod(Commentitem::class,'list')]
#[CoversMethod(Commentitem::class,'update')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Task\Commentitem\Service\Commentitem::class)]
class CommentitemTest extends TestCase
{
    use CustomBitrix24Assertions;
    
    protected Commentitem $commentitemService;
    
    protected int $taskId = 0;
    
    protected int $userId = 0;
    
    
    protected function setUp(): void
    {
        $this->commentitemService = Factory::getServiceBuilder()->getTaskScope()->commentitem();
        $this->userId = Factory::getServiceBuilder()->getUserScope()->user()->current()->user()->ID;
        $this->taskId = $this->getTaskId();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $itemId = $this->commentitemService->add(
            $this->taskId,
            [
                'POST_MESSAGE' => 'Test comment 1',
                'AUTHOR_ID' => $this->userId,
            ]
        )->getId();
        self::assertGreaterThan(1, $itemId);
        
        $this->commentitemService->delete($this->taskId, $itemId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $itemId = $this->commentitemService->add(
            $this->taskId,
            [
                'POST_MESSAGE' => 'Test comment 1',
                'AUTHOR_ID' => $this->userId,
            ]
        )->getId();
        self::assertTrue($this->commentitemService->delete($this->taskId, $itemId)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $itemId = $this->commentitemService->add(
            $this->taskId,
            [
                'POST_MESSAGE' => 'Test comment 1',
                'AUTHOR_ID' => $this->userId,
            ]
        )->getId();
        self::assertGreaterThan(
            1,
            $this->commentitemService->get($this->taskId, $itemId)->commentitem()->ID
        );
        
        $this->commentitemService->delete($this->taskId, $itemId);
    }
    
    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetList(): void
    {
        $itemId = $this->commentitemService->add(
            $this->taskId,
            [
                'POST_MESSAGE' => 'Test comment 1',
                'AUTHOR_ID' => $this->userId,
            ]
        )->getId();
        $item2Id = $this->commentitemService->add(
            $this->taskId,
            [
                'POST_MESSAGE' => 'Test comment 2',
                'AUTHOR_ID' => $this->userId,
            ]
        )->getId();
        $this->assertEquals(
            $item2Id,
            $this->commentitemService->getList($this->taskId, ['ID'=> 'asc'])->getCommentitems()[1]->ID
        );
        
        $this->commentitemService->delete($this->taskId, $item2Id);
        $this->commentitemService->delete($this->taskId, $itemId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $itemId = $this->commentitemService->add(
            $this->taskId,
            [
                'POST_MESSAGE' => 'Test comment 1',
                'AUTHOR_ID' => $this->userId,
            ]
        )->getId();
        $newMessage = 'Test updated comment';

        self::assertTrue($this->commentitemService->update($this->taskId, $itemId, ['POST_MESSAGE' => $newMessage])->isSuccess());
        self::assertEquals($newMessage, $this->commentitemService->get($this->taskId, $itemId)->commentitem()->POST_MESSAGE);
        
        $this->commentitemService->delete($this->taskId, $itemId);
    }
    
    protected function getTaskId(string $title = 'Test task for checklists'): int {
        static $taskId;
        
        if (intval($taskId) > 0) {
            
            return $taskId;
        }
        
        $taskId = Factory::getServiceBuilder()->getTaskScope()->task()->add(
            [
                'TITLE' => $title,
                'RESPONSIBLE_ID' => $this->userId,
            ]
        )->getId();
        
        return $taskId;
    }
}
