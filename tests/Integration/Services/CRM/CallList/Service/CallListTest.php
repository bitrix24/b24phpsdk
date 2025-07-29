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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\CallList\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\CRM\CallList\Result\CallListItemResult;
use Bitrix24\SDK\Services\CRM\CallList\Service\CallList;
use Bitrix24\SDK\Services\CRM\Contact\Service\Contact;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class CallListTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\CallList\Service
 */
#[CoversMethod(CallList::class,'add')]
#[CoversMethod(CallList::class,'get')]
#[CoversMethod(CallList::class,'list')]
#[CoversMethod(CallList::class,'update')]
#[CoversMethod(CallList::class,'countByFilter')]
#[CoversMethod(CallList::class,'statusList')]
#[CoversMethod(CallList::class,'getItems')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\CallList\Service\CallList::class)]
class CallListTest extends TestCase
{
    use CustomBitrix24Assertions;
    
    protected CallList $callListService;
    
    protected array $contactIds = [];
    
    
    protected function setUp(): void
    {
        $this->callListService = Fabric::getServiceBuilder()->getCRMScope()->callList();
        $contacts = [
            ['NAME' => 'name-1'],
            ['NAME' => 'name-2'],
        ];
        foreach (Fabric::getServiceBuilder()->getCRMScope()->contact()->batch->add($contacts) as $item) {
            $this->contactIds[] = $item->getId();
        }
    }
    
    protected function tearDown(): void
    {
        foreach (Fabric::getServiceBuilder()->getCRMScope()->contact()->batch->delete($this->contactIds) as $item) {
            //
        }
    }

    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($this->callListService->fields()->getFieldsDescription()));
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, CallListItemResult::class);
    }

    /*
    ignore because the result has code => name pairs only
    public function testAllSystemFieldsHasValidTypeAnnotation():void
    {
        $allFields = $this->callListService->fields()->getFieldsDescription();
        $systemFieldsCodes = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter($allFields, static fn($code): bool => in_array($code, $systemFieldsCodes, true), ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $systemFields,
            CallListItemResult::class);
    }
    */

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $listId = $this->callListService->add('CONTACT', $this->contactIds)->getId();
        self::assertGreaterThan(1, $listId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $listId = $this->callListService->add('CONTACT', $this->contactIds)->getId();
        self::assertGreaterThan(
            1,
            $this->callListService->get($listId)->calllist()->ID
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $listId = $this->callListService->add('CONTACT', [ $this->contactIds[0] ])->getId();
        
        self::assertTrue($this->callListService->update($listId, 'CONTACT', $this->contactIds)->isSuccess());
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    public function testCountByFilter(): void
    {
        $before = $this->callListService->countByFilter();
        $listId = $this->callListService->add('CONTACT', [ $this->contactIds[0] ])->getId();
        $after = $this->callListService->countByFilter();
        $this->assertEquals($before + 1, $after);
    }
}
