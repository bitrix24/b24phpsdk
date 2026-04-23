<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\OpenApi\Domain;

use Bitrix24\SDK\OpenApi\Domain\ResultItem\OpenApiResultFieldProvider;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\ResultFieldCollection;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\ResultFieldDescriptor;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\ResultItemFieldMetadataRequest;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\ResultItemFieldMetadataResolver;
use RuntimeException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ResultItemFieldMetadataResolverTest extends TestCase
{
    #[Test]
    public function itUsesOpenApiMetadataWhenAvailable(): void
    {
        $resolver = new ResultItemFieldMetadataResolver(
            new class extends OpenApiResultFieldProvider {
                public function __construct() {}

                #[\Override]
                public function provide(string $schemaFile, ?string $entityKey): ?ResultFieldCollection
                {
                    return new ResultFieldCollection([
                        new ResultFieldDescriptor('id', 'integer'),
                    ], 'openapi');
                }
            }
        );

        $fieldCollection = $resolver->resolve(new ResultItemFieldMetadataRequest(
            methodName: 'im.dialog.get',
            schemaFile: 'docs/open-api/openapi.json',
            entityKey: 'bitrix.example.dialogdto',
            webhook: 'https://portal.example/rest/1/token/',
        ));

        $this->assertSame('openapi', $fieldCollection->sourceName);
        $this->assertCount(1, $fieldCollection->fields);
        $this->assertSame('id', $fieldCollection->fields[0]->name);
    }

    #[Test]
    public function itFailsFastWhenOpenApiMetadataIsUnavailable(): void
    {
        $resolver = new ResultItemFieldMetadataResolver(
            new class extends OpenApiResultFieldProvider {
                public function __construct() {}

                #[\Override]
                public function provide(string $schemaFile, ?string $entityKey): ?ResultFieldCollection
                {
                    return null;
                }
            }
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Unable to resolve result field metadata for method "im.dialog.get" from OpenAPI'
        );

        $resolver->resolve(new ResultItemFieldMetadataRequest(
            methodName: 'im.dialog.get',
            schemaFile: 'docs/open-api/openapi.json',
            entityKey: null,
            webhook: 'https://portal.example/rest/1/token/',
        ));
    }
}
