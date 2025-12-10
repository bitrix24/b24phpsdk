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

namespace Bitrix24\SDK\Tests\Integration\Services\IMOpenLines\Config;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IMOpenLines\Config\Result\GetRevisionResult;
use Bitrix24\SDK\Services\IMOpenLines\Config\Result\OptionItemResult;
use Bitrix24\SDK\Services\IMOpenLines\Config\Result\PathResult;
use Bitrix24\SDK\Services\IMOpenLines\Config\Service\Config;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class ConfigTest
 *
 * Integration tests for IMOpenLines Config service
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\IMOpenLines\Config
 */
#[CoversClass(Config::class)]
#[CoversMethod(Config::class, 'add')]
#[CoversMethod(Config::class, 'delete')]
#[CoversMethod(Config::class, 'get')]
#[CoversMethod(Config::class, 'getList')]
#[CoversMethod(Config::class, 'getPath')]
#[CoversMethod(Config::class, 'update')]
#[CoversMethod(Config::class, 'getRevision')]
class ConfigTest extends TestCase
{
    private Config $configService;
    private array $createdConfigIds = [];

    /**
     * Helper method to delete a test config
     */
    private function deleteTestConfig(int $id): void
    {
        try {
            $this->configService->delete($id);
        } catch (\Exception) {
            // Ignore if config doesn't exist
        }
    }

    /**
     * Test get revision
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetRevision(): void
    {
        $result = $this->configService->getRevision();
        
        self::assertInstanceOf(GetRevisionResult::class, $result);
        
        $revision = $result->revision();
        self::assertGreaterThan(0, $revision->rest);
        self::assertGreaterThan(0, $revision->web);
        self::assertGreaterThan(0, $revision->mobile);
    }

    /**
     * Test get path
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetPath(): void
    {
        $result = $this->configService->getPath();
        
        self::assertInstanceOf(PathResult::class, $result);
        
        $path = $result->getPath();
        self::assertNotEmpty($path);
        self::assertIsString($path);
    }

    /**
     * Test get list
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetList(): void
    {
        $result = $this->configService->getList();
        
        self::assertIsArray($result->getOptions());
        
        if (count($result->getOptions()) > 0) {
            $firstOption = $result->getOptions()[0];
            self::assertInstanceOf(OptionItemResult::class, $firstOption);
        }
    }

    /**
     * Test get list with filters
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetListWithFilters(): void
    {
        $result = $this->configService->getList(
            ['ID', 'LINE_NAME'],
            ['ID' => 'DESC'],
            ['ACTIVE' => 'Y']
        );
        
        self::assertIsArray($result->getOptions());
    }

    /**
     * Test add config
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $timestamp = time();
        $params = [
            'LINE_NAME' => 'Integration Test Line ' . $timestamp,
            'ACTIVE' => true,
            'QUEUE_TYPE' => 'evenly',
            'CRM' => true,
            'CRM_CREATE_THIRD' => true,
            'CHECK_AVAILABLE' => false,
            'WATCH_TYPING' => true,
            'WELCOME_MESSAGE' => true,
            'VOTE_MESSAGE' => true,
            'LANGUAGE_ID' => 'en'
        ];

        $result = $this->configService->add($params);
        $configId = $result->getId();
        
        self::assertGreaterThan(0, $configId);
        
        // Track for cleanup
        $this->createdConfigIds[] = $configId;
    }

    /**
     * Test get config
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        // First, get any existing config from the list
        $listResult = $this->configService->getList(['ID'], ['ID' => 'ASC'], null, ['limit' => 1]);
        $options = $listResult->getOptions();
        
        self::assertGreaterThan(0, count($options), 'No open lines available to test get method');
        
        $configId = (int)$options[0]->ID;
        
        $result = $this->configService->get($configId);
        
        self::assertInstanceOf(OptionItemResult::class, $result->config());
        self::assertEquals($configId, $result->config()->ID);
    }

    /**
     * Test get config with parameters
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetWithParameters(): void
    {
        // First, get any existing config from the list
        $listResult = $this->configService->getList(['ID'], ['ID' => 'ASC'], null, ['limit' => 1]);
        $options = $listResult->getOptions();
        
        self::assertGreaterThan(0, count($options), 'No open lines available to test get method');
        
        $configId = (int)$options[0]->ID;
        
        $result = $this->configService->get($configId, false, false);
        
        self::assertInstanceOf(OptionItemResult::class, $result->config());
    }

    /**
     * Test update config
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        // First, get any existing config from the list
        $listResult = $this->configService->getList(['ID'], ['ID' => 'ASC'], null, ['limit' => 1]);
        $options = $listResult->getOptions();
        
        self::assertGreaterThan(0, count($options), 'No open lines available to test update method');
        
        $configId = (int)$options[0]->ID;
        
        $params = [
            'LINE_NAME' => 'Updated Test Line ' . time()
        ];
        
        $result = $this->configService->update($configId, $params);
        
        self::assertTrue($result->isSuccess());
    }

    /**
     * Test delete config
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        // Create a config specifically for deletion test
        $timestamp = time();
        $params = [
            'LINE_NAME' => 'Test Line for Deletion ' . $timestamp,
            'ACTIVE' => true,
            'QUEUE_TYPE' => 'evenly',
            'CRM' => true,
            'CRM_CREATE_THIRD' => true,
            'CHECK_AVAILABLE' => false,
            'WATCH_TYPING' => true,
            'WELCOME_MESSAGE' => true,
            'VOTE_MESSAGE' => true,
            'LANGUAGE_ID' => 'en'
        ];

        $addResult = $this->configService->add($params);
        $configId = $addResult->getId();
        self::assertGreaterThan(0, $configId);
        
        $deleteResult = $this->configService->delete($configId);
        self::assertTrue($deleteResult->isSuccess());
    }



    protected function setUp(): void
    {
        $this->configService = Fabric::getServiceBuilder(true)->getIMOpenLinesScope()->config();
    }

    protected function tearDown(): void
    {
        // Clean up any created configs
        foreach ($this->createdConfigIds as $configId) {
            $this->deleteTestConfig($configId);
        }
        $this->createdConfigIds = [];
    }
}
