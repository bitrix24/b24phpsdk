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

namespace Bitrix24\SDK\Tests\Integration\Services\Lists\Element\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Lists\Element\Result\ElementItemResult;
use Bitrix24\SDK\Services\Lists\Element\Service\Element;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class ElementTest
 *
 * Integration tests for Lists Element service
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Lists\Element\Service
 */
#[CoversClass(Element::class)]
#[CoversMethod(Element::class, 'add')]
#[CoversMethod(Element::class, 'delete')]
#[CoversMethod(Element::class, 'get')]
#[CoversMethod(Element::class, 'update')]
#[CoversMethod(Element::class, 'getFileUrl')]
class ElementTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Element $elementService;

    private int $testListId;

    private string $testListCode;

    /**
     * @throws \Exception
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->elementService = Factory::getServiceBuilder()->getListsScope()->element();

        // Create a test list for element operations
        $this->testListCode = 'sdk_element_test_' . (int)(microtime(true) * 1000000);
        $listFields = [
            'NAME' => 'SDK Element Test List',
            'DESCRIPTION' => 'Test list for element integration tests',
            'SORT' => 100,
            'BIZPROC' => 'N'
        ];

        $listsService = Factory::getServiceBuilder()->getListsScope()->lists();
        $addedItemResult = $listsService->add('lists', $this->testListCode, $listFields);
        $this->testListId = $addedItemResult->getId();
    }

    /**
     * Clean up test environment
     */
    #[\Override]
    protected function tearDown(): void
    {
        // Clean up: delete the test list
        if (isset($this->testListId)) {
            try {
                $listsService = Factory::getServiceBuilder()->getListsScope()->lists();
                $listsService->delete('lists', $this->testListId);
            } catch (\Exception) {
                // Ignore cleanup errors
            }
        }
    }

    /**
     * Test create, read, update, delete element operations
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testCrudOperations(): void
    {
        $uniqueCode = 'test_element_' . (int)(microtime(true) * 1000000);
        $elementFields = [
            'NAME' => 'Test Element for SDK Integration',
        ];

        // Test element creation
        $addedItemResult = $this->elementService->add(
            'lists',
            $this->testListId,
            $uniqueCode,
            $elementFields
        );

        $this->assertIsInt($addedItemResult->getId());
        $this->assertGreaterThan(0, $addedItemResult->getId());
        $elementId = $addedItemResult->getId();

        try {
            // Test element retrieval by ID
            $getResult = $this->elementService->get(
                'lists',
                $this->testListId,
                $elementId
            );

            $elements = $getResult->getElements();
            $this->assertNotEmpty($elements);
            $this->assertInstanceOf(ElementItemResult::class, $elements[0]);
            $this->assertEquals($elementFields['NAME'], $elements[0]->NAME);
            $this->assertEquals($uniqueCode, $elements[0]->CODE);

            // Test element retrieval by code
            $getByCodeResult = $this->elementService->get(
                'lists',
                $this->testListCode,
                $uniqueCode
            );

            $elementsByCode = $getByCodeResult->getElements();
            $this->assertNotEmpty($elementsByCode);
            $this->assertEquals($elementId, $elementsByCode[0]->ID);

            // Test element update
            $updateFields = [
                'NAME' => 'Updated Test Element for SDK Integration',
            ];

            $updateResult = $this->elementService->update(
                'lists',
                $this->testListId,
                $elementId,
                $updateFields
            );

            $this->assertTrue($updateResult->isSuccess());

            // Verify update was successful
            $verifyUpdateResult = $this->elementService->get(
                'lists',
                $this->testListId,
                $elementId
            );

            $updatedElements = $verifyUpdateResult->getElements();
            $this->assertNotEmpty($updatedElements);
            $this->assertEquals($updateFields['NAME'], $updatedElements[0]->NAME);

        } finally {
            // Clean up: delete the test element
            $deleteResult = $this->elementService->delete(
                'lists',
                $this->testListId,
                $elementId
            );

            $this->assertTrue($deleteResult->isSuccess());
        }
    }

    /**
     * Test getting multiple elements with filtering and pagination
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetMultipleElementsWithFilters(): void
    {
        $elements = [];
        $uniquePrefix = 'test_multi_' . (int)(microtime(true) * 1000000);

        try {
            // Create multiple test elements
            for ($i = 1; $i <= 5; $i++) {
                $elementCode = $uniquePrefix . '_' . $i;
                $elementFields = [
                    'NAME' => 'Test Element ' . $i,
                ];

                $addResult = $this->elementService->add(
                    'lists',
                    $this->testListId,
                    $elementCode,
                    $elementFields
                );

                $elements[] = $addResult->getId();
            }

            // Test getting all elements
            $getAllResult = $this->elementService->get(
                'lists',
                $this->testListId
            );
            $allElements = $getAllResult->getElements();

            // Should contain at least our test elements
            $this->assertGreaterThanOrEqual(5, count($allElements));

            // Test filtering by name
            $filterResult = $this->elementService->get(
                'lists',
                $this->testListId,
                null,
                [], // select all fields
                ['%NAME' => 'Test Element'] // filter by name containing "Test Element"
            );

            $filteredElements = $filterResult->getElements();
            $this->assertGreaterThanOrEqual(5, count($filteredElements));

            // Verify all filtered elements contain "Test Element" in name
            foreach ($filteredElements as $filteredElement) {
                $this->assertStringContainsString('Test Element', $filteredElement->NAME);
            }

            // Test with specific field selection
            $selectResult = $this->elementService->get(
                'lists',
                $this->testListId,
                null,
                ['ID', 'NAME', 'CODE'] // select only specific fields
            );

            $selectedElements = $selectResult->getElements();
            $this->assertGreaterThanOrEqual(5, count($selectedElements));

            // Test pagination with start parameter
            $paginatedResult = $this->elementService->get(
                'lists',
                $this->testListId,
                null,
                [],
                [],
                [],
                0 // start from first page
            );

            $paginatedElements = $paginatedResult->getElements();
            $this->assertNotEmpty($paginatedElements);

        } finally {
            // Clean up: delete all test elements
            foreach ($elements as $element) {
                try {
                    $this->elementService->delete(
                        'lists',
                        $this->testListId,
                        $element
                    );
                } catch (\Exception) {
                    // Ignore cleanup errors
                }
            }
        }
    }

    /**
     * Test getting elements with sorting
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetElementsWithSorting(): void
    {
        $elements = [];
        $uniquePrefix = 'test_sort_' . (int)(microtime(true) * 1000000);

        try {
            // Create multiple test elements with different sort values
            $sortValues = [300, 100, 200];
            $expectedOrder = ['Element B', 'Element C', 'Element A']; // Based on sort values 100, 200, 300

            for ($i = 0; $i < 3; $i++) {
                $elementCode = $uniquePrefix . '_' . $i;
                $elementFields = [
                    'NAME' => ['Element A', 'Element B', 'Element C'][$i],
                    'SORT' => $sortValues[$i]
                ];

                $addResult = $this->elementService->add(
                    'lists',
                    $this->testListId,
                    $elementCode,
                    $elementFields
                );

                $elements[] = $addResult->getId();
            }

            // Test sorting by SORT field in ascending order
            $sortedResult = $this->elementService->get(
                'lists',
                $this->testListId,
                null,
                ['ID', 'NAME', 'SORT'],
                ['%NAME' => 'Element'], // filter to our test elements
                ['SORT' => 'asc']
            );

            $sortedElements = $sortedResult->getElements();
            $this->assertGreaterThanOrEqual(3, count($sortedElements));

            // Verify sorting order - find our test elements in the result
            $foundElements = [];
            foreach ($sortedElements as $sortedElement) {
                if (in_array($sortedElement->NAME, $expectedOrder)) {
                    $foundElements[] = $sortedElement->NAME;
                }
            }

            // Should have found all 3 elements
            $this->assertCount(3, $foundElements);

        } finally {
            // Clean up: delete all test elements
            foreach ($elements as $element) {
                try {
                    $this->elementService->delete(
                        'lists',
                        $this->testListId,
                        $element
                    );
                } catch (\Exception) {
                    // Ignore cleanup errors
                }
            }
        }
    }

    /**
     * Test error handling for invalid parameters
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testErrorHandling(): void
    {
        // Test with non-existent list ID
        $this->expectException(BaseException::class);

        $this->elementService->add(
            'lists',
            999999, // non-existent list ID
            'test_code',
            ['NAME' => 'Test Element']
        );
    }

    /**
     * Test getFileUrl method
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetFileUrl(): void
    {
        $fieldsService = Factory::getServiceBuilder()->getListsScope()->field();
        $fileFieldCode = 'TEST_FILE_FIELD_' . (int)(microtime(true) * 1000000);

        // First, create a file field in our test list
        $fileFieldParams = [
            'NAME' => 'Test File Field',
            'CODE' => $fileFieldCode,
            'TYPE' => 'F',
            'IS_REQUIRED' => 'N',
            'MULTIPLE' => 'N'
        ];

        $addedFieldResult = $fieldsService->add(
            'lists',
            $fileFieldParams,
            $this->testListId,
            null // iblockCode
        );
        $fieldId = $addedFieldResult->getId();
        $fieldCleanId = intval(str_replace('PROPERTY_', '', $fieldId));
        $this->assertIsString($fieldId);
        $this->assertNotEmpty($fieldId);

        try {
            // Create a 1x1 red pixel GIF image in base64
            $imageBase64 = 'R0lGODlhAQABAIAAAAUEBAAAACwAAAAAAQABAAACAkQBADs=';

            // Create element with file
            $uniqueCode = 'test_file_element_' . (int)(microtime(true) * 1000000);
            $elementFields = [
                'NAME' => 'Test Element with File Field',
                $fieldId => [
                    'test_image.gif', // filename
                    $imageBase64      // base64 content
                ]
            ];

            // Test element creation with file
            $addedItemResult = $this->elementService->add(
                'lists',
                $this->testListId,
                $uniqueCode,
                $elementFields
            );

            $this->assertIsInt($addedItemResult->getId());
            $this->assertGreaterThan(0, $addedItemResult->getId());
            $elementId = $addedItemResult->getId();

            try {
                // Test getFileUrl method with real file
                $fileUrlResult = $this->elementService->getFileUrl(
                    'lists',
                    $this->testListId,
                    $elementId,
                    $fieldCleanId
                );

                // The method should return a result object
                $this->assertNotNull($fileUrlResult);

                // Verify result has getFileUrls method
                $this->assertTrue(method_exists($fileUrlResult, 'getFileUrls'));

                $fileUrls = $fileUrlResult->getFileUrls();

                // Should have at least one file URL since we uploaded a file
                $this->assertIsArray($fileUrls);
                $this->assertNotEmpty($fileUrls, 'File URLs should not be empty when file is uploaded');

                // Each file URL should be a string
                foreach ($fileUrls as $fileUrl) {
                    $this->assertIsString($fileUrl);
                    $this->assertNotEmpty($fileUrl);
                    // Should be a valid URL format
                    $this->assertStringContainsString($fieldId, $fileUrl);
                }

            } finally {
                // Clean up: delete the test element
                $deleteResult = $this->elementService->delete(
                    'lists',
                    $this->testListId,
                    $elementId
                );

                $this->assertTrue($deleteResult->isSuccess());
            }

        } finally {
            // Clean up: delete the file field
            try {
                $fieldsService->delete(
                    'lists',
                    $fieldId,
                    $this->testListId
                );
            } catch (\Exception) {
                // Ignore cleanup errors for field deletion
            }
        }
    }
}
