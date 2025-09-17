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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\PersonType\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\Sale\PersonType\Service\PersonType;
use Bitrix24\SDK\Services\Sale\PersonType\Result\PersonTypeItemResult;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class PersonTypeTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Sale\PersonType\Service
 */
#[CoversMethod(PersonType::class,'add')]
#[CoversMethod(PersonType::class,'delete')]
#[CoversMethod(PersonType::class,'get')]
#[CoversMethod(PersonType::class,'list')]
#[CoversMethod(PersonType::class,'update')]
#[CoversMethod(PersonType::class,'getFields')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Sale\PersonType\Service\PersonType::class)]
class PersonTypeTest extends TestCase
{
    use CustomBitrix24Assertions;
    
    protected PersonType $personTypeService;
    
    protected function setUp(): void
    {
        $this->personTypeService = Fabric::getServiceBuilder()->getSaleScope()->personType();
    }

    public function testAllSystemFieldsAnnotated(): void
    {
        $fields = $this->personTypeService->getFields()->getFieldsDescription();
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($fields));
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, PersonTypeItemResult::class);
    }

    public function testAllSystemFieldsHasValidTypeAnnotation():void
    {
        $allFields = $this->personTypeService->getFields()->getFieldsDescription();
        $systemFieldsCodes = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter($allFields, static fn($code): bool => in_array($code, $systemFieldsCodes, true), ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $systemFields,
            PersonTypeItemResult::class);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testFields(): void
    {
        self::assertIsArray($this->personTypeService->getFields()->getFieldsDescription());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $itemId = $this->personTypeService->add(
            [
                'name' => 'Test person 1',
            ]
        )->getId();
        self::assertGreaterThan(1, $itemId);
        
        $this->personTypeService->delete($itemId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $itemId = $this->personTypeService->add(
            [
                'name' => 'Test person 2',
            ]
        )->getId();
        self::assertTrue($this->personTypeService->delete($itemId)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $itemId = $this->personTypeService->add(
            [
                'name' => 'Test person 3',
            ]
        )->getId();
        self::assertGreaterThan(
            1,
            $this->personTypeService->get($itemId)->PersonType()->id
        );
        
        $this->personTypeService->delete($itemId);
    }
    
    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetList(): void
    {
        $itemId = $this->personTypeService->add(
            [
                'name' => 'Test person 4',
            ]
        )->getId();
        $this->assertEquals(
            $itemId,
            $this->personTypeService->list(['id'], ['=id' => $itemId])->getPersonTypes()[0]->id
        );
        
        $this->personTypeService->delete($itemId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $itemId = $this->personTypeService->add(
            [
                'name' => 'Test person 5',
            ]
        )->getId();
        $newName = 'Updated person 5';

        self::assertTrue($this->personTypeService->update($itemId, ['name' => $newName])->isSuccess());
        self::assertEquals($newName, $this->personTypeService->get($itemId)->persontype()->name);
        
        $this->personTypeService->delete($itemId);
    }
    
}
