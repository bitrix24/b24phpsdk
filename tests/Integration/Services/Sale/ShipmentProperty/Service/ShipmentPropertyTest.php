<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\ShipmentProperty\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\Sale\ShipmentProperty\Result\ShipmentPropertyItemResult;
use Bitrix24\SDK\Services\Sale\ShipmentProperty\Service\ShipmentProperty;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class ShipmentPropertyTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Sale\ShipmentProperty\Service
 */
#[CoversMethod(ShipmentProperty::class,'add')]
#[CoversMethod(ShipmentProperty::class,'update')]
#[CoversMethod(ShipmentProperty::class,'get')]
#[CoversMethod(ShipmentProperty::class,'list')]
#[CoversMethod(ShipmentProperty::class,'delete')]
#[CoversMethod(ShipmentProperty::class,'getFieldsByType')]
#[CoversMethod(ShipmentPropertyTest::class,'testAllSystemFieldsAnnotated')]
#[CoversMethod(ShipmentPropertyTest::class,'testAllSystemFieldsHasValidTypeAnnotation')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Sale\ShipmentProperty\Service\ShipmentProperty::class)]
class ShipmentPropertyTest extends TestCase
{
    use CustomBitrix24Assertions;

    protected ShipmentProperty $shipmentPropertyService;

    protected int $propertyId;

    protected string $propertyName;

    protected int $personTypeId;

    protected int $propertyGroupId;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        $this->shipmentPropertyService = Fabric::getServiceBuilder()->getSaleScope()->shipmentProperty();
        $this->propertyName = 'Test Shipment Property ' . uniqid();
        $this->personTypeId = $this->getPersonTypeId();
        $this->propertyGroupId = $this->getPropertyGroupId();
    }

    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($this->shipmentPropertyService->getFieldsByType('STRING')->getFieldsDescription()));
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, ShipmentPropertyItemResult::class);
    }

    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $allFields = $this->shipmentPropertyService->getFieldsByType('STRING')->getFieldsDescription();
        $systemFieldsCodes = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter($allFields, static fn($code): bool => in_array($code, $systemFieldsCodes, true), ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $systemFields,
            ShipmentPropertyItemResult::class);
    }

    /**
     * Clean up resources after tests
     */
    protected function tearDown(): void
    {
        // Clean up created property if it exists
        if (isset($this->propertyId)) {
            try {
                $this->shipmentPropertyService->delete($this->propertyId);
            } catch (\Exception) {
                // Property might have been deleted in the test
            }
        }
        
        // Clean up property group
        if (isset($this->propertyGroupId)) {
            $this->deletePropertyGroup($this->propertyGroupId);
        }
        
        // Clean up person type
        if (isset($this->personTypeId)) {
            $this->deletePersonType($this->personTypeId);
        }
    }

    /**
     * Test adding a shipment property
     * 
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        // Create a property
        $propertyFields = [
            'name' => $this->propertyName,
            'type' => 'STRING',
            'required' => 'N',
            'sort' => 100,
            'personTypeId' => $this->personTypeId,
            'propsGroupId' => $this->propertyGroupId
        ];

        $addedShipmentPropertyResult = $this->shipmentPropertyService->add($propertyFields);
        $this->propertyId = $addedShipmentPropertyResult->getId();

        self::assertGreaterThan(0, $this->propertyId);
    }

    /**
     * Test updating a shipment property
     * 
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        // Create a property first
        $propertyFields = [
            'name' => $this->propertyName,
            'type' => 'STRING',
            'required' => 'N',
            'sort' => 100,
            'personTypeId' => $this->personTypeId,
            'propsGroupId' => $this->propertyGroupId
        ];

        $addedShipmentPropertyResult = $this->shipmentPropertyService->add($propertyFields);
        $this->propertyId = $addedShipmentPropertyResult->getId();

        // Update the property
        $newName = 'Updated ' . $this->propertyName;
        $updateFields = [
            'name' => $newName
        ];

        $updatedShipmentPropertyResult = $this->shipmentPropertyService->update($this->propertyId, $updateFields);
        self::assertTrue($updatedShipmentPropertyResult->isSuccess());

        // Verify the update
        $shipmentPropertyResult = $this->shipmentPropertyService->get($this->propertyId);
        $property = $shipmentPropertyResult->property();

        self::assertEquals($newName, $property->name);
    }

    /**
     * Test getting a shipment property
     * 
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        // Create a property first
        $propertyFields = [
            'name' => $this->propertyName,
            'type' => 'STRING',
            'required' => 'N',
            'sort' => 100,
            'personTypeId' => $this->personTypeId,
            'propsGroupId' => $this->propertyGroupId
        ];

        $addedShipmentPropertyResult = $this->shipmentPropertyService->add($propertyFields);
        $this->propertyId = $addedShipmentPropertyResult->getId();

        // Get the property
        $shipmentPropertyResult = $this->shipmentPropertyService->get($this->propertyId);
        $property = $shipmentPropertyResult->property();

        self::assertEquals($this->propertyId, $property->id);
        self::assertEquals($this->propertyName, $property->name);
        self::assertEquals('STRING', $property->type);
    }

    /**
     * Test listing shipment properties
     * 
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        // Create a property first
        $propertyFields = [
            'name' => $this->propertyName,
            'type' => 'STRING',
            'required' => 'N',
            'sort' => 100,
            'personTypeId' => $this->personTypeId,
            'propsGroupId' => $this->propertyGroupId
        ];

        $addedShipmentPropertyResult = $this->shipmentPropertyService->add($propertyFields);
        $this->propertyId = $addedShipmentPropertyResult->getId();

        // List properties with filter
        $filter = [
            'name' => $this->propertyName
        ];

        $shipmentPropertiesResult = $this->shipmentPropertyService->list([], $filter);
        $properties = $shipmentPropertiesResult->getProperties();

        self::assertGreaterThanOrEqual(1, count($properties));

        // Verify our property is in the list
        $found = false;
        foreach ($properties as $property) {
            if ($property->id === $this->propertyId) {
                $found = true;
                break;
            }
        }

        self::assertTrue($found);
    }

    /**
     * Test deleting a shipment property
     * 
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        // Create a property first
        $propertyFields = [
            'name' => $this->propertyName,
            'type' => 'STRING',
            'required' => 'N',
            'sort' => 100,
            'personTypeId' => $this->personTypeId,
            'propsGroupId' => $this->propertyGroupId
        ];

        $addedShipmentPropertyResult = $this->shipmentPropertyService->add($propertyFields);
        $this->propertyId = $addedShipmentPropertyResult->getId();

        // Delete the property
        $deletedItemResult = $this->shipmentPropertyService->delete($this->propertyId);
        self::assertTrue($deletedItemResult->isSuccess());

        // Verify deletion
        $this->expectException(BaseException::class);
        $this->shipmentPropertyService->get($this->propertyId);

        // Clear property ID since it's been deleted
        $this->propertyId = 0;
    }

    /**
     * Test getting shipment property fields for a specific property type
     * 
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetFieldsByType(): void
    {
        $shipmentPropertyFieldsResult = $this->shipmentPropertyService->getFieldsByType('STRING');
        $fields = $shipmentPropertyFieldsResult->getFieldsDescription();

        self::assertIsArray($fields);
        self::assertNotEmpty($fields);

        // Verify essential fields are present
        self::assertArrayHasKey('name', $fields);
        self::assertArrayHasKey('type', $fields);
        self::assertArrayHasKey('required', $fields);
    }

    /**
     * Create a person type for testing
     */
    protected function getPersonTypeId(): int
    {
        $core = Fabric::getCore();
        return (int)$core->call('sale.persontype.add', [
            'fields' => [
                'name' => 'Test Person Type ' . uniqid(),
                'sort' => 100,
            ]
        ])->getResponseData()->getResult()['personType']['id'];
    }

    /**
     * Delete a person type
     */
    protected function deletePersonType(int $id): void
    {
        try {
            $core = Fabric::getCore();
            $core->call('sale.persontype.delete', [
                'id' => $id
            ]);
        } catch (\Exception) {
            // Ignore cleanup errors
        }
    }

    /**
     * Create a property group for testing
     */
    protected function getPropertyGroupId(): int
    {
        $core = Fabric::getCore();
        return (int)$core->call('sale.propertygroup.add', [
            'fields' => [
                'name' => 'Test Property Group ' . uniqid(),
                'personTypeId' => $this->personTypeId,
                'sort' => 100,
            ]
        ])->getResponseData()->getResult()['propertyGroup']['id'];
    }

    /**
     * Delete a property group
     */
    protected function deletePropertyGroup(int $id): void
    {
        try {
            $core = Fabric::getCore();
            $core->call('sale.propertygroup.delete', [
                'id' => $id
            ]);
        } catch (\Exception) {
            // Ignore cleanup errors
        }
    }
}
