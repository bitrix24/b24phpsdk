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

namespace Bitrix24\SDK\Tests\Integration\Services\Task\Stage\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\Task\Stage\Service\Stage;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class StageTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Task\Stage\Service
 */
#[CoversMethod(Stage::class,'add')]
#[CoversMethod(Stage::class,'delete')]
#[CoversMethod(Stage::class,'get')]
#[CoversMethod(Stage::class,'update')]
#[CoversMethod(Stage::class,'canMoveTask')]
#[CoversMethod(Stage::class,'moveTask')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Task\Stage\Service\Stage::class)]
class StageTest extends TestCase
{
    use CustomBitrix24Assertions;
    
    protected Stage $stageService;
    
    protected int $userId = 0;
    
    protected int $afterStageId = 0;
    
    
    #[\Override]
    protected function setUp(): void
    {
        $this->stageService = Factory::getServiceBuilder()->getTaskScope()->stage();
        $this->userId = Factory::getServiceBuilder()->getUserScope()->user()->current()->user()->ID;
        $stages = $this->stageService->get(0)->getStages();
        $this->afterStageId = intval($stages[0]->ID);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $itemId = $this->getStageId('Test', $this->afterStageId);
        self::assertGreaterThanOrEqual(1, $itemId);
        $this->stageService->delete($itemId, true);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $itemId = $this->getStageId('Test', $this->afterStageId);
        self::assertTrue($this->stageService->delete($itemId, true)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        self::assertIsArray($this->stageService->get(0)->getStages());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $itemId = $this->getStageId('Test', $this->afterStageId);
        $newTitle = 'Test updated comment';

        self::assertTrue($this->stageService->update($itemId, ['TITLE' => $newTitle])->isSuccess());
        $stages = $this->stageService->get(0)->getStages();
        foreach ($stages as $stage) {
            if ($stage->ID == $itemId) {
                self::assertEquals($newTitle, $stage->TITLE);
                break;
            }
        }
        
        $this->stageService->delete($itemId, true);
    }
    
    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testCanMoveTask(): void
    {
        self::assertTrue($this->stageService->canMoveTask($this->userId, 'U')->isSuccess());
    }
    
    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testMoveTask(): void
    {
        $taskId = $this->getTaskId();
        $itemId = $this->getStageId('Test', $this->afterStageId);
        
        self::assertTrue($this->stageService->moveTask($taskId, $itemId)->isSuccess());
        self::assertTrue($this->stageService->moveTask($taskId, $this->afterStageId)->isSuccess());
        
        self::assertTrue(Factory::getServiceBuilder()->getTaskScope()->task()->delete($taskId)->isSuccess());
        $this->stageService->delete($itemId, true);
    }
    
    

    
    protected function getTaskId(string $title = 'Test task for stages'): int {
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
    
    protected function getStageId(string $title = 'Test stage', int $after = 0): int {
        $fields = [
            'TITLE' => $title,
        ];
        if ($after > 0) {
            $fields['AFTER_ID'] = $after;
        }
        
        return $this->stageService->add($fields)->getId();
    }
    
}
