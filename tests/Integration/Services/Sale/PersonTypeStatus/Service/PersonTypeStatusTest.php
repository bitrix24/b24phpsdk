<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\PersonTypeStatus\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sale\PersonTypeStatus\Service\PersonTypeStatus;
use Bitrix24\SDK\Services\Sale\PersonTypeStatus\Result\PersonTypeStatusItemResult;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\TestCase;

class PersonTypeStatusTest extends TestCase
{
    protected PersonTypeStatus $service;

    protected function setUp(): void
    {
        $this->service = Factory::getServiceBuilder()->getSaleScope()->personTypeStatus();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testFields(): void
    {
        self::assertIsArray($this->service->getFields()->getFieldsDescription());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $list = $this->service->list();
        $items = $list->getPersonTypeStatuses();
        if ($items !== []) {
            self::assertInstanceOf(PersonTypeStatusItemResult::class, $items[0]);
        } else {
            self::assertIsArray($items);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $personTypeId = $this->getPersonTypeId();

    self::assertTrue($this->service->add($personTypeId, 'I')->isSuccess());

    $items = $this->service->list(['personTypeId' => $personTypeId, 'domain' => 'I'])->getPersonTypeStatuses();
    self::assertNotEmpty($items);
    self::assertEquals('I', $items[0]->domain);
    self::assertEquals($personTypeId, $items[0]->personTypeId);

        // cleanup
        $this->deletePersonType($personTypeId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $personTypeId = $this->getPersonTypeId();

        $this->service->add($personTypeId, 'I');

        $deletedItemResult = $this->service->delete($personTypeId, 'I');
        self::assertTrue($deletedItemResult->isSuccess());

        $itemsAfter = $this->service->list(['personTypeId' => $personTypeId, 'domain' => 'I'])->getPersonTypeStatuses();
        self::assertEmpty($itemsAfter);

        // cleanup
        $this->deletePersonType($personTypeId);
    }

    protected function getPersonTypeId(): int
    {
        $core = Factory::getCore();
        return (int)$core->call('sale.persontype.add', [
            'fields' => [
                'name' => 'Test Person Type',
                'sort' => 100,
            ]
        ])->getResponseData()->getResult()['personType']['id'];
    }

    protected function deletePersonType(int $id): void
    {
        $core = Factory::getCore();
        $core->call('sale.persontype.delete', [
            'id' => $id
       ]);
    }
    
}
