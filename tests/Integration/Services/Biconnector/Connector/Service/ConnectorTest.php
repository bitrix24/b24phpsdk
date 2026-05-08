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
            'urlCheck'             => 'http://example.com/api/check',
            'urlTableList'         => 'http://example.com/api/table_list',
            'urlTableDescription'  => 'http://example.com/api/table_description',
            'urlData'              => 'http://example.com/api/data',
            'settings'             => [
                [
                    'name' => 'Login',
                    'type' => 'STRING',
                    'code' => 'login',
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
    public function testAdd(): void
    {
        $title = 'connector-' . $this->faker->uuid();
        $id = $this->connectorService->add($this->makeConnectorFields($title))->getId();

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
        $title = 'connector-' . $this->faker->uuid();
        $id = $this->connectorService->add($this->makeConnectorFields($title))->getId();

        $connectorItemResult = $this->connectorService->get($id)->connector();
        self::assertInstanceOf(ConnectorItemResult::class, $connectorItemResult);
        self::assertEquals($id, $connectorItemResult->id);
        self::assertEquals($title, $connectorItemResult->title);

        // Cleanup
        $this->connectorService->delete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $title = 'connector-' . $this->faker->uuid();
        $id = $this->connectorService->add($this->makeConnectorFields($title))->getId();

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
        $title = 'connector-' . $this->faker->uuid();
        $id = $this->connectorService->add($this->makeConnectorFields($title))->getId();

        $newTitle = $title . '-updated';
        self::assertTrue(
            $this->connectorService->update($id, [
                'title' => $newTitle,
            ])->isSuccess()
        );

        self::assertEquals($newTitle, $this->connectorService->get($id)->connector()->title);

        // Cleanup
        $this->connectorService->delete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $title = 'connector-' . $this->faker->uuid();
        $id = $this->connectorService->add($this->makeConnectorFields($title))->getId();

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

        $title = 'connector-' . $this->faker->uuid();
        $id = $this->connectorService->add($this->makeConnectorFields($title))->getId();

        $countAfter = $this->connectorService->count();
        self::assertEquals($countBefore + 1, $countAfter);

        // Cleanup
        $this->connectorService->delete($id);
    }
}
