<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Biconnector\Source\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Biconnector\Connector\Service\Connector;
use Bitrix24\SDK\Services\Biconnector\Source\Result\SourceItemResult;
use Bitrix24\SDK\Services\Biconnector\Source\Service\Source;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use Faker\Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Faker;

#[CoversClass(Source::class)]
class SourceTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Source $sourceService;

    private Connector $connectorService;

    private Generator $faker;

    private int $connectorId;

    /**
     * @throws InvalidArgumentException
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $builder = Fabric::getServiceBuilder(true)->getBiconnectorScope();
        $this->sourceService = $builder->source();
        $this->connectorService = $builder->connector();
        $this->faker = Faker\Factory::create();

        // Create a connector to use for source tests
        $this->connectorId = $this->connectorService->add($this->makeConnectorFields(
            'connector-for-source-' . $this->faker->uuid()
        ))->getId();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function tearDown(): void
    {
        // Clean up the connector created for tests
        try {
            $this->connectorService->delete($this->connectorId);
        } catch (\Throwable) {
            // Ignore cleanup errors
        }
    }

    /**
     * Returns the minimum set of required fields to create a connector.
     */
    private function makeConnectorFields(string $title): array
    {
        return [
            'title'              => $title,
            'logo'               => 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjIiIGhlaWdodD0iMjIiIHZpZXdCb3g9IjAgMCAyMiAyMiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KCTxjaXJjbGUgY3g9IjExIiBjeT0iMTEiIHI9IjEwIiBmaWxsPSIjRkYzQjNCIiAvPgoJPHRleHQgeD0iMTEiIHk9IjEzIiBmb250LWZhbWlseT0iQXJpYWwsIHNhbnMtc2VyaWYiIGZvbnQtc2l6ZT0iNiIgZmlsbD0iI0ZGRkZGRiIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZm9udC13ZWlnaHQ9ImJvbGQiPlJFU1Q8L3RleHQ+Cjwvc3ZnPg==',
            'urlCheck'           => 'https://digitmind8080.cloudpub.ru/?connection_type=mysql&action=check',
            'urlTableList'       => 'https://digitmind8080.cloudpub.ru/?connection_type=mysql&action=table_list',
            'urlTableDescription' => 'https://digitmind8080.cloudpub.ru/?connection_type=mysql&action=table_description',
            'urlData'            => 'https://digitmind8080.cloudpub.ru/?connection_type=mysql&action=data',
            'settings'           => [
                [
                    'name' => 'Host',
                    'type' => 'STRING',
                    'code' => 'host',
                ],
                [
                    'name' => 'Port',
                    'type' => 'STRING',
                    'code' => 'port',
                ],
                [
                    'name' => 'Database',
                    'type' => 'STRING',
                    'code' => 'database',
                ],
                [
                    'name' => 'Username',
                    'type' => 'STRING',
                    'code' => 'username',
                ],
                [
                    'name' => 'Password',
                    'type' => 'STRING',
                    'code' => 'password',
                ],
            ],
        ];
    }

    /**
     * Returns the minimum set of required fields to create a source.
     */
    private function makeSourceFields(string $title): array
    {
        return [
            'title'       => $title,
            'connectorId' => $this->connectorId,
            'settings'    => [
                'host'     => '172.18.0.2',
                'port'     => '3306',
                'database' => 'customer_db',
                'username' => 'testuser',
                'password' => 'testpass123',
            ],
        ];
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        // Test body is commented out: this test requires an additional external service
        // (a real database accessible via the Biconnector connector).
        $this->markTestSkipped('This test requires an additional external service (a real database accessible via the Biconnector connector).');
        /*
        $title = 'source-' . $this->faker->uuid();
        $id = $this->sourceService->add($this->makeSourceFields($title))->getId();

        self::assertGreaterThanOrEqual(1, $id);

        // Cleanup
        $this->sourceService->delete($id);
        */
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        // Test body is commented out: this test requires an additional external service
        // (a real database accessible via the Biconnector connector).
        $this->markTestSkipped('This test requires an additional external service (a real database accessible via the Biconnector connector).');
        /*
        $title = 'source-' . $this->faker->uuid();
        $id = $this->sourceService->add($this->makeSourceFields($title))->getId();

        $sourceItemResult = $this->sourceService->get($id)->source();
        self::assertInstanceOf(SourceItemResult::class, $sourceItemResult);
        self::assertEquals($id, $sourceItemResult->id);
        self::assertEquals($title, $sourceItemResult->title);

        // Cleanup
        $this->sourceService->delete($id);
        */
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        // Test body is commented out: this test requires an additional external service
        // (a real database accessible via the Biconnector connector).
        $this->markTestSkipped('This test requires an additional external service (a real database accessible via the Biconnector connector).');
        /*
        $title = 'source-' . $this->faker->uuid();
        $id = $this->sourceService->add($this->makeSourceFields($title))->getId();

        $list = $this->sourceService->list()->getSources();
        self::assertIsArray($list);
        self::assertGreaterThanOrEqual(1, count($list));

        // Cleanup
        $this->sourceService->delete($id);
        */
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        // Test body is commented out: this test requires an additional external service
        // (a real database accessible via the Biconnector connector).
        $this->markTestSkipped('This test requires an additional external service (a real database accessible via the Biconnector connector).');
        /*
        $title = 'source-' . $this->faker->uuid();
        $sourceFields = $this->makeSourceFields($title);
        $id = $this->sourceService->add($sourceFields)->getId();

        $newTitle = $title . '-updated';
        self::assertTrue(
            $this->sourceService->update($id, [
                'title'    => $newTitle,
                'settings' => $sourceFields['settings'],
            ])->isSuccess()
        );

        self::assertEquals($newTitle, $this->sourceService->get($id)->source()->title);

        // Cleanup
        $this->sourceService->delete($id);
        */
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        // Test body is commented out: this test requires an additional external service
        // (a real database accessible via the Biconnector connector).
        $this->markTestSkipped('This test requires an additional external service (a real database accessible via the Biconnector connector).');
        /*
        $title = 'source-' . $this->faker->uuid();
        $id = $this->sourceService->add($this->makeSourceFields($title))->getId();

        self::assertTrue($this->sourceService->delete($id)->isSuccess());
        */
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testFields(): void
    {
        // Test body is commented out: this test requires an additional external service
        // (a real database accessible via the Biconnector connector).
        $this->markTestSkipped('This test requires an additional external service (a real database accessible via the Biconnector connector).');
        /*
        $fields = $this->sourceService->fields()->getFieldsDescription();
        self::assertIsArray($fields);
        self::assertNotEmpty($fields);
        */
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testCount(): void
    {
        // Test body is commented out: this test requires an additional external service
        // (a real database accessible via the Biconnector connector).
        $this->markTestSkipped('This test requires an additional external service (a real database accessible via the Biconnector connector).');
        /*
        $countBefore = $this->sourceService->count();

        $title = 'source-' . $this->faker->uuid();
        $id = $this->sourceService->add($this->makeSourceFields($title))->getId();

        $countAfter = $this->sourceService->count();
        self::assertEquals($countBefore + 1, $countAfter);

        // Cleanup
        $this->sourceService->delete($id);
        */
    }
}
