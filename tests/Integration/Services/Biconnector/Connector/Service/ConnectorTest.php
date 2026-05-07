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
use Bitrix24\SDK\Services\Biconnector\Connector\Service\Connector;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use Faker\Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Faker;

#[CoversClass(Connector::class)]
class ConnectorTest extends TestCase
{
    use CustomBitrix24Assertions;

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
    public function testAdd(): void
    {
        $name = 'connector-' . $this->faker->uuid();
        $code = 'code_' . substr($this->faker->uuid(), 0, 8);
        $id = $this->connectorService->add([
            'name' => $name,
            'code' => $code,
        ])->getId();

        self::assertGreaterThanOrEqual(1, $id);

        // Cleanup
        $this->connectorService->delete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $name = 'connector-' . $this->faker->uuid();
        $code = 'code_' . substr($this->faker->uuid(), 0, 8);
        $id = $this->connectorService->add([
            'name' => $name,
            'code' => $code,
        ])->getId();

        $connectorItemResult = $this->connectorService->get($id)->connector();
        self::assertInstanceOf(ConnectorItemResult::class, $connectorItemResult);
        self::assertEquals($id, $connectorItemResult->id);
        self::assertEquals($name, $connectorItemResult->name);

        // Cleanup
        $this->connectorService->delete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $name = 'connector-' . $this->faker->uuid();
        $code = 'code_' . substr($this->faker->uuid(), 0, 8);
        $id = $this->connectorService->add([
            'name' => $name,
            'code' => $code,
        ])->getId();

        $list = $this->connectorService->list()->getConnectors();
        self::assertIsArray($list);
        self::assertGreaterThanOrEqual(1, count($list));

        // Cleanup
        $this->connectorService->delete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $name = 'connector-' . $this->faker->uuid();
        $code = 'code_' . substr($this->faker->uuid(), 0, 8);
        $id = $this->connectorService->add([
            'name' => $name,
            'code' => $code,
        ])->getId();

        $newName = $name . '-updated';
        self::assertTrue(
            $this->connectorService->update($id, [
                'name' => $newName,
            ])->isSuccess()
        );

        self::assertEquals($newName, $this->connectorService->get($id)->connector()->name);

        // Cleanup
        $this->connectorService->delete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $name = 'connector-' . $this->faker->uuid();
        $code = 'code_' . substr($this->faker->uuid(), 0, 8);
        $id = $this->connectorService->add([
            'name' => $name,
            'code' => $code,
        ])->getId();

        self::assertTrue($this->connectorService->delete($id)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testFields(): void
    {
        $fields = $this->connectorService->fields()->getFieldsDescription();
        self::assertIsArray($fields);
        self::assertNotEmpty($fields);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testCount(): void
    {
        $countBefore = $this->connectorService->count();

        $name = 'connector-' . $this->faker->uuid();
        $code = 'code_' . substr($this->faker->uuid(), 0, 8);
        $id = $this->connectorService->add([
            'name' => $name,
            'code' => $code,
        ])->getId();

        $countAfter = $this->connectorService->count();
        self::assertEquals($countBefore + 1, $countAfter);

        // Cleanup
        $this->connectorService->delete($id);
    }
}
