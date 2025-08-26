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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\PropertyVariant\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sale\PropertyVariant\Service\PropertyVariant;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class PropertyVariantTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Sale\PropertyVariant\Service
 */
#[CoversMethod(PropertyVariant::class,'add')]
#[CoversMethod(PropertyVariant::class,'update')]
#[CoversMethod(PropertyVariant::class,'get')]
#[CoversMethod(PropertyVariant::class,'list')]
#[CoversMethod(PropertyVariant::class,'delete')]
#[CoversMethod(PropertyVariant::class,'getFields')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Sale\PropertyVariant\Service\PropertyVariant::class)]
class PropertyVariantTest extends TestCase
{
    protected PropertyVariant $propertyVariantService;

    protected int $personTypeId;

    protected int $propertyGroupId;

    protected int $enumPropertyId;

    /**
     * Set up test environment
     * 
     * @throws BaseException
     * @throws TransportException
     */
    protected function setUp(): void
    {
        $this->propertyVariantService = Fabric::getServiceBuilder()->getSaleScope()->propertyVariant();
        $this->personTypeId = $this->getPersonTypeId();
        $this->propertyGroupId = $this->getPropertyGroupId($this->personTypeId);
        $this->enumPropertyId = $this->createEnumProperty($this->personTypeId, $this->propertyGroupId);
    }

    /**
     * Clean up test environment
     * 
     * @throws BaseException
     * @throws TransportException
     */
    protected function tearDown(): void
    {
        // Clean up created resources in reverse order of creation
        if (isset($this->enumPropertyId)) {
            $this->deleteProperty($this->enumPropertyId);
        }

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
        $core = Fabric::getCore();
        return (int)$core->call('sale.persontype.add', [
            'fields' => [
                'name' => 'Test Person Type for PropertyVariant',
                'sort' => 100,
            ]
        ])->getResponseData()->getResult()['personType']['id'];
    }

    /**
     * Helper method to delete a person type after testing
     */
    protected function deletePersonType(int $id): void
    {
        $core = Fabric::getCore();
        $core->call('sale.persontype.delete', [
            'id' => $id
        ]);
    }

    /**
     * Helper method to create a property group for testing
     */
    protected function getPropertyGroupId(int $personTypeId): int
    {
        $core = Fabric::getCore();
        return (int)$core->call('sale.propertygroup.add', [
            'fields' => [
                'personTypeId' => $personTypeId,
                'name' => 'Test Property Group for PropertyVariant',
                'sort' => 100,
            ]
        ])->getResponseData()->getResult()['propertyGroup']['id'];
    }

    /**
     * Helper method to delete a property group after testing
     */
    protected function deletePropertyGroup(int $id): void
    {
        $core = Fabric::getCore();
        $core->call('sale.propertygroup.delete', [
            'id' => $id
        ]);
    }

    /**
     * Helper method to create an ENUM property for testing
     */
    protected function createEnumProperty(int $personTypeId, int $propertyGroupId): int
    {
        $core = Fabric::getCore();
        return (int)$core->call('sale.property.add', [
            'fields' => [
                'personTypeId' => $personTypeId,
                'propsGroupId' => $propertyGroupId,
                'name' => 'Test ENUM Property',
                'type' => 'ENUM',
                'required' => 'N',
                'multiple' => 'N',
                'sort' => 100,
            ]
        ])->getResponseData()->getResult()['property']['id'];
    }

    /**
     * Helper method to delete a property after testing
     */
    protected function deleteProperty(int $id): void
    {
        $core = Fabric::getCore();
        $core->call('sale.property.delete', [
            'id' => $id
        ]);
    }

    /**
     * Test adding a property variant
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        // Create property variant
        $variantFields = [
            'orderPropsId' => $this->enumPropertyId,
            'name' => 'Test Variant',
            'value' => 'test_variant',
            'sort' => 100,
            'description' => 'Test description',
        ];

        $propertyVariantAddResult = $this->propertyVariantService->add($variantFields);
        $variantId = $propertyVariantAddResult->getId();

        // Verify the variant was created
        self::assertGreaterThan(0, $variantId);

        // Get the variant to further verify
        $propertyVariantResult = $this->propertyVariantService->get($variantId);
        $propertyVariantItemResult = $propertyVariantResult->getPropertyVariant();

        self::assertEquals($variantId, $propertyVariantItemResult->id);
        self::assertEquals('Test Variant', $propertyVariantItemResult->name);
        self::assertEquals('test_variant', $propertyVariantItemResult->value);

        // Clean up
        $this->propertyVariantService->delete($variantId);
    }

    /**
     * Test updating a property variant
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        // Create property variant
        $variantFields = [
            'orderPropsId' => $this->enumPropertyId,
            'name' => 'Test Variant',
            'value' => 'test_variant',
            'sort' => 100,
        ];

        $propertyVariantAddResult = $this->propertyVariantService->add($variantFields);
        $variantId = $propertyVariantAddResult->getId();

        // Update the variant - include value field which is required
        $updateFields = [
            'name' => 'Updated Test Variant',
            'value' => 'test_variant', // Value field must be included even if not changing
            'description' => 'New description',
        ];

        $this->propertyVariantService->update($variantId, $updateFields);

        // Verify the update
        $propertyVariantResult = $this->propertyVariantService->get($variantId);
        $propertyVariantItemResult = $propertyVariantResult->getPropertyVariant();

        self::assertEquals('Updated Test Variant', $propertyVariantItemResult->name);
        self::assertEquals('New description', $propertyVariantItemResult->description);

        // Clean up
        $this->propertyVariantService->delete($variantId);
    }

    /**
     * Test getting a property variant
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        // Create property variant
        $variantFields = [
            'orderPropsId' => $this->enumPropertyId,
            'name' => 'Get Test Variant',
            'value' => 'get_test_variant',
            'sort' => 100,
            'description' => 'Test description for get',
        ];

        $propertyVariantAddResult = $this->propertyVariantService->add($variantFields);
        $variantId = $propertyVariantAddResult->getId();

        // Get the variant
        $propertyVariantResult = $this->propertyVariantService->get($variantId);
        $propertyVariantItemResult = $propertyVariantResult->getPropertyVariant();

        // Verify properties
        self::assertEquals($variantId, $propertyVariantItemResult->id);
        self::assertEquals('Get Test Variant', $propertyVariantItemResult->name);
        self::assertEquals('get_test_variant', $propertyVariantItemResult->value);
        self::assertEquals('Test description for get', $propertyVariantItemResult->description);

        // Clean up
        $this->propertyVariantService->delete($variantId);
    }

    /**
     * Test listing property variants
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        // Create multiple property variants
        $variant1Fields = [
            'orderPropsId' => $this->enumPropertyId,
            'name' => 'Variant One',
            'value' => 'variant_one',
            'sort' => 100,
        ];

        $variant2Fields = [
            'orderPropsId' => $this->enumPropertyId,
            'name' => 'Variant Two',
            'value' => 'variant_two',
            'sort' => 200,
        ];

        $variant1Id = $this->propertyVariantService->add($variant1Fields)->getId();
        $variant2Id = $this->propertyVariantService->add($variant2Fields)->getId();

        // List variants
        $filter = ['orderPropsId' => $this->enumPropertyId];
        $propertyVariantsResult = $this->propertyVariantService->list([], $filter);
        $variants = $propertyVariantsResult->getPropertyVariants();

        // Verify we have at least the 2 variants we created
        self::assertGreaterThanOrEqual(2, count($variants));

        // Verify our variants are in the list
        $foundVariant1 = false;
        $foundVariant2 = false;

        foreach ($variants as $variant) {
            if ((int)$variant->id === $variant1Id) {
                $foundVariant1 = true;
                self::assertEquals('Variant One', $variant->name);
            }

            if ((int)$variant->id === $variant2Id) {
                $foundVariant2 = true;
                self::assertEquals('Variant Two', $variant->name);
            }
        }

        self::assertTrue($foundVariant1);
        self::assertTrue($foundVariant2);

        // Clean up
        $this->propertyVariantService->delete($variant1Id);
        $this->propertyVariantService->delete($variant2Id);
    }

    /**
     * Test deleting a property variant
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        // Create property variant
        $variantFields = [
            'orderPropsId' => $this->enumPropertyId,
            'name' => 'Delete Test Variant',
            'value' => 'delete_test_variant',
            'sort' => 100,
        ];

        $propertyVariantAddResult = $this->propertyVariantService->add($variantFields);
        $variantId = $propertyVariantAddResult->getId();

        // Delete the variant
        $deletedItemResult = $this->propertyVariantService->delete($variantId);

        // Verify the delete was successful
        self::assertTrue($deletedItemResult->isSuccess());

        // Verify the variant no longer exists
        try {
            $this->propertyVariantService->get($variantId);
            self::fail('Expected exception when getting deleted variant');
        } catch (\Exception $exception) {
            // Expected exception - check for error message indicating variant doesn't exist
            self::assertStringContainsString('not exists', $exception->getMessage());
        }
    }

    /**
     * Test getting available fields of property variants
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetFields(): void
    {
        // Get fields
        $propertyVariantFieldsResult = $this->propertyVariantService->getFields();
        $fields = $propertyVariantFieldsResult->getFieldsDescription();

        // Verify fields structure
        self::assertIsArray($fields);
        self::assertNotEmpty($fields);

        // Verify some expected fields are present
        self::assertArrayHasKey('id', $fields);
        self::assertArrayHasKey('name', $fields);
        self::assertArrayHasKey('value', $fields);
        self::assertArrayHasKey('orderPropsId', $fields);
    }
}
