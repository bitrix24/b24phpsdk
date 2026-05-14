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
use Bitrix24\SDK\Services\Biconnector\Source\Service\Batch;
use Bitrix24\SDK\Services\Biconnector\Source\Service\Source;
use Bitrix24\SDK\Tests\Integration\Factory;
use Faker\Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Faker;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
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
        $builder = Factory::getServiceBuilder(true)->getBiconnectorScope();
        $this->sourceService = $builder->source();
        $this->connectorService = $builder->connector();
        $this->faker = Faker\Factory::create();

        // Create a connector to use for source tests
        $this->connectorId = $this->connectorService->add($this->makeConnectorFields(
            'connector-for-source-batch-' . $this->faker->uuid()
        ))->getId();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function tearDown(): void
    {
        try {
            $this->connectorService->delete($this->connectorId);
        } catch (\Throwable) {
            // Ignore cleanup errors
        }
    }

    private function makeConnectorFields(string $title): array
    {
        return [
            'title'              => $title,
            'logo'               => 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjIiIGhlaWdodD0iMjIiIHZpZXdCb3g9IjAgMCAyMiAyMiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KCTxjaXJjbGUgY3g9IjExIiBjeT0iMTEiIHI9IjEwIiBmaWxsPSIjRkYzQjNCIiAvPgoJPHRleHQgeD0iMTEiIHk9IjEzIiBmb250LWZhbWlseT0iQXJpYWwsIHNhbnMtc2VyaWYiIGZvbnQtc2l6ZT0iNiIgZmlsbD0iI0ZGRkZGRiIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZm9udC13ZWlnaHQ9ImJvbGQiPlJFU1Q8L3RleHQ+Cjwvc3ZnPg==',
            'urlCheck'           => 'http://example.com/api/check',
            'urlTableList'       => 'http://example.com/api/table_list',
            'urlTableDescription' => 'http://example.com/api/table_description',
            'urlData'            => 'http://example.com/api/data',
            'settings'           => [
                [
                    'name' => 'Token',
                    'type' => 'STRING',
                    'code' => 'token',
                ],
            ],
        ];
    }

    private function makeSourceFields(string $title): array
    {
        return [
            'title'       => $title,
            'connectorId' => $this->connectorId,
            'settings'    => [
                'token' => 'test-token-' . $this->faker->uuid(),
            ],
        ];
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchList(): void
    {
        $title = 'source-' . $this->faker->uuid();
        $id = $this->sourceService->add($this->makeSourceFields($title))->getId();

        $count = 0;
        foreach ($this->sourceService->batch->list([], [], [], 10) as $item) {
            self::assertInstanceOf(SourceItemResult::class, $item);
            $count++;
        }

        self::assertGreaterThanOrEqual(1, $count);

        // Cleanup
        $this->sourceService->delete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchAdd(): void
    {
        $sources = [];
        for ($i = 0; $i < 3; $i++) {
            $sources[] = $this->makeSourceFields('source-batch-' . $this->faker->uuid());
        }

        $addedIds = [];
        foreach ($this->sourceService->batch->add($sources) as $result) {
            $addedIds[] = $result->getId();
            self::assertGreaterThanOrEqual(1, $result->getId());
        }

        self::assertCount(3, $addedIds);

        // Cleanup
        foreach ($this->sourceService->batch->delete($addedIds) as $deleteResult) {
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
            $ids[] = $this->sourceService->add(
                $this->makeSourceFields('source-del-batch-' . $this->faker->uuid())
            )->getId();
        }

        foreach ($this->sourceService->batch->delete($ids) as $result) {
            self::assertTrue($result->isSuccess());
        }
    }
}

