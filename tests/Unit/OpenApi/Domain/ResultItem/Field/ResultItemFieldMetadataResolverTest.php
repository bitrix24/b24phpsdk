<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\OpenApi\Domain\ResultItem\Field;

use Bitrix24\SDK\OpenApi\Domain\ResultItem\Provider\OpenApiResultFieldProvider;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Field\ResultFieldCollection;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Field\ResultFieldDescriptor;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Field\ResultItemFieldMetadataRequest;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Field\ResultItemFieldMetadataResolver;
use RuntimeException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ResultItemFieldMetadataResolverTest extends TestCase
{
    #[Test]
    public function itUsesOpenApiMetadataWhenAvailable(): void
    {
        $resultItemFieldMetadataResolver = new ResultItemFieldMetadataResolver(
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

        $resultFieldCollection = $resultItemFieldMetadataResolver->resolve(new ResultItemFieldMetadataRequest(
            methodName: 'im.dialog.get',
            schemaFile: 'docs/open-api/openapi.json',
            entityKey: 'bitrix.example.dialogdto',
            webhook: 'https://portal.example/rest/1/token/',
        ));

        $this->assertSame('openapi', $resultFieldCollection->sourceName);
        $this->assertCount(1, $resultFieldCollection->fields);
        $this->assertSame('id', $resultFieldCollection->fields[0]->name);
    }

    #[Test]
    public function itFailsFastWhenOpenApiMetadataIsUnavailable(): void
    {
        $resultItemFieldMetadataResolver = new ResultItemFieldMetadataResolver(
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

        $resultItemFieldMetadataResolver->resolve(new ResultItemFieldMetadataRequest(
            methodName: 'im.dialog.get',
            schemaFile: 'docs/open-api/openapi.json',
            entityKey: null,
            webhook: 'https://portal.example/rest/1/token/',
        ));
    }
}
