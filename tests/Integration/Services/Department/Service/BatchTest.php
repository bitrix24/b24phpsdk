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

namespace Bitrix24\SDK\Tests\Integration\Services\Department\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Department\Service\Department;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\TestCase;

/**
 * Class BatchTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Department\Service
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Department\Service\Batch::class)]
class BatchTest extends TestCase
{
    protected Department $departmentService;
    
    protected int $rootDepartmentId = 0;
    
    
    protected function setUp(): void
    {
        $this->departmentService = Factory::getServiceBuilder()->getDepartmentScope()->department();
        $dep = $this->departmentService->get(['PARENT' => 0])->getDepartments()[0];
        
        $this->rootDepartmentId = intval($dep->ID);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch get departments')]
    public function testBatchGet(): void
    {
        $depId = $this->departmentService->add('Test depart', $this->rootDepartmentId)->getId();
        $cnt = 0;
        foreach ($this->departmentService->batch->get(['ID' => $depId]) as $item) {
            $cnt++;
        }

        self::assertGreaterThanOrEqual(1, $cnt);

        $this->departmentService->delete($depId);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch add department')]
    public function testBatchAdd(): void
    {
        $items = [];
        for ($i = 1; $i < 60; $i++) {
            $items[] = [
                'NAME' => 'Dep-' . $i,
                'PARENT' => $this->rootDepartmentId
            ];
        }

        $cnt = 0;
        $depId = [];
        foreach ($this->departmentService->batch->add($items) as $item) {
            $cnt++;
            $depId[] = $item->getId();
        }

        self::assertEquals(count($items), $cnt);

        $cnt = 0;
        foreach ($this->departmentService->batch->delete($depId) as $cnt => $deleteResult) {
            $cnt++;
        }
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch delete departments')]
    public function testBatchDelete(): void
    {
        $items = [];
        for ($i = 1; $i < 60; $i++) {
            $items[] = [
                'NAME' => 'Dep-' . $i,
                'PARENT' => $this->rootDepartmentId
            ];
        }

        $cnt = 0;
        $depId = [];
        foreach ($this->departmentService->batch->add($items) as $item) {
            $cnt++;
            $depId[] = $item->getId();
        }

        $cnt = 0;
        foreach ($this->departmentService->batch->delete($depId) as $cnt => $deleteResult) {
            $cnt++;
        }

        self::assertEquals(count($items), $cnt);
    }
    
    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch update departments')]
    public function testBatchUpdate(): void
    {
        $items = [];
        for ($i = 1; $i < 60; $i++) {
            $items[] = [
                'NAME' => 'Dep-' . $i,
                'PARENT' => $this->rootDepartmentId
            ];
        }

        $cnt = 0;
        $depIds = [];
        foreach ($this->departmentService->batch->add($items) as $item) {
            $cnt++;
            $depIds[] = $item->getId();
        }
        
        $updates = [];
        foreach ($depIds as $depId) {
            $updates[$depId] = [
                'NAME' => 'Updated '.$depId,
            ];
        }

        $cnt = 0;
        foreach ($this->departmentService->batch->update($updates) as $cnt => $updateResult) {
            $cnt++;
            self::assertTrue($updateResult->isSuccess());
        }

        self::assertEquals(count($updates), $cnt);
        
        $cnt = 0;
        foreach ($this->departmentService->batch->delete($depIds) as $cnt => $deleteResult) {
            $cnt++;
        }

        self::assertEquals(count($items), $cnt);
    }

}
