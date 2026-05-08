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

namespace Bitrix24\SDK\Tests\Integration\Services\Biconnector\Connector\Result;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\Biconnector\Connector\Result\ConnectorItemResult;
use Bitrix24\SDK\Services\Biconnector\Connector\Service\Connector;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConnectorItemResult::class)]
class ConnectorItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Connector $connectorService;

    /**
     * @throws InvalidArgumentException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->connectorService = Factory::getServiceBuilder()->getBiconnectorScope()->connector();
    }

    #[Test]
    #[TestDox('all fields in ConnectorItemResult are annotated and match live API fields schema')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $fieldCodes = $this->getConnectorFieldCodes();

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            $fieldCodes,
            ConnectorItemResult::class
        );
    }

    #[Test]
    #[TestDox('all fields in ConnectorItemResult have valid type casting matching API fields schema')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $fieldTypesMap = $this->getConnectorFieldTypesMap();

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $fieldTypesMap,
            ConnectorItemResult::class
        );
    }

    /**
     * Returns list of field codes from biconnector.connector.fields API.
     *
     * @return array<int, string>
     */
    private function getConnectorFieldCodes(): array
    {
        $raw = $this->connectorService->fields()->getFieldsDescription();
        $fields = $raw['fields'] ?? [];

        return array_column($fields, 'title');
    }

    /**
     * Returns field type map compatible with assertBitrix24AllResultItemFieldsHasValidTypeAnnotation.
     * Normalises biconnector-specific types to types known by the shared assertion.
     *
     * @return array<string, array{type: string}>
     */
    private function getConnectorFieldTypesMap(): array
    {
        $raw = $this->connectorService->fields()->getFieldsDescription();
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
