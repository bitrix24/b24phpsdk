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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\PropertyRelation\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\Sale\PropertyRelation\Result\PropertyRelationItemResult;
use Bitrix24\SDK\Services\Sale\PropertyRelation\Service\PropertyRelation;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class PropertyRelationTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Sale\PropertyRelation\Service
 */
#[CoversMethod(PropertyRelation::class,'add')]
#[CoversMethod(PropertyRelation::class,'list')]
#[CoversMethod(PropertyRelation::class,'deleteByFilter')]
#[CoversMethod(PropertyRelation::class,'getFields')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Sale\PropertyRelation\Service\PropertyRelation::class)]
class PropertyRelationTest extends TestCase
{
    use CustomBitrix24Assertions;

    protected PropertyRelation $propertyRelationService;

    protected int $propertyId = 0;

    protected int $deliveryServiceId = 0;

    protected int $personTypeId = 0;

    protected int $propsGroupId = 0;

    #[\Override]
    protected function setUp(): void
    {
        $serviceBuilder = Factory::getServiceBuilder();
        $this->propertyRelationService = $serviceBuilder->getSaleScope()->propertyRelation();
        $this->personTypeId = $this->getPersonTypeId();
        $this->propsGroupId = $this->createTestPropertyGroup();
        $this->propertyId = $this->createTestProperty();
        $this->deliveryServiceId = $this->getDeliveryServiceId();
    }

    #[\Override]
    protected function tearDown(): void
    {
        // Clean up created resources in reverse order
        $this->deleteTestProperty($this->propertyId);
        $this->deleteTestPropertyGroup($this->propsGroupId);
        $this->deletePersonType($this->personTypeId);
    }

    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($this->propertyRelationService->getFields()->getFieldsDescription()));
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, PropertyRelationItemResult::class);
    }
    
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $allFields = $this->propertyRelationService->getFields()->getFieldsDescription();
        $systemFieldsCodes = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter($allFields, static fn($code): bool => in_array($code, $systemFieldsCodes, true), ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $systemFields,
            PropertyRelationItemResult::class);
    }

    /**
     * Helper method to create a person type for testing
     */
    protected function getPersonTypeId(): int
    {
        $personTypeService = Factory::getServiceBuilder()->getSaleScope()->personType();
        return $personTypeService->add([
            'name' => 'Test Person Type for PropertyRelation',
            'sort' => 100,
        ])->getId();
    }

    /**
     * Helper method to delete a person type after testing
     */
    protected function deletePersonType(int $id): void
    {
        $personTypeService = Factory::getServiceBuilder()->getSaleScope()->personType();
        $personTypeService->delete($id);
    }

    /**
     * Helper method to create a property group for testing
     */
    protected function createTestPropertyGroup(): int
    {
        $propertyGroupService = Factory::getServiceBuilder()->getSaleScope()->propertyGroup();
        return $propertyGroupService->add([
            'personTypeId' => $this->personTypeId,
            'name' => 'Test Property Group for PropertyRelation',
            'sort' => 100,
        ])->getId();
    }

    /**
     * Helper method to delete a property group after testing
     */
    protected function deleteTestPropertyGroup(int $id): void
    {
        $propertyGroupService = Factory::getServiceBuilder()->getSaleScope()->propertyGroup();
        try {
            $propertyGroupService->delete($id);
        } catch (\Exception) {
            // Ignore if property group doesn't exist
        }
    }

    /**
     * Helper method to get a delivery service for testing
     * We fetch an existing one from the system
     */
    protected function getDeliveryServiceId(): int
    {
        $core = Factory::getCore();
        $response = $core->call('sale.delivery.getlist', [
            'select' => ['ID'],
            'filter' => ['ACTIVE' => 'Y'],
            'order' => ['ID' => 'ASC']
        ]);

        $result = $response->getResponseData()->getResult();
        $deliveryServices = (is_array($result)) ? $result : [];

        return (int)$deliveryServices[0]['ID'];
    }

    /**
     * Helper method to create a test property
     */
    protected function createTestProperty(): int
    {
        $propertyService = Factory::getServiceBuilder()->getSaleScope()->property();
        $propertyFields = [
            'personTypeId' => $this->personTypeId,
            'propsGroupId' => $this->propsGroupId,
            'name' => 'Test Property for PropertyRelation',
            'type' => 'STRING',
            'required' => 'N',
            'active' => 'Y',
            'sort' => 100
        ];

        return $propertyService->add($propertyFields)->getId();
    }

    /**
     * Helper method to delete a test property
     */
    protected function deleteTestProperty(int $id): void
    {
        $propertyService = Factory::getServiceBuilder()->getSaleScope()->property();
        try {
            $propertyService->delete($id);
        } catch (\Exception) {
            // Ignore if property doesn't exist
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        // Create a property relation
        $relationFields = [
            'entityId' => $this->deliveryServiceId,
            'entityType' => 'D',
            'propertyId' => $this->propertyId
        ];

        $propertyRelationAddedResult = $this->propertyRelationService->add($relationFields);
        $entityId = $propertyRelationAddedResult->getId();

        self::assertEquals($this->deliveryServiceId, $entityId);

        // Clean up
        $this->propertyRelationService->deleteByFilter($relationFields);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        // Create a property relation
        $relationFields = [
            'entityId' => $this->deliveryServiceId,
            'entityType' => 'D',
            'propertyId' => $this->propertyId
        ];

        $this->propertyRelationService->add($relationFields);

        // List relations
        $filter = ['entityId' => $this->deliveryServiceId, 'entityType' => 'D'];
        $propertyRelationsResult = $this->propertyRelationService->list([], $filter);
        $relations = $propertyRelationsResult->getPropertyRelations();

        self::assertGreaterThan(0, count($relations));

        // Verify our relation is in the list
        $found = false;
        foreach ($relations as $relation) {
            if ($relation->entityId === $this->deliveryServiceId &&
                $relation->entityType === 'D' &&
                $relation->propertyId === $this->propertyId) {
                $found = true;
                break;
            }
        }

        self::assertTrue($found);

        // Clean up
        $this->propertyRelationService->deleteByFilter($relationFields);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDeleteByFilter(): void
    {
        // Create a property relation
        $relationFields = [
            'entityId' => $this->deliveryServiceId,
            'entityType' => 'D',
            'propertyId' => $this->propertyId
        ];

        $this->propertyRelationService->add($relationFields);

        // Delete the relation
        self::assertTrue($this->propertyRelationService->deleteByFilter($relationFields)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetFields(): void
    {
        // Get fields for property relation
        $propertyRelationFieldsResult = $this->propertyRelationService->getFields();
        $fields = $propertyRelationFieldsResult->getFieldsDescription();

        // Verify fields structure
        self::assertIsArray($fields);
        // Verify basic property relation fields are present
        self::assertArrayHasKey('entityId', $fields);
        self::assertArrayHasKey('entityType', $fields);
        self::assertArrayHasKey('propertyId', $fields);
    }
}