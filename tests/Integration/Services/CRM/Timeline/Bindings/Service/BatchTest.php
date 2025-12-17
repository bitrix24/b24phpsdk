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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Timeline\Bindings\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Timeline\Comment\Service\Comment;
use Bitrix24\SDK\Services\CRM\Timeline\Bindings\Service\Bindings;
use Bitrix24\SDK\Services\CRM\Company\Service\Company;
use Bitrix24\SDK\Tests\Builders\Services\CRM\CompanyBuilder;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\TestCase;

/**
 * Class BatchTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\Timeline\Comment\Service
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Timeline\Bindings\Service\Batch::class)]
class BatchTest extends TestCase
{
    protected Bindings $bindingService;
    
    protected Comment $commentService;
    
    protected Company $companyService;
    
    protected int $companyOneId = 0;
    
    protected int $companyTwoId = 0;
    
    #[\Override]
    protected function setUp(): void
    {
        $this->bindingService = Factory::getServiceBuilder()->getCRMScope()->timelineBindings();
        $this->commentService = Factory::getServiceBuilder()->getCRMScope()->timelineComment();
        $this->companyService = Factory::getServiceBuilder()->getCRMScope()->company();
        $this->companyOneId = $this->companyService->add((new CompanyBuilder())->build())->getId();
        $this->companyTwoId = $this->companyService->add((new CompanyBuilder())->build())->getId();
    }
    
    #[\Override]
    protected function tearDown(): void
    {
        $this->companyService->delete($this->companyOneId);
        $this->companyService->delete($this->companyTwoId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch list bindings')]
    public function testBatchList(): void
    {
        $newCommentId = $this->getNewComment();
        $this->bindingService->bind($newCommentId, $this->companyTwoId, 'company');
        
        $filter = [
            'OWNER_ID' => $newCommentId,
        ];
        
        $cnt = 0;
        foreach ($this->bindingService->batch->list($filter) as $item) {
            $cnt++;
        }

        self::assertGreaterThanOrEqual(1, $cnt);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch add bindings')]
    public function testBatchBind(): void
    {
        $newCommentId = $this->getNewComment();
        $items = [
            [
                'OWNER_ID' => $newCommentId,
                'ENTITY_ID' => $this->companyTwoId,
                'ENTITY_TYPE' => 'company'
            ]
        ];

        $cnt = 0;
        foreach ($this->bindingService->batch->bind($items) as $item) {
            $cnt++;
        }

        self::assertEquals(count($items), $cnt);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch delete comments')]
    public function testBatchUnbind(): void
    {
        $newCommentId = $this->getNewComment();
        $items = [
            [
                'OWNER_ID' => $newCommentId,
                'ENTITY_ID' => $this->companyTwoId,
                'ENTITY_TYPE' => 'company'
            ]
        ];

        $cnt = 0;
        foreach ($this->bindingService->batch->bind($items) as $item) {
            $cnt++;
        }

        $cnt = 0;
        foreach ($this->bindingService->batch->unbind($items) as $cnt => $deleteResult) {
            $cnt++;
        }

        self::assertEquals(count($items), $cnt);
    }
    
    private function getNewComment(): int {
        $newComment = [
            'ENTITY_ID' => $this->companyOneId,
            'ENTITY_TYPE' => 'company',
            'COMMENT' => 'Test timeline comments',
        ];
        
        return $this->commentService->add($newComment)->getId();
    }

}
