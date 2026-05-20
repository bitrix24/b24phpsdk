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

namespace Bitrix24\SDK\Tests\Integration\Services\Biconnector\Connector\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Biconnector\Connector\Result\ConnectorItemResult;
use Bitrix24\SDK\Services\Biconnector\Connector\Service\Batch;
use Bitrix24\SDK\Services\Biconnector\Connector\Service\Connector;
use Bitrix24\SDK\Tests\Integration\Factory;
use Faker\Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Faker;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private Connector $connectorService;

    private Generator $faker;

    /**
     * @throws InvalidArgumentException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->connectorService = Factory::getServiceBuilder(true)->getBiconnectorScope()->connector();
        $this->faker = Faker\Factory::create();
    }

    /**
     * Returns the minimum set of required fields to create a connector.
     *
     * @return array{
     *   title: string,
     *   logo: string,
     *   urlCheck: string,
     *   urlData: string,
     *   urlTableList: string,
     *   urlTableDescription: string,
     *   settings: array,
     * }
     */
    private function makeConnectorFields(string $title): array
    {
        return [
            'title'               => $title,
            'logo'                => 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjIiIGhlaWdodD0iMjIiIHZpZXdCb3g9IjAgMCAyMiAyMiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KCTxjaXJjbGUgY3g9IjExIiBjeT0iMTEiIHI9IjEwIiBmaWxsPSIjRkYzQjNCIiAvPgoJPHRleHQgeD0iMTEiIHk9IjEzIiBmb250LWZhbWlseT0iQXJpYWwsIHNhbnMtc2VyaWYiIGZvbnQtc2l6ZT0iNiIgZmlsbD0iI0ZGRkZGRiIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZm9udC13ZWlnaHQ9ImJvbGQiPlJFU1Q8L3RleHQ+Cjwvc3ZnPg==',
            'urlCheck'           => 'https://example.com/api/check',
            'urlTableList'       => 'https://example.com/api/table_list',
            'urlTableDescription' => 'https://example.com/api/table_description',
            'urlData'            => 'https://example.com/api/data',
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
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchList(): void
    {
        $title = 'connector-' . $this->faker->uuid();
        $id = $this->connectorService->add($this->makeConnectorFields($title))->getId();

        $count = 0;
        foreach ($this->connectorService->batch->list([], [], [], 10) as $item) {
            self::assertInstanceOf(ConnectorItemResult::class, $item);
            $count++;
        }

        self::assertGreaterThanOrEqual(1, $count);

        // Cleanup
        $this->connectorService->delete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchAdd(): void
    {
        $connectors = [];
        for ($i = 0; $i < 3; $i++) {
            $connectors[] = $this->makeConnectorFields('connector-batch-' . $this->faker->uuid());
        }

        $addedIds = [];
        foreach ($this->connectorService->batch->add($connectors) as $result) {
            $addedIds[] = $result->getId();
            self::assertGreaterThanOrEqual(1, $result->getId());
        }

        self::assertCount(3, $addedIds);

        // Cleanup
        foreach ($this->connectorService->batch->delete($addedIds) as $deleteResult) {
            self::assertTrue($deleteResult->isSuccess());
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchDelete(): void
    {
        $ids = [];
        for ($i = 0; $i < 2; $i++) {
            $ids[] = $this->connectorService->add(
                $this->makeConnectorFields('connector-del-batch-' . $this->faker->uuid())
            )->getId();
        }

        foreach ($this->connectorService->batch->delete($ids) as $result) {
            self::assertTrue($result->isSuccess());
        }
    }
}
