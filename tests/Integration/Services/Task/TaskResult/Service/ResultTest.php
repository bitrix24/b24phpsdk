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

namespace Bitrix24\SDK\Tests\Integration\Services\Task\TaskResult\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\Task\Commentitem\Service\Commentitem;
use Bitrix24\SDK\Services\Task\TaskResult\Service\Result;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class ResultTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Task\Result\Service
 */
#[CoversMethod(Result::class,'addFromComment')]
#[CoversMethod(Result::class,'deleteFromComment')]
#[CoversMethod(Result::class,'list')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Task\TaskResult\Service\Result::class)]
class ResultTest extends TestCase
{
    use CustomBitrix24Assertions;
    
    protected Commentitem $commentitemService;
    
    protected Result $resultService;
    
    protected int $taskId = 0;
    
    protected int $userId = 0;
    
    protected function setUp(): void
    {
        $this->commentitemService = Factory::getServiceBuilder()->getTaskScope()->commentitem();
        $this->resultService = Factory::getServiceBuilder()->getTaskScope()->result();
        $this->userId = Factory::getServiceBuilder()->getUserScope()->user()->current()->user()->ID;
        $this->taskId = $this->getTaskId();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAddFromComment(): void
    {
        $commentId = $this->commentitemService->add(
            $this->taskId,
            [
                'POST_MESSAGE' => 'Test comment 1',
                'AUTHOR_ID' => $this->userId,
            ]
        )->getId();
        $itemId = $this->resultService->addFromComment(
            $commentId
        )->getId();
        self::assertGreaterThan(1, $itemId);
        
        $this->resultService->deleteFromComment($commentId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDeleteFromComment(): void
    {
        $commentId = $this->commentitemService->add(
            $this->taskId,
            [
                'POST_MESSAGE' => 'Test comment 1',
                'AUTHOR_ID' => $this->userId,
            ]
        )->getId();
        $this->resultService->addFromComment(
            $commentId
        )->getId();
        self::assertTrue($this->resultService->deleteFromComment($commentId)->isSuccess());
    }
    
    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $commentId = $this->commentitemService->add(
            $this->taskId,
            [
                'POST_MESSAGE' => 'Test comment 1',
                'AUTHOR_ID' => $this->userId,
            ]
        )->getId();
        $comment2Id = $this->commentitemService->add(
            $this->taskId,
            [
                'POST_MESSAGE' => 'Test comment 2',
                'AUTHOR_ID' => $this->userId,
            ]
        )->getId();
        $this->resultService->addFromComment(
            $commentId
        )->getId();
        $item2Id = $this->resultService->addFromComment(
            $comment2Id
        )->getId();
        $this->assertEquals(
            $item2Id,
            $this->resultService->list($this->taskId)->getResults()[0]->id
        );
        
        $this->resultService->deleteFromComment($commentId);
        $this->resultService->deleteFromComment($comment2Id);
    }
    
    protected function getTaskId(string $title = 'Test task for task results'): int {
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
