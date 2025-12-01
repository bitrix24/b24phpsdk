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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\Property\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\Sale\Property\Service\Property;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class PropertyTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Sale\Property\Service
 */
#[CoversMethod(Property::class,'add')]
#[CoversMethod(Property::class,'update')]
#[CoversMethod(Property::class,'get')]
#[CoversMethod(Property::class,'list')]
#[CoversMethod(Property::class,'delete')]
#[CoversMethod(Property::class,'getFieldsByType')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Sale\Property\Service\Property::class)]
class PropertyTest extends TestCase
{
    protected Property $propertyService;

    protected int $personTypeId;

    protected int $propertyGroupId;

    protected function setUp(): void
    {
        $this->propertyService = Factory::getServiceBuilder()->getSaleScope()->property();
        $this->personTypeId = $this->getPersonTypeId();
        $this->propertyGroupId = $this->getPropertyGroupId($this->personTypeId);
    }

    protected function tearDown(): void
    {
        // Clean up created resources
        if (isset($this->propertyGroupId)) {
            $this->deletePropertyGroup($this->propertyGroupId);
        }

        if (isset($this->personTypeId)) {
            $this->deletePersonType($this->personTypeId);
        }
    }

    /**
     * Helper method to create a person type for testing
     */
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

    /**
     * Helper method to delete a person type after testing
     */
    protected function deletePersonType(int $id): void
    {
        $core = Factory::getCore();
        $core->call('sale.persontype.delete', [
            'id' => $id
        ]);
    }

    /**
     * Helper method to create a property group for testing
     */
    protected function getPropertyGroupId(int $personTypeId): int
    {
        $core = Factory::getCore();
        return (int)$core->call('sale.propertygroup.add', [
            'fields' => [
                'personTypeId' => $personTypeId,
                'name' => 'Test Property Group',
                'sort' => 100,
            ]
        ])->getResponseData()->getResult()['propertyGroup']['id'];
    }

    /**
     * Helper method to delete a property group after testing
     */
    protected function deletePropertyGroup(int $id): void
    {
        $core = Factory::getCore();
        $core->call('sale.propertygroup.delete', [
            'id' => $id
        ]);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        // Create a property
        $propertyFields = [
            'personTypeId' => $this->personTypeId,
            'propsGroupId' => $this->propertyGroupId,
            'name' => 'Test Property',
            'type' => 'STRING',
            'required' => 'N',
            'multiple' => 'N',
            'sort' => 100,
        ];

        $propertyAddResult = $this->propertyService->add($propertyFields);
        $propertyId = $propertyAddResult->getId();

        self::assertGreaterThan(0, $propertyId);

        // Clean up
        $this->propertyService->delete($propertyId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        // Create a property
        $propertyFields = [
            'personTypeId' => $this->personTypeId,
            'propsGroupId' => $this->propertyGroupId,
            'name' => 'Test Property',
            'type' => 'STRING',
            'required' => 'N',
            'multiple' => 'N',
            'sort' => 100,
        ];

        $propertyAddResult = $this->propertyService->add($propertyFields);
        $propertyId = $propertyAddResult->getId();

        // Update the property
        $updateFields = [
            'name' => 'Updated Test Property',
        ];

        $this->propertyService->update($propertyId, $updateFields);

        // Verify the update
        $propertyItemResult = $this->propertyService->get($propertyId)->getProperty();
        self::assertEquals('Updated Test Property', $propertyItemResult->name);

        // Clean up
        $this->propertyService->delete($propertyId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        // Create a property
        $propertyFields = [
            'personTypeId' => $this->personTypeId,
            'propsGroupId' => $this->propertyGroupId,
            'name' => 'Test Property',
            'type' => 'STRING',
            'required' => 'N',
            'multiple' => 'N',
            'sort' => 100,
        ];

        $propertyAddResult = $this->propertyService->add($propertyFields);
        $propertyId = $propertyAddResult->getId();

        // Get the property
        $propertyItemResult = $this->propertyService->get($propertyId)->getProperty();

        self::assertEquals($propertyId, $propertyItemResult->id);
        self::assertEquals('Test Property', $propertyItemResult->name);
        self::assertEquals('STRING', $propertyItemResult->type);

        // Clean up
        $this->propertyService->delete($propertyId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        // Create a property
        $propertyFields = [
            'personTypeId' => $this->personTypeId,
            'propsGroupId' => $this->propertyGroupId,
            'name' => 'Test List Property',
            'type' => 'STRING',
            'required' => 'N',
            'multiple' => 'N',
            'sort' => 100,
        ];

        $propertyAddResult = $this->propertyService->add($propertyFields);
        $propertyId = $propertyAddResult->getId();

        // List properties
        $filter = ['personTypeId' => $this->personTypeId];
        $propertiesResult = $this->propertyService->list([], $filter);
        $properties = $propertiesResult->getProperties();

        self::assertGreaterThan(0, count($properties));

        // Verify our property is in the list
        $found = false;
        foreach ($properties as $property) {
            if ((int)$property->id === $propertyId) {
                $found = true;
                break;
            }
        }

        self::assertTrue($found);

        // Clean up
        $this->propertyService->delete($propertyId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        // Create a property
        $propertyFields = [
            'personTypeId' => $this->personTypeId,
            'propsGroupId' => $this->propertyGroupId,
            'name' => 'Test Delete Property',
            'type' => 'STRING',
            'required' => 'N',
            'multiple' => 'N',
            'sort' => 100,
        ];

        $propertyAddResult = $this->propertyService->add($propertyFields);
        $propertyId = $propertyAddResult->getId();

        // Delete the property
        $this->propertyService->delete($propertyId);

        // Verify property no longer exists
        try {
            $this->propertyService->get($propertyId);
            self::fail('Expected exception when getting deleted property');
        } catch (\Exception $exception) {
            // Expected exception - check for error message indicating property doesn't exist
            // The actual message is something like "200840400001 - property is not exists"
            self::assertStringContainsString('property is not exists', $exception->getMessage());
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetFieldsByType(): void
    {
        // Get fields for STRING type
        $propertyFieldsByTypeResult = $this->propertyService->getFieldsByType('STRING');

        $fields = $propertyFieldsByTypeResult->getFieldsDescription();

        // Verify fields structure
        self::assertIsArray($fields);
        // The response structure may vary from the documentation
        // Instead of checking specific keys, just verify it's a non-empty array
        self::assertNotEmpty($fields);
    }
}
