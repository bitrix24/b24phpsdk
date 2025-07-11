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
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\CRM\Timeline\Bindings\Result\BindingItemResult;
use Bitrix24\SDK\Services\CRM\Timeline\Bindings\Service\Bindings;
use Bitrix24\SDK\Services\CRM\Timeline\Comment\Service\Comment;
use Bitrix24\SDK\Services\CRM\Company\Service\Company;
use Bitrix24\SDK\Tests\Builders\Services\CRM\CompanyBuilder;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class BindingsTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\Timeline\Bindings\Service
 */
#[CoversMethod(Bindings::class,'bind')]
#[CoversMethod(Bindings::class,'unbind')]
#[CoversMethod(Bindings::class,'list')]
#[CoversMethod(Bindings::class,'fields')]
#[CoversMethod(Bindings::class,'countByFilter')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Timeline\Bindings\Service\Bindings::class)]
class BindingsTest extends TestCase
{
    use CustomBitrix24Assertions;
    
    protected Bindings $bindingService;
    
    protected Comment $commentService;
    
    protected Company $companyService;
    
    protected int $companyOneId = 0;
    
    protected int $companyTwoId = 0;
    
    protected function setUp(): void
    {
        $this->bindingService = Fabric::getServiceBuilder()->getCRMScope()->timelineBindings();
        $this->commentService = Fabric::getServiceBuilder()->getCRMScope()->timelineComment();
        $this->companyService = Fabric::getServiceBuilder()->getCRMScope()->company();
        $this->companyOneId = $this->companyService->add((new CompanyBuilder())->build())->getId();
        $this->companyTwoId = $this->companyService->add((new CompanyBuilder())->build())->getId();
    }
    
    protected function tearDown(): void
    {
        $this->companyService->delete($this->companyOneId);
        $this->companyService->delete($this->companyTwoId);
    }

    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($this->bindingService->fields()->getFieldsDescription()));
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, BindingItemResult::class);
    }

    public function testAllSystemFieldsHasValidTypeAnnotation():void
    {
        $allFields = $this->bindingService->fields()->getFieldsDescription();
        $systemFieldsCodes = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter($allFields, static fn($code): bool => in_array($code, $systemFieldsCodes, true), ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $systemFields,
            BindingItemResult::class);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBind(): void
    {
        $newId = $this->getNewComment();
        
        self::assertTrue($this->bindingService->bind($newId, $this->companyTwoId, 'company')->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUnbind(): void
    {
        $newCommentId = $this->getNewComment();
        
        $this->bindingService->bind($newCommentId, $this->companyTwoId, 'company')->isSuccess();
        self::assertTrue($this->bindingService->unbind($newCommentId, $this->companyTwoId, 'company')->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testFields(): void
    {
        self::assertIsArray($this->bindingService->fields()->getFieldsDescription());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $newCommentId = $this->getNewComment();
        $this->bindingService->bind($newCommentId, $this->companyTwoId, 'company');
        
        $filter = [
            'OWNER_ID' => $newCommentId,
        ];
        self::assertGreaterThanOrEqual(1, $this->bindingService->list($filter)->getBindings());
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    public function testCountByFilter(): void
    {
        $newCommentId = $this->getNewComment();
        $filter = [
            'OWNER_ID' => $newCommentId,
        ];
        // Can be used only with required filtration by ENTITY_ID and ENTITY_TYPE
        $before = $this->bindingService->countByFilter($filter);

        $this->bindingService->bind($newCommentId, $this->companyTwoId, 'company');

        $after = $this->bindingService->countByFilter($filter);

        $this->assertEquals($before + 1, $after);
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
