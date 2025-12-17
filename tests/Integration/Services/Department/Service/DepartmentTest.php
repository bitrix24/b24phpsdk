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
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\Department\Result\DepartmentItemResult;
use Bitrix24\SDK\Services\Department\Service\Department;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class DepartmentTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Department\Service
 */
#[CoversMethod(Department::class,'add')]
#[CoversMethod(Department::class,'delete')]
#[CoversMethod(Department::class,'get')]
#[CoversMethod(Department::class,'list')]
#[CoversMethod(Department::class,'fields')]
#[CoversMethod(Department::class,'update')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Department\Service\Department::class)]
class DepartmentTest extends TestCase
{
    use CustomBitrix24Assertions;
    
    protected Department $departmentService;
    
    protected int $rootDepartmentId = 0;
    
    
    #[\Override]
    protected function setUp(): void
    {
        $this->departmentService = Factory::getServiceBuilder()->getDepartmentScope()->department();
        
        $this->rootDepartmentId = intval($this->departmentService->get(['PARENT' => 0])->getDepartments()[0]->ID);
    }

    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($this->departmentService->fields()->getFieldsDescription()));
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, DepartmentItemResult::class);
    }

    /*
    ignore because the result has code => name pairs only
    public function testAllSystemFieldsHasValidTypeAnnotation():void
    {
        $allFields = $this->departmentService->fields()->getFieldsDescription();
        $systemFieldsCodes = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter($allFields, static fn($code): bool => in_array($code, $systemFieldsCodes, true), ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $systemFields,
            DepartmentItemResult::class);
    }
    */

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $depId = $this->departmentService->add('Test depart', $this->rootDepartmentId)->getId();
        self::assertGreaterThan(1, $depId);
        
        $this->departmentService->delete($depId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        self::assertTrue($this->departmentService->delete($this->departmentService->add('Test depart', $this->rootDepartmentId)->getId())->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testFields(): void
    {
        self::assertIsArray($this->departmentService->fields()->getFieldsDescription());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $depId = $this->departmentService->add('Test depart', $this->rootDepartmentId)->getId();
        self::assertGreaterThan(
            1,
            $this->departmentService->get(['ID' => $depId])->getDepartments()[0]->ID
        );
        
        $this->departmentService->delete($depId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $depId = $this->departmentService->add('Test depart', $this->rootDepartmentId)->getId();
        $newName = 'Test2 dep';

        self::assertTrue($this->departmentService->update($depId, ['NAME' => $newName])->isSuccess());
        self::assertEquals($newName, $this->departmentService->get(['ID' => $depId])->getDepartments()[0]->NAME);
        
        $this->departmentService->delete($depId);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    public function testCountByFilter(): void
    {
        $before = $this->departmentService->countByFilter();
        $depId = $this->departmentService->add('Test depart', $this->rootDepartmentId)->getId();
        $after = $this->departmentService->countByFilter();
        $this->assertEquals($before + 1, $after);
        
        $this->departmentService->delete($depId);
    }
}
