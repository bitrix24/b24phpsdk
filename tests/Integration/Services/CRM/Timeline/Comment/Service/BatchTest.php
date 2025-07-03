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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Timeline\Comment\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Timeline\Comment\Service\Comment;
use Bitrix24\SDK\Services\CRM\Company\Service\Company;
use Bitrix24\SDK\Tests\Builders\Services\CRM\CompanyBuilder;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\TestCase;

/**
 * Class BatchTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\Timeline\Comment\Service
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Timeline\Comment\Service\Batch::class)]
class BatchTest extends TestCase
{
    protected Comment $commentService;
    
    protected Company $companyService;
    
    protected int $companyId = 0;
    
    protected function setUp(): void
    {
        $this->commentService = Fabric::getServiceBuilder()->getCRMScope()->timelineComment();
        $this->companyService = Fabric::getServiceBuilder()->getCRMScope()->company();
        $this->companyId = $this->companyService->add((new CompanyBuilder())->build())->getId();
    }
    
    protected function tearDown(): void
    {
        $this->companyService->delete($this->companyId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch list comments')]
    public function testBatchList(): void
    {
        $newComment = [
            'ENTITY_ID' => $this->companyId,
            'ENTITY_TYPE' => 'company',
            'COMMENT' => 'Test timeline comments',
        ];
        $newId = $this->commentService->add($newComment)->getId();
        
        $cnt = 0;
        $filter = [
            'ID' => $newId,
            'ENTITY_ID' => $this->companyId,
            'ENTITY_TYPE' => 'company',
        ];
        foreach ($this->commentService->batch->list([], $filter, ['ID', 'COMMENT'], 1) as $item) {
            $cnt++;
        }

        self::assertGreaterThanOrEqual(1, $cnt);

        $this->commentService->delete($newId);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch add comment')]
    public function testBatchAdd(): void
    {
        $newComment = [
            'ENTITY_ID' => $this->companyId,
            'ENTITY_TYPE' => 'company',
            'COMMENT' => 'Test timeline comments',
        ];
        $items = [];
        for ($i = 1; $i < 10; $i++) {
            $copy = $newComment;
            $copy['COMMENT'] .= ' '.$i;
            $items[] = $copy;
        }

        $cnt = 0;
        $itemId = [];
        foreach ($this->commentService->batch->add($items) as $item) {
            $cnt++;
            $itemId[] = $item->getId();
        }

        self::assertEquals(count($items), $cnt);

        $cnt = 0;
        foreach ($this->commentService->batch->delete($itemId) as $cnt => $deleteResult) {
            $cnt++;
        }
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch delete comments')]
    public function testBatchDelete(): void
    {
        $newComment = [
            'ENTITY_ID' => $this->companyId,
            'ENTITY_TYPE' => 'company',
            'COMMENT' => 'Test timeline comments',
        ];
        $items = [];
        for ($i = 1; $i < 10; $i++) {
            $copy = $newComment;
            $copy['COMMENT'] .= ' '.$i;
            $items[] = $copy;
        }

        $cnt = 0;
        $itemId = [];
        foreach ($this->commentService->batch->add($items) as $item) {
            $cnt++;
            $itemId[] = $item->getId();
        }

        $cnt = 0;
        foreach ($this->commentService->batch->delete($itemId) as $cnt => $deleteResult) {
            $cnt++;
        }

        self::assertEquals(count($items), $cnt);
    }
    
    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Exception
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch update comments')]
    public function testBatchUpdate(): void
    {
        $newComment = [
            'ENTITY_ID' => $this->companyId,
            'ENTITY_TYPE' => 'company',
            'COMMENT' => 'Test timeline comments',
        ];
        $items = [];
        for ($i = 1; $i < 10; $i++) {
            $copy = $newComment;
            $copy['COMMENT'] .= ' '.$i;
            $items[] = $copy;
        }

        $updateComments = [];
        foreach ($this->commentService->batch->add($items) as $item) {
            $id = $item->getId();
            $updateComments[$id] = [
                'fields' => ['COMMENT' => 'Updated '.$id]
            ];
        }
        
        foreach ($this->commentService->batch->update($updateComments) as $updateResult) {
            $this->assertTrue($updateResult->isSuccess());
        }
        
        $cnt = 0;
        foreach ($this->commentService->batch->delete(array_keys($updateComments)) as $cnt => $deleteResult) {
            $cnt++;
        }
    }

}
