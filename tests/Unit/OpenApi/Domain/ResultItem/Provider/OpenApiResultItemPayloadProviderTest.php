<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\OpenApi\Domain\ResultItem\Provider;

use Bitrix24\SDK\OpenApi\Domain\ResultItem\Provider\OpenApiResultItemPayloadProvider;
use Bitrix24\SDK\OpenApi\Domain\OpenApiSchemaEntityReader;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\PhpDoc\ResultItemPhpDocTypeResolver;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

final class OpenApiResultItemPayloadProviderTest extends TestCase
{
    private const string SCHEMA_FIXTURE = __DIR__ . '/../Fixtures/result-item-openapi.json';

    #[Test]
    public function itBuildsResultItemPayloadFromOpenApiEntityMetadata(): void
    {
        $openApiResultItemPayloadProvider = new OpenApiResultItemPayloadProvider(
            new OpenApiSchemaEntityReader(new Filesystem()),
            new ResultItemPhpDocTypeResolver(),
        );

        $resultItemPayload = $openApiResultItemPayloadProvider->provide(
            schemaFile: self::SCHEMA_FIXTURE,
            method: 'im.dialog.get',
            entityKey: 'bitrix.example.dialogdto',
        );

        self::assertSame('im.dialog.get', $resultItemPayload->method);
        self::assertSame('result-item', $resultItemPayload->object);
        self::assertSame(['openapi'], $resultItemPayload->generatedFrom);
        self::assertCount(7, $resultItemPayload->fields);
        self::assertSame([], $resultItemPayload->sections);

        $id = $this->findField($resultItemPayload->fields, 'id');
        self::assertNotNull($id);
        self::assertTrue($id->required);
        self::assertFalse($id->nullable);

        $dateCreate = $this->findField($resultItemPayload->fields, 'date_create');
        self::assertNotNull($dateCreate);
        self::assertSame('datetime', $dateCreate->sourceType);
        self::assertSame(CarbonImmutable::class, $dateCreate->phpdocType);
        self::assertSame('date-time', $dateCreate->format);
        self::assertTrue($dateCreate->required);
        self::assertFalse($dateCreate->nullable);
        self::assertSame('openapi', $dateCreate->source);
        self::assertSame('Created at', $dateCreate->description);

        $birthday = $this->findField($resultItemPayload->fields, 'birthday');
        self::assertNotNull($birthday);
        self::assertSame('date', $birthday->format);
        self::assertSame(CarbonImmutable::class, $birthday->phpdocType);
        self::assertFalse($birthday->required);
        self::assertFalse($birthday->nullable);

        $backgroundId = $this->findField($resultItemPayload->fields, 'background_id');
        self::assertNotNull($backgroundId);
        self::assertFalse($backgroundId->required);
        self::assertTrue($backgroundId->nullable);
        self::assertSame('int|null', $backgroundId->phpdocType);

        $profile = $this->findField($resultItemPayload->fields, 'profile');
        self::assertNotNull($profile);
        self::assertSame('object', $profile->sourceType);
        self::assertSame('array', $profile->phpdocType);
        self::assertFalse($profile->required);
        self::assertFalse($profile->nullable);
    }

    /**
     * @param list<\Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload\ResultItemPayloadField> $fields
     */
    private function findField(array $fields, string $code): ?\Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload\ResultItemPayloadField
    {
        foreach ($fields as $field) {
            if ($field->code === $code) {
                return $field;
            }
        }

        return null;
    }
}
