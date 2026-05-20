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

namespace Bitrix24\SDK\Tests\Integration\Services\Biconnector\Dataset\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Biconnector\Connector\Service\Connector;
use Bitrix24\SDK\Services\Biconnector\Dataset\Result\DatasetItemResult;
use Bitrix24\SDK\Services\Biconnector\Dataset\Service\Dataset;
use Bitrix24\SDK\Services\Biconnector\Source\Service\Source;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use Faker\Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Faker;

#[CoversClass(DatasetItemResult::class)]
class DatasetItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Dataset $datasetService;

    private Source $sourceService;

    private Connector $connectorService;

    private Generator $faker;

    private int $connectorId;

    private int $sourceId;

    private int $datasetId;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $biconnectorServiceBuilder = Factory::getServiceBuilder(true)->getBiconnectorScope();
        $this->datasetService = $biconnectorServiceBuilder->dataset();
        $this->sourceService = $biconnectorServiceBuilder->source();
        $this->connectorService = $biconnectorServiceBuilder->connector();
        $this->faker = Faker\Factory::create();

        // Create connector, source, and dataset for annotation tests
        $this->connectorId = $this->connectorService->add($this->makeConnectorFields(
            'connector-annotations-' . $this->faker->uuid()
        ))->getId();

        $this->sourceId = $this->sourceService->add([
            'title'       => 'source-annotations-' . $this->faker->uuid(),
            'connectorId' => $this->connectorId,
            'settings'    => [
                'host'     => '172.18.0.2',
                'port'     => '3306',
                'database' => 'customer_db',
                'username' => 'testuser',
                'password' => 'testpass123',
            ],
        ])->getId();

        $name = 'ds' . substr(str_replace('-', '', $this->faker->uuid()), 0, 20);
        $this->datasetId = $this->datasetService->add([
            'sourceId'     => $this->sourceId,
            'name'         => $name,
            'externalName' => 'order_items',
            'externalCode' => 'order_items',
            'fields'       => [
                ['type' => 'int', 'name' => 'ID', 'externalCode' => 'ID'],
            ],
        ])->getId();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function tearDown(): void
    {
        try {
            $this->datasetService->delete($this->datasetId);
        } catch (\Throwable) {
        }

        try {
            $this->sourceService->delete($this->sourceId);
        } catch (\Throwable) {
        }

        try {
            $this->connectorService->delete($this->connectorId);
        } catch (\Throwable) {
        }
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

    #[Test]
    #[TestDox('all fields in DatasetItemResult are annotated and match live API fields schema')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $fieldCodes = $this->getDatasetFieldCodes();

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            $fieldCodes,
            DatasetItemResult::class
        );
    }

    #[Test]
    #[TestDox('all fields in DatasetItemResult have valid type casting matching API fields schema')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $fieldTypesMap = $this->getDatasetFieldTypesMap();

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $fieldTypesMap,
            DatasetItemResult::class
        );
    }

    /**
     * Returns list of field codes from biconnector.dataset.fields API.
     *
     * @return array<int, string>
     */
    private function getDatasetFieldCodes(): array
    {
        $raw = $this->datasetService->fields()->getFieldsDescription();
        $fields = $raw['fields'] ?? [];

        return array_column($fields, 'title');
    }

    /**
     * Returns field type map compatible with assertBitrix24AllResultItemFieldsHasValidTypeAnnotation.
     *
     * @return array<string, array{type: string}>
     */
    private function getDatasetFieldTypesMap(): array
    {
        $raw = $this->datasetService->fields()->getFieldsDescription();
        $fields = $raw['fields'] ?? [];

        $result = [];
        foreach ($fields as $field) {
            $result[$field['title']] = ['type' => $field['type']];
        }

        return $result;
    }
}

