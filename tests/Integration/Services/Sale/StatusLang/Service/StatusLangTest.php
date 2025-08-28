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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\StatusLang\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sale\StatusLang\Result\StatusLangItemResult;
use Bitrix24\SDK\Services\Sale\StatusLang\Service\StatusLang;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Class StatusLangTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Sale\StatusLang\Service
 */
#[CoversMethod(StatusLang::class, 'getListLangs')]
#[CoversMethod(StatusLang::class, 'add')]
#[CoversMethod(StatusLang::class, 'list')]
#[CoversMethod(StatusLang::class, 'deleteByFilter')]
#[CoversMethod(StatusLang::class, 'getFields')]
#[CoversClass(StatusLang::class)]
class StatusLangTest extends TestCase
{
    protected StatusLang $statusLangService;

    protected array $createdStatusIds = [];

    protected array $createdStatusLangs = [];

    /**
     * @throws BaseException
     * @throws TransportException
     */
    protected function setUp(): void
    {
        $this->statusLangService = Fabric::getServiceBuilder()->getSaleScope()->statusLang();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    protected function tearDown(): void
    {
        // Clean up any test status langs created during tests
        foreach ($this->createdStatusLangs as $createdRectorPrefix202407StatusLang) {
            try {
                $this->statusLangService->deleteByFilter([
                    'statusId' => $createdRectorPrefix202407StatusLang['STATUS_ID'],
                    'lid' => $createdRectorPrefix202407StatusLang['LID'],
                    'name' => $createdRectorPrefix202407StatusLang['NAME'],
                ]);
            } catch (\Exception) {
                // Ignore errors during cleanup
            }
        }

        // Clean up any test statuses created during tests
        $statusService = Fabric::getServiceBuilder()->getSaleScope()->status();
        foreach ($this->createdStatusIds as $createdRectorPrefix202407StatusId) {
            try {
                $statusService->delete($createdRectorPrefix202407StatusId);
            } catch (\Exception) {
                // Ignore errors during cleanup
            }
        }
    }

    /**
     * Helper method to create a test status for language tests
     *
     * @return string ID of the created status
     * @throws BaseException
     * @throws TransportException
     */
    protected function createTestStatus(): string
    {
        // Generate a unique ID - must be max 2 characters
        $statusId = 'T' . chr(random_int(65, 90)); // Random letter A-Z with prefix T
        $statusName = 'Test Status ' . time();

        $statusService = Fabric::getServiceBuilder()->getSaleScope()->status();
        $statusService->add([
            'id' => $statusId,
            'type' => 'O', // Order status
            'sort' => 500,
            'notify' => false,
            'name' => [
                'br' => $statusName,
            ],
        ]);

        $this->createdStatusIds[] = $statusId;
        return $statusId;
    }

    /**
     * Helper method to create a test status language
     *
     * @param string $statusId ID of the status
     * @param string $lid Language ID
     * @param string $name Name in the language
     * @return array Created status language data
     * @throws BaseException
     * @throws TransportException
     */
    protected function createStatusLang(string $statusId, string $lid, string $name): array
    {
        $fields = [
            'statusId' => $statusId,
            'lid' => $lid,
            'name' => $name,
            'description' => 'Test description for ' . $name
        ];

        $this->statusLangService->add($fields);

        $this->createdStatusLangs[] = [
            'STATUS_ID' => $statusId,
            'LID' => $lid,
            'NAME' => $name,
        ];
        return $fields;
    }

    /**
     * Test getting list of available languages
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetListLangs(): void
    {
        $languagesResult = $this->statusLangService->getListLangs();
        $languages = $languagesResult->getLanguages();

        $this->assertIsArray($languages);
        $this->assertNotEmpty($languages);

        // Just check there are languages, don't check for a specific one
        $languageExists = false;
        foreach ($languages as $language) {
            if (isset($language['lid'])) {
                $languageExists = true;
                break;
            }
        }

        $this->assertTrue($languageExists, 'No languages found');
    }

    /**
     * Test adding a new status language
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        // Create a status first
        $statusId = $this->createTestStatus();
        $langId = 'en';
        $name = 'Test Status Language ' . time();

        // Add status language
        $statusLangAddResult = $this->statusLangService->add([
            'statusId' => $statusId,
            'lid' => $langId,
            'name' => $name,
            'description' => 'Test description'
        ]);

        // Track for cleanup
        $this->createdStatusLangs[] = [
            'STATUS_ID' => $statusId,
            'LID' => $langId,
            'NAME' => $name,
        ];

        // Verify result was successful
        $this->assertTrue($statusLangAddResult->isSuccess());

        // Verify we can retrieve the added status language
        $statusLangsResult = $this->statusLangService->list(
            [],
            [
                'STATUS_ID' => $statusId,
                'LID' => $langId
            ]
        );

        $statusLangs = $statusLangsResult->getStatusLangs();
        $this->assertNotEmpty($statusLangs);

        // Find our status language
        $found = false;
        foreach ($statusLangs as $RectorPrefix202407statusLang) {
            if ($RectorPrefix202407statusLang->statusId === $statusId && $RectorPrefix202407statusLang->lid === $langId) {
                $this->assertEquals($name, $RectorPrefix202407statusLang->name);
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Status language not found in the list');
    }

    /**
     * Test getting list of status languages
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        // Create a status first
        $statusId = $this->createTestStatus();

        // Add a couple of languages to test listing
        $this->createStatusLang($statusId, 'en', 'English Test Status');
        $this->createStatusLang($statusId, 'de', 'German Test Status');

        // Get list 
        $statusLangsResult = $this->statusLangService->list();

        $statusLangs = $statusLangsResult->getStatusLangs();
        // Just check it returns something that looks like a collection
        $this->assertIsArray($statusLangs);
    }

    /**
     * Test deleting status languages by filter
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testDeleteByFilter(): void
    {
        // Create a status first
        $statusId = $this->createTestStatus();

        // Add status languages
        $this->createStatusLang($statusId, 'en', 'English Test Status');

        // Delete by filter
        $deletedItemResult = $this->statusLangService->deleteByFilter([
            'statusId' => $statusId,
            'lid' => 'en',
            'name' => 'English Test Status',
        ]);

        // Check that deletion was successful
        $this->assertTrue($deletedItemResult->isSuccess());
    }

    /**
     * Test getting field descriptions
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetFields(): void
    {
        $statusLangFieldsResult = $this->statusLangService->getFields();
        $fields = $statusLangFieldsResult->getFieldsDescription();

        $this->assertIsArray($fields);
        $this->assertNotEmpty($fields);

        // Check for required fields
        $requiredFields = ['statusId', 'lid', 'name'];
        foreach ($requiredFields as $requiredField) {
            $this->assertArrayHasKey($requiredField, $fields);
        }
    }
}
