<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\OpenApi\Domain;

use Bitrix24\SDK\OpenApi\Domain\ResultItem\OpenApiResultFieldProvider;
use Bitrix24\SDK\OpenApi\Domain\Schema\OpenApiSchemaEntityReader;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class OpenApiResultFieldProviderTest extends TestCase
{
    private const string SCHEMA_FIXTURE = __DIR__ . '/Fixtures/result-item-openapi.json';

    #[Test]
    public function itBuildsResultFieldDescriptorsFromEntityProperties(): void
    {
        $provider = new OpenApiResultFieldProvider(
            new OpenApiSchemaEntityReader(new Filesystem())
        );

        $fieldCollection = $provider->provide(self::SCHEMA_FIXTURE, 'bitrix.example.dialogdto');

        $this->assertNotNull($fieldCollection);
        $this->assertSame('openapi', $fieldCollection->sourceName);
        $this->assertCount(7, $fieldCollection->fields);

        $dateCreate = $this->findField($fieldCollection->fields, 'date_create');
        $this->assertNotNull($dateCreate);
        $this->assertSame('string', $dateCreate->type);
        $this->assertSame('date-time', $dateCreate->format);

        $backgroundId = $this->findField($fieldCollection->fields, 'background_id');
        $this->assertNotNull($backgroundId);
        $this->assertTrue($backgroundId->nullable);
    }

    /**
     * @param list<\Bitrix24\SDK\OpenApi\Domain\ResultItem\ResultFieldDescriptor> $fields
     */
    private function findField(array $fields, string $name): ?\Bitrix24\SDK\OpenApi\Domain\ResultItem\ResultFieldDescriptor
    {
        foreach ($fields as $field) {
            if ($field->name === $name) {
                return $field;
            }
        }

        return null;
    }
}
