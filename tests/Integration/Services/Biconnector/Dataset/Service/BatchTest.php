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

namespace Bitrix24\SDK\Tests\Integration\Services\Biconnector\Dataset\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Biconnector\Connector\Service\Connector;
use Bitrix24\SDK\Services\Biconnector\Dataset\Result\DatasetItemResult;
use Bitrix24\SDK\Services\Biconnector\Dataset\Service\Batch;
use Bitrix24\SDK\Services\Biconnector\Dataset\Service\Dataset;
use Bitrix24\SDK\Services\Biconnector\Source\Service\Source;
use Bitrix24\SDK\Tests\Integration\Factory;
use Faker\Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Faker;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private Dataset $datasetService;

    private Source $sourceService;

    private Connector $connectorService;

    private Generator $faker;

    private int $connectorId;

    private int $sourceId;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        // setUp body is commented out: this test class requires an additional external service
        // (a real database accessible via the Biconnector connector).
        $this->markTestSkipped('This test requires an additional external service (a real database accessible via the Biconnector connector).');
        /*
        $biconnectorServiceBuilder = Factory::getServiceBuilder(true)->getBiconnectorScope();
        $this->datasetService = $biconnectorServiceBuilder->dataset();
        $this->sourceService = $biconnectorServiceBuilder->source();
        $this->connectorService = $biconnectorServiceBuilder->connector();
        $this->faker = Faker\Factory::create();

        // Create a connector and source to use for dataset batch tests
        $this->connectorId = $this->connectorService->add($this->makeConnectorFields(
            'connector-for-dataset-batch-' . $this->faker->uuid()
        ))->getId();

        $this->sourceId = $this->sourceService->add([
            'title'       => 'source-for-dataset-batch-' . $this->faker->uuid(),
            'connectorId' => $this->connectorId,
            'settings'    => [
                'host'     => '172.18.0.2',
                'port'     => '3306',
                'database' => 'customer_db',
                'username' => 'testuser',
                'password' => 'testpass123',
            ],
        ])->getId();
        */
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function tearDown(): void
    {
        // tearDown body is commented out: this test class requires an additional external service
        // (a real database accessible via the Biconnector connector).
        /*
        try {
            $this->sourceService->delete($this->sourceId);
        } catch (\Throwable) {
        }

        try {
            $this->connectorService->delete($this->connectorId);
        } catch (\Throwable) {
        }
        */
    }

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
                ['name' => 'Host', 'type' => 'STRING', 'code' => 'host'],
                ['name' => 'Port', 'type' => 'STRING', 'code' => 'port'],
                ['name' => 'Database', 'type' => 'STRING', 'code' => 'database'],
                ['name' => 'Username', 'type' => 'STRING', 'code' => 'username'],
                ['name' => 'Password', 'type' => 'STRING', 'code' => 'password'],
            ],
        ];
    }

    private function makeDatasetFields(string $name): array
    {
        return [
            'sourceId'     => $this->sourceId,
            'name'         => $name,
            'externalName' => 'order_items',
            'externalCode' => 'order_items',
            'fields'       => [
                ['type' => 'int', 'name' => 'ID', 'externalCode' => 'ID'],
            ],
        ];
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchList(): void
    {
        // Test body is commented out: this test requires an additional external service
        // (a real database accessible via the Biconnector connector).
        $this->markTestSkipped('This test requires an additional external service (a real database accessible via the Biconnector connector).');
        /*
        $name = 'ds' . substr(str_replace('-', '', $this->faker->uuid()), 0, 20);
        $id = $this->datasetService->add($this->makeDatasetFields($name))->getId();

        $count = 0;
        foreach ($this->datasetService->batch->list([], [], [], 10) as $item) {
            self::assertInstanceOf(DatasetItemResult::class, $item);
            $count++;
        }

        self::assertGreaterThanOrEqual(1, $count);

        // Cleanup
        $this->datasetService->delete($id);
        */
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchAdd(): void
    {
        // Test body is commented out: this test requires an additional external service
        // (a real database accessible via the Biconnector connector).
        $this->markTestSkipped('This test requires an additional external service (a real database accessible via the Biconnector connector).');
        /*
        $datasets = [];
        for ($i = 0; $i < 3; $i++) {
            $name = 'ds' . substr(str_replace('-', '', $this->faker->uuid()), 0, 20);
            $datasets[] = $this->makeDatasetFields($name);
        }

        $addedIds = [];
        foreach ($this->datasetService->batch->add($datasets) as $result) {
            $addedIds[] = $result->getId();
            self::assertGreaterThanOrEqual(1, $result->getId());
        }

        self::assertCount(3, $addedIds);

        // Cleanup
        foreach ($this->datasetService->batch->delete($addedIds) as $deleteResult) {
            self::assertTrue($deleteResult->isSuccess());
        }
        */
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchDelete(): void
    {
        // Test body is commented out: this test requires an additional external service
        // (a real database accessible via the Biconnector connector).
        $this->markTestSkipped('This test requires an additional external service (a real database accessible via the Biconnector connector).');
        /*
        $ids = [];
        for ($i = 0; $i < 2; $i++) {
            $name = 'ds' . substr(str_replace('-', '', $this->faker->uuid()), 0, 20);
            $ids[] = $this->datasetService->add($this->makeDatasetFields($name))->getId();
        }

        foreach ($this->datasetService->batch->delete($ids) as $result) {
            self::assertTrue($result->isSuccess());
        }
        */
    }
}
