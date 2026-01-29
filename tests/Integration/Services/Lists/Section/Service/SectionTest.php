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

namespace Bitrix24\SDK\Tests\Integration\Services\Lists\Section\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Lists\Section\Result\SectionItemResult;
use Bitrix24\SDK\Services\Lists\Section\Service\Section;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class SectionTest
 *
 * Integration tests for Section service
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Lists\Section\Service
 */
#[CoversClass(Section::class)]
#[CoversMethod(Section::class, 'add')]
#[CoversMethod(Section::class, 'delete')]
#[CoversMethod(Section::class, 'get')]
#[CoversMethod(Section::class, 'update')]
class SectionTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Section $sectionService;

    private int $testListId;

    private string $testListCode;

    /**
     * @throws \Exception
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->sectionService = Factory::getServiceBuilder()->getListsScope()->section();

        // Create a test list for sections
        $this->testListCode = 'test_section_list_' . (int)(microtime(true) * 1000000);
        $listFields = [
            'NAME' => 'Test List for Section Integration',
            'DESCRIPTION' => 'Test list created for section tests',
            'SORT' => 100,
            'BIZPROC' => 'N'
        ];

        $addedItemResult = Factory::getServiceBuilder()->getListsScope()->lists()->add(
            'lists',
            $this->testListCode,
            $listFields
        );

        $this->testListId = $addedItemResult->getId();
    }

    /**
     * Clean up test environment
     */
    #[\Override]
    protected function tearDown(): void
    {
        try {
            // Delete test list
            Factory::getServiceBuilder()->getListsScope()->lists()->delete(
                'lists',
                $this->testListId
            );
        } catch (\Exception) {
            // Ignore cleanup errors
        }
    }

    /**
     * Test create, read, update, delete section operations
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testCrudOperations(): void
    {
        $uniqueCode = 'test_section_' . (int)(microtime(true) * 1000000);
        $sectionFields = [
            'NAME' => 'Test Section for SDK Integration',
            'DESCRIPTION' => 'Test section created by SDK integration tests',
            'SORT' => 100,
            'ACTIVE' => 'Y'
        ];

        // Test section creation
        $addedItemResult = $this->sectionService->add(
            'lists',
            $this->testListId,
            $uniqueCode,
            $sectionFields
        );

        $this->assertIsInt($addedItemResult->getId());
        $this->assertGreaterThan(0, $addedItemResult->getId());
        $sectionId = $addedItemResult->getId();

        try {
            // Test section retrieval
            $getResult = $this->sectionService->get(
                'lists',
                $this->testListId,
                ['ID' => $sectionId],
                ['ID', 'NAME', 'CODE', 'DESCRIPTION', 'SORT', 'ACTIVE']
            );

            $sections = $getResult->getSections();
            $this->assertNotEmpty($sections);
            $this->assertInstanceOf(SectionItemResult::class, $sections[0]);
            $this->assertEquals($sectionFields['NAME'], $sections[0]->NAME);
            $this->assertEquals($uniqueCode, $sections[0]->CODE);
            $this->assertEquals($sectionId, $sections[0]->ID);

            // Test section update
            $updateFields = [
                'NAME' => 'Updated Test Section',
                'DESCRIPTION' => 'Updated description for test section',
                'SORT' => 200
            ];

            $updatedItemResult = $this->sectionService->update(
                'lists',
                $this->testListId,
                $sectionId,
                $updateFields
            );

            $this->assertTrue($updatedItemResult->isSuccess());

            // Verify update by getting the section again
            $updatedGetResult = $this->sectionService->get(
                'lists',
                $this->testListId,
                ['ID' => $sectionId],
                ['ID', 'NAME', 'DESCRIPTION', 'SORT']
            );

            $updatedSections = $updatedGetResult->getSections();
            $this->assertNotEmpty($updatedSections);
            $this->assertEquals($updateFields['NAME'], $updatedSections[0]->NAME);
            $this->assertEquals($updateFields['DESCRIPTION'], $updatedSections[0]->DESCRIPTION);

        } finally {
            // Clean up: delete the test section
            $deleteResult = $this->sectionService->delete(
                'lists',
                $this->testListId,
                $sectionId
            );

            $this->assertTrue($deleteResult->isSuccess());
        }
    }

    /**
     * Test getting multiple sections
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetMultipleSections(): void
    {
        $uniquePrefix = 'multi_test_' . (int)(microtime(true) * 1000000);
        $sectionsToCreate = [
            [
                'code' => $uniquePrefix . '_1',
                'fields' => [
                    'NAME' => 'Multi Test Section 1',
                    'SORT' => 100,
                    'ACTIVE' => 'Y'
                ]
            ],
            [
                'code' => $uniquePrefix . '_2',
                'fields' => [
                    'NAME' => 'Multi Test Section 2',
                    'SORT' => 200,
                    'ACTIVE' => 'Y'
                ]
            ]
        ];

        $createdSectionIds = [];

        try {
            // Create multiple sections
            foreach ($sectionsToCreate as $sectionToCreate) {
                $addResult = $this->sectionService->add(
                    'lists',
                    $this->testListId,
                    $sectionToCreate['code'],
                    $sectionToCreate['fields']
                );
                $createdSectionIds[] = $addResult->getId();
            }

            // Get all sections for the list
            $getResult = $this->sectionService->get(
                'lists',
                $this->testListId,
                [],
                ['ID', 'NAME', 'CODE', 'SORT']
            );

            $sections = $getResult->getSections();
            $this->assertGreaterThanOrEqual(2, count($sections));

            // Verify that our created sections are in the result
            $sectionNames = array_map(fn($section) => $section->NAME, $sections);
            $this->assertContains('Multi Test Section 1', $sectionNames);
            $this->assertContains('Multi Test Section 2', $sectionNames);

        } finally {
            // Clean up: delete all created sections
            foreach ($createdSectionIds as $createdSectionId) {
                try {
                    $this->sectionService->delete(
                        'lists',
                        $this->testListId,
                        $createdSectionId
                    );
                } catch (\Exception) {
                    // Ignore cleanup errors
                }
            }
        }
    }

    /**
     * Test getting section by code
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetByCode(): void
    {
        $uniqueCode = 'get_by_code_' . (int)(microtime(true) * 1000000);
        $sectionFields = [
            'NAME' => 'Get By Code Test Section',
            'SORT' => 100,
            'ACTIVE' => 'Y'
        ];

        // Create section
        $addedItemResult = $this->sectionService->add(
            'lists',
            $this->testListId,
            $uniqueCode,
            $sectionFields
        );

        $sectionId = $addedItemResult->getId();

        try {
            // Get section by code filter
            $getResult = $this->sectionService->get(
                'lists',
                $this->testListId,
                ['CODE' => $uniqueCode],
                ['ID', 'NAME', 'CODE']
            );

            $sections = $getResult->getSections();
            $this->assertNotEmpty($sections);
            $this->assertEquals($uniqueCode, $sections[0]->CODE);
            $this->assertEquals($sectionFields['NAME'], $sections[0]->NAME);

        } finally {
            // Clean up
            $this->sectionService->delete(
                'lists',
                $this->testListId,
                $sectionId
            );
        }
    }
}