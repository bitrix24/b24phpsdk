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

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        $this->shipmentPropertyService = Fabric::getServiceBuilder()->getSaleScope()->shipmentProperty();
        $this->propertyName = 'Test Shipment Property ' . uniqid();
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
            } catch (\Exception $e) {
                // Property might have been deleted in the test
            }
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
            'sort' => 100
        ];

        $result = $this->shipmentPropertyService->add($propertyFields);
        $this->propertyId = $result->getId();

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
            'sort' => 100
        ];

        $addResult = $this->shipmentPropertyService->add($propertyFields);
        $this->propertyId = $addResult->getId();

        // Update the property
        $newName = 'Updated ' . $this->propertyName;
        $updateFields = [
            'name' => $newName
        ];

        $updateResult = $this->shipmentPropertyService->update($this->propertyId, $updateFields);
        self::assertTrue($updateResult->isSuccess());

        // Verify the update
        $getResult = $this->shipmentPropertyService->get($this->propertyId);
        $property = $getResult->property();
        
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
            'sort' => 100
        ];

        $addResult = $this->shipmentPropertyService->add($propertyFields);
        $this->propertyId = $addResult->getId();

        // Get the property
        $getResult = $this->shipmentPropertyService->get($this->propertyId);
        $property = $getResult->property();
        
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
            'sort' => 100
        ];

        $addResult = $this->shipmentPropertyService->add($propertyFields);
        $this->propertyId = $addResult->getId();

        // List properties with filter
        $filter = [
            'name' => $this->propertyName
        ];

        $listResult = $this->shipmentPropertyService->list([], $filter);
        $properties = $listResult->getProperties();
        
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
            'sort' => 100
        ];

        $addResult = $this->shipmentPropertyService->add($propertyFields);
        $this->propertyId = $addResult->getId();

        // Delete the property
        $deleteResult = $this->shipmentPropertyService->delete($this->propertyId);
        self::assertTrue($deleteResult->isSuccess());

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
        $fieldsResult = $this->shipmentPropertyService->getFieldsByType('STRING');
        $fields = $fieldsResult->getFieldsDescription();
        
        self::assertIsArray($fields);
        self::assertNotEmpty($fields);
        
        // Verify essential fields are present
        self::assertArrayHasKey('NAME', $fields);
        self::assertArrayHasKey('TYPE', $fields);
        self::assertArrayHasKey('REQUIRED', $fields);
    }
}
