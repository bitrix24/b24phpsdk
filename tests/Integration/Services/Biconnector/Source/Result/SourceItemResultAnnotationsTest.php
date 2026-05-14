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

namespace Bitrix24\SDK\Tests\Integration\Services\Biconnector\Source\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Biconnector\Connector\Service\Connector;
use Bitrix24\SDK\Services\Biconnector\Source\Result\SourceItemResult;
use Bitrix24\SDK\Services\Biconnector\Source\Service\Source;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use Faker\Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Faker;

#[CoversClass(SourceItemResult::class)]
class SourceItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Source $sourceService;

    private Connector $connectorService;

    private Generator $faker;

    private int $connectorId;

    private int $sourceId;

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

        // Create a connector, then a source for annotation tests
        $this->connectorId = $this->connectorService->add($this->makeConnectorFields(
            'connector-annotations-' . $this->faker->uuid()
        ))->getId();

        $this->sourceId = $this->sourceService->add([
            'title'       => 'source-annotations-' . $this->faker->uuid(),
            'connectorId' => $this->connectorId,
            'settings'    => ['token' => 'test-token-' . $this->faker->uuid()],
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
            'urlCheck'           => 'http://example.com/api/check',
            'urlTableList'       => 'http://example.com/api/table_list',
            'urlTableDescription' => 'http://example.com/api/table_description',
            'urlData'            => 'http://example.com/api/data',
            'settings'           => [
                ['name' => 'Token', 'type' => 'STRING', 'code' => 'token'],
            ],
        ];
    }

    #[Test]
    #[TestDox('all fields in SourceItemResult are annotated and match live API fields schema')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $fieldCodes = $this->getSourceFieldCodes();

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            $fieldCodes,
            SourceItemResult::class
        );
    }

    #[Test]
    #[TestDox('all fields in SourceItemResult have valid type casting matching API fields schema')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $fieldTypesMap = $this->getSourceFieldTypesMap();

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $fieldTypesMap,
            SourceItemResult::class
        );
    }

    /**
     * Returns list of field codes from biconnector.source.fields API.
     *
     * @return array<int, string>
     */
    private function getSourceFieldCodes(): array
    {
        $raw = $this->sourceService->fields()->getFieldsDescription();
        $fields = $raw['fields'] ?? [];

        return array_column($fields, 'title');
    }

    /**
     * Returns field type map compatible with assertBitrix24AllResultItemFieldsHasValidTypeAnnotation.
     * Normalises biconnector-specific types to types known by the shared assertion.
     *
     * @return array<string, array{type: string}>
     */
    private function getSourceFieldTypesMap(): array
    {
        $raw = $this->sourceService->fields()->getFieldsDescription();
        $fields = $raw['fields'] ?? [];

        $result = [];
        foreach ($fields as $field) {
            $apiType = $field['type'];

            // biconnector uses 'boolean' — map to 'char' which the shared assertion handles as bool
            if ($apiType === 'boolean') {
                $apiType = 'char';
            }

            $result[$field['title']] = ['type' => $apiType];
        }

        return $result;
    }
}
