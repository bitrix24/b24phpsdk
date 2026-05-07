<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <titarx@gmail.com>
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
        $this->connectorService = Factory::getServiceBuilder()->getBiconnectorScope()->connector();
        $this->faker = Faker\Factory::create();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchList(): void
    {
        $name = 'connector-' . $this->faker->uuid();
        $code = 'code_' . substr($this->faker->uuid(), 0, 8);
        $id = $this->connectorService->add([
            'name' => $name,
            'code' => $code,
        ])->getId();

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
        $codes = [];
        for ($i = 0; $i < 3; $i++) {
            $code = 'code_' . substr($this->faker->uuid(), 0, 8);
            $codes[] = $code;
            $connectors[] = [
                'name' => 'connector-batch-' . $this->faker->uuid(),
                'code' => $code,
            ];
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
            $ids[] = $this->connectorService->add([
                'name' => 'connector-del-batch-' . $this->faker->uuid(),
                'code' => 'code_' . substr($this->faker->uuid(), 0, 8),
            ])->getId();
        }

        foreach ($this->connectorService->batch->delete($ids) as $result) {
            self::assertTrue($result->isSuccess());
        }
    }
}
