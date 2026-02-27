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
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\CRM\Timeline\Comment\Result\CommentItemResult;
use Bitrix24\SDK\Services\CRM\Timeline\Comment\Service\Comment;
use Bitrix24\SDK\Services\CRM\Company\Service\Company;
use Bitrix24\SDK\Tests\Builders\Services\CRM\CompanyBuilder;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class CommentTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\Timeline\Comment\Service
 */
#[CoversMethod(Comment::class,'add')]
#[CoversMethod(Comment::class,'delete')]
#[CoversMethod(Comment::class,'get')]
#[CoversMethod(Comment::class,'list')]
#[CoversMethod(Comment::class,'fields')]
#[CoversMethod(Comment::class,'update')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Timeline\Comment\Service\Comment::class)]
class CommentTest extends TestCase
{
    use CustomBitrix24Assertions;
    
    protected Comment $commentService;
    
    protected Company $companyService;
    
    protected int $companyId = 0;
    
    #[\Override]
    protected function setUp(): void
    {
        $this->commentService = Factory::getServiceBuilder()->getCRMScope()->timelineComment();
        $this->companyService = Factory::getServiceBuilder()->getCRMScope()->company();
        $this->companyId = $this->companyService->add((new CompanyBuilder())->build())->getId();
    }
    
    #[\Override]
    protected function tearDown(): void
    {
        $this->companyService->delete($this->companyId);
    }

    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($this->commentService->fields()->getFieldsDescription()));
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, CommentItemResult::class);
    }

    public function testAllSystemFieldsHasValidTypeAnnotation():void
    {
        $allFields = $this->commentService->fields()->getFieldsDescription();
        $systemFieldsCodes = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter($allFields, static fn($code): bool => in_array($code, $systemFieldsCodes, true), ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $systemFields,
            CommentItemResult::class);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $newComment = [
            'ENTITY_ID' => $this->companyId,
            'ENTITY_TYPE' => 'company',
            'COMMENT' => 'Test timeline comments',
        ];
        $newId = $this->commentService->add($newComment)->getId();
        self::assertGreaterThan(1, $newId);
        $this->commentService->delete($newId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $newComment = [
            'ENTITY_ID' => $this->companyId,
            'ENTITY_TYPE' => 'company',
            'COMMENT' => 'Test timeline comments',
        ];
        $newId = $this->commentService->add($newComment)->getId();
        $res = $this->commentService->delete($newId)->getCoreResponse()->getResponseData()->getResult()[0];
        // always returns result => null
        self::assertNull($res);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testFields(): void
    {
        self::assertIsArray($this->commentService->fields()->getFieldsDescription());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $newComment = [
            'ENTITY_ID' => $this->companyId,
            'ENTITY_TYPE' => 'company',
            'COMMENT' => 'Test timeline comments',
        ];
        $newId = $this->commentService->add($newComment)->getId();
        self::assertGreaterThan(
            1,
            $this->commentService->get($newId)->comment()->ID
        );
        $this->commentService->delete($newId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $newComment = [
            'ENTITY_ID' => $this->companyId,
            'ENTITY_TYPE' => 'company',
            'COMMENT' => 'Test timeline comments',
        ];
        $newId = $this->commentService->add($newComment)->getId();
        $filter = [
            'ENTITY_ID' => $this->companyId,
            'ENTITY_TYPE' => 'company',
        ];
        $comments = $this->commentService->list([], $filter, ['ID', 'COMMENT'])->getComments();
        self::assertGreaterThanOrEqual(1, $comments);
        $this->commentService->delete($newId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $newComment = [
            'ENTITY_ID' => $this->companyId,
            'ENTITY_TYPE' => 'company',
            'COMMENT' => 'Test timeline comments',
        ];
        $newId = $this->commentService->add($newComment)->getId();
        $newText = 'Test 2 timeline comments';

        self::assertTrue($this->commentService->update($newId, ['COMMENT' => $newText])->isSuccess());
        self::assertEquals($newText, $this->commentService->get($newId)->comment()->COMMENT);
        $this->commentService->delete($newId);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    public function testCountByFilter(): void
    {
        $filter = [
            'ENTITY_ID' => $this->companyId,
            'ENTITY_TYPE' => 'company',
        ];
        // Can be used only with required filtration by ENTITY_ID and ENTITY_TYPE
        $before = $this->commentService->countByFilter($filter);

        $newComment = [
            'ENTITY_ID' => $this->companyId,
            'ENTITY_TYPE' => 'company',
            'COMMENT' => 'Test timeline comments',
        ];
        $newId = $this->commentService->add($newComment)->getId();

        $after = $this->commentService->countByFilter($filter);

        $this->assertEquals($before + 1, $after);
        $this->commentService->delete($newId);
    }
}
