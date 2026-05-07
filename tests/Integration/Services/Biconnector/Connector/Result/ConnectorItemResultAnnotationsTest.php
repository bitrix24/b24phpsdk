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
use Faker\Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Faker;

#[CoversClass(ConnectorItemResult::class)]
class ConnectorItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Connector $connectorService;

    private Generator $faker;

    private int $createdConnectorId;

    /**
     * @throws InvalidArgumentException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->connectorService = Factory::getServiceBuilder()->getBiconnectorScope()->connector();
        $this->faker = Faker\Factory::create();

        // Create a connector to use in tests
        $this->createdConnectorId = $this->connectorService->add([
            'name' => 'test-annotations-' . $this->faker->uuid(),
            'code' => 'code_' . substr($this->faker->uuid(), 0, 8),
        ])->getId();
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->connectorService->delete($this->createdConnectorId);
    }

    #[Test]
    #[TestDox('all fields in ConnectorItemResult are annotated and match live API response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $rawItem = $this->connectorService->get($this->createdConnectorId)
            ->getCoreResponse()
            ->getResponseData()
            ->getResult();

        // Handle nested envelope: result['connector'] or result directly
        if (!empty($rawItem['connector']) && is_array($rawItem['connector'])) {
            $rawItem = $rawItem['connector'];
        }

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawItem),
            ConnectorItemResult::class
        );
    }

    #[Test]
    #[TestDox('all fields in ConnectorItemResult have valid type casting in magic getters')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $connectorItemResult = $this->connectorService->get($this->createdConnectorId)->connector();

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $connectorItemResult,
            ConnectorItemResult::class
        );
    }
}
