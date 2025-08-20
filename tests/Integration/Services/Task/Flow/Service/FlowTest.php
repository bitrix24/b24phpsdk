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

namespace Bitrix24\SDK\Tests\Integration\Services\Task\Flow\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\Task\Flow\Service\Flow;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class FlowTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Task\Flow\Service
 */
#[CoversMethod(Flow::class,'create')]
#[CoversMethod(Flow::class,'update')]
#[CoversMethod(Flow::class,'delete')]
#[CoversMethod(Flow::class,'get')]
#[CoversMethod(Flow::class,'isExists')]
#[CoversMethod(Flow::class,'activate')]
#[CoversMethod(Flow::class,'pin')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Task\Flow\Service\Flow::class)]
class FlowTest extends TestCase
{
    use CustomBitrix24Assertions;
    
    protected Flow $flowService;
    
    protected int $userId = 0;
    
    protected function setUp(): void
    {
        $this->flowService = Fabric::getServiceBuilder()->getTaskScope()->flow();
        $this->userId = Fabric::getServiceBuilder()->getUserScope()->user()->current()->user()->ID;
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testCreate(): void
    {
        $name = 'Test flow 1';
        $flowId = $this->getFlowId($name);
        self::assertGreaterThan(0, $flowId);
        
        $flowData = [
            'id' => $flowId,
        ];
        $this->flowService->delete($flowData);
        $this->deleteGroupByName($name);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $name = 'Test flow 2';
        $flowId = $this->getFlowId($name);
        $flowData = [
            'id' => $flowId,
        ];
        self::assertTrue($this->flowService->delete($flowData)->isSuccess());
        $this->deleteGroupByName($name);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $name = 'Test flow 3';
        $flowId = $this->getFlowId($name);
        self::assertGreaterThan(
            0,
            $this->flowService->get($flowId)->flow()->id
        );
        
        $flowData = [
            'id' => $flowId,
        ];
        $this->flowService->delete($flowData);
        $this->deleteGroupByName($name);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $name = 'Test flow 4-0';
        $flowId = $this->getFlowId($name);
        $name2 = 'Test2 flow 4-1';
        $flowData = [
            'id' => $flowId,
            'name' => $name2,
        ];

        self::assertTrue($this->flowService->update($flowData)->isSuccess());
        self::assertEquals($flowData['name'], $this->flowService->get($flowId)->flow()->name);
        
        $this->flowService->delete($flowData);
        $this->deleteGroupByName($name);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    public function testIsExists(): void
    {
        $name = 'Test flow for search 5';
        $flowId = $this->getFlowId($name);
        $flowData = [
            'name' => $name,
        ];
        
        self::assertTrue($this->flowService->isExists($flowData)->isSuccess());
        
        $flowData = [
            'id' => $flowId,
        ];
        $this->flowService->delete($flowData);
        $this->deleteGroupByName($name);
    }
    
    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    public function testActivate(): void
    {
        $name = 'Test flow 6';
        $flowId = $this->getFlowId($name);
        self::assertTrue($this->flowService->activate($flowId)->isSuccess());
        
        $flowData = [
            'id' => $flowId,
        ];
        $this->flowService->delete($flowData);
        $this->deleteGroupByName($name);
    }
    
    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    public function testPin(): void
    {
        $name = 'Test flow 7';
        $flowId = $this->getFlowId($name);
        self::assertTrue($this->flowService->pin($flowId)->isSuccess());
        
        $flowData = [
            'id' => $flowId,
        ];
        $this->flowService->delete($flowData);
        $this->deleteGroupByName($name);
    }
    
    protected function deleteGroupByName($name): void {
        $core = Fabric::getCore();
        $res = $core->call(
            'sonet_group.get',
            [
                'FILTER' => [
                    '=NAME' => $name
                ]
            ]
        )->getResponseData()->getResult();
        
        if (!empty($res[0]['ID'])) {
            $core->call(
                'sonet_group.delete',
                [
                    'GROUP_ID' => $res[0]['ID'],
                ]
            );
        }
    }
    
    protected function getFlowId(string $name = 'Test flow'): int {
        return $this->flowService->create(
            [
                'name' => $name,
                'plannedCompletionTime' => 8400,
                'distributionType' => 'manually',
                'responsibleList' => [
                    [ 
                        'user',
                        $this->userId
                    ],
                ]
            ]
        )->getId();
    }
}
