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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\PropertyGroup\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sale\PropertyGroup\Result\PropertyGroupItemResult;
use Bitrix24\SDK\Services\Sale\PropertyGroup\Service\PropertyGroup;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(PropertyGroup::class, 'add')]
#[CoversMethod(PropertyGroup::class, 'update')]
#[CoversMethod(PropertyGroup::class, 'get')]
#[CoversMethod(PropertyGroup::class, 'list')]
#[CoversMethod(PropertyGroup::class, 'delete')]
#[CoversMethod(PropertyGroup::class, 'getFields')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Sale\PropertyGroup\Service\PropertyGroup::class)]
class PropertyGroupTest extends TestCase
{
    use CustomBitrix24Assertions;

    private PropertyGroup $service;

    private int $personTypeId;

    /** @var int[] */
    private array $createdIds = [];

    protected function setUp(): void
    {
        $this->service = Factory::getServiceBuilder()->getSaleScope()->propertyGroup();
        $this->personTypeId = $this->getPersonTypeId();
    }

    protected function tearDown(): void
    {
        $this->deletePersonType($this->personTypeId);
        
        foreach ($this->createdIds as $createdId) {
            try {
                $this->service->delete($createdId);
            } catch (\Throwable) {
            }
        }
    }

    public function testFields(): void
    {
        $fields = $this->service->getFields()->getFieldsDescription();
        self::assertIsArray($fields);
        $this->assertNotEmpty($fields);
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($fields), PropertyGroupItemResult::class);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAddGetUpdateDeleteList(): void
    {
        $name = 'SDK Test PG ' . uniqid('', true);
        $propertyGroupAddResult = $this->service->add([
            'name' => $name,
            'personTypeId' => $this->personTypeId,
            'sort' => 100,
        ]);
        $id = $propertyGroupAddResult->getId();
        $this->createdIds[] = $id;
        self::assertGreaterThan(0, $id);

        $propertyGroupItemResult = $this->service->get($id)->propertyGroup();
        self::assertEquals($name, $propertyGroupItemResult->name);
        self::assertEquals($this->personTypeId, $propertyGroupItemResult->personTypeId);

        $newName = $name . ' upd';
        self::assertTrue($this->service->update($id, ['personTypeId' => $this->personTypeId, 'name' => $newName])->isSuccess());
        self::assertEquals($newName, $this->service->get($id)->propertyGroup()->name);

    $list = $this->service->list(['id','name','personTypeId','sort'], ['id' => $id], ['id' => 'asc'])->propertyGroups();
        self::assertNotEmpty($list);
        self::assertEquals($id, $list[0]->id);

        self::assertTrue($this->service->delete($id)->isSuccess());
        // remove from cleanup since deleted
        $this->createdIds = array_values(array_filter($this->createdIds, static fn($i): bool => $i !== $id));
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
