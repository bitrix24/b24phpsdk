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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Status\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\CRM\Status\Result\StatusItemResult;
use Bitrix24\SDK\Services\CRM\Status\Service\Status;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class StatusTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\Status\Service
 */
#[CoversMethod(Status::class,'add')]
#[CoversMethod(Status::class,'delete')]
#[CoversMethod(Status::class,'get')]
#[CoversMethod(Status::class,'list')]
#[CoversMethod(Status::class,'fields')]
#[CoversMethod(Status::class,'update')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Status\Service\Status::class)]
class StatusTest extends TestCase
{
    use CustomBitrix24Assertions;
    protected Status $statusService;
    
    protected function setUp(): void
    {
        $this->statusService = Factory::getServiceBuilder()->getCRMScope()->status();
    }

    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($this->statusService->fields()->getFieldsDescription()));
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, StatusItemResult::class);
    }

    public function testAllSystemFieldsHasValidTypeAnnotation():void
    {
        $allFields = $this->statusService->fields()->getFieldsDescription();
        $systemFieldsCodes = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter($allFields, static fn($code): bool => in_array($code, $systemFieldsCodes, true), ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $systemFields,
            StatusItemResult::class);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $newStatus = [
            'ENTITY_ID' => 'SOURCE',
            'STATUS_ID' => 'undefined',
            'SORT' => 100,
            'NAME' => 'Test status',
        ];
        $newId = $this->statusService->add($newStatus)->getId();
        self::assertGreaterThan(1, $newId);
        $this->statusService->delete($newId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $newStatus = [
            'ENTITY_ID' => 'SOURCE',
            'STATUS_ID' => 'undefined',
            'SORT' => 100,
            'NAME' => 'Test status',
        ];
        $newId = $this->statusService->add($newStatus)->getId();
        self::assertTrue($this->statusService->delete($newId)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testFields(): void
    {
        self::assertIsArray($this->statusService->fields()->getFieldsDescription());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $newStatus = [
            'ENTITY_ID' => 'SOURCE',
            'STATUS_ID' => 'undefined',
            'SORT' => 100,
            'NAME' => 'Test status',
        ];
        $newId = $this->statusService->add($newStatus)->getId();
        self::assertGreaterThan(
            1,
            $this->statusService->get($newId)->status()->ID
        );
        $this->statusService->delete($newId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $newStatus = [
            'ENTITY_ID' => 'SOURCE',
            'STATUS_ID' => 'undefined',
            'SORT' => 100,
            'NAME' => 'Test status',
        ];
        $newId = $this->statusService->add($newStatus)->getId();
        self::assertGreaterThanOrEqual(1, $this->statusService->list([], [], ['ID', 'NAME'])->getStatuses());
        $this->statusService->delete($newId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $newStatus = [
            'ENTITY_ID' => 'SOURCE',
            'STATUS_ID' => 'undefined',
            'SORT' => 100,
            'NAME' => 'Test status',
        ];
        $newId = $this->statusService->add($newStatus)->getId();
        $newName = 'Test2';

        self::assertTrue($this->statusService->update($newId, ['NAME' => $newName])->isSuccess());
        self::assertEquals($newName, $this->statusService->get($newId)->status()->NAME);
        $this->statusService->delete($newId);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    public function testCountByFilter(): void
    {
        $before = $this->statusService->countByFilter();

        $newStatus = [
            'ENTITY_ID' => 'SOURCE',
            'STATUS_ID' => 'undefined',
            'SORT' => 100,
            'NAME' => 'Test status',
        ];
        $newId = $this->statusService->add($newStatus)->getId();

        $after = $this->statusService->countByFilter();

        $this->assertEquals($before + 1, $after);
        $this->statusService->delete($newId);
    }
}
