<?php

declare(strict_types=1);

namespace Bitrix24\SDK\OpenApi\Domain\ResultItem\Provider;

use Bitrix24\SDK\OpenApi\Domain\OpenApiSchemaEntityReader;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Field\ResultFieldDescriptor;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload\ResultItemPayload;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload\ResultItemPayloadField;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\PhpDoc\ResultItemPhpDocTypeResolver;

final class OpenApiResultItemPayloadProvider
{
    public function __construct(
        private readonly OpenApiSchemaEntityReader $schemaEntityReader,
        private readonly ResultItemPhpDocTypeResolver $typeResolver,
    ) {
    }

    public function provide(string $schemaFile, string $method, string $entityKey, string $object = 'result-item'): ResultItemPayload
    {
        $fields = array_map(
            fn(ResultFieldDescriptor $field): ResultItemPayloadField => new ResultItemPayloadField(
                code: $field->name,
                sourceType: $this->resolveSourceType($field),
                phpdocType: $this->typeResolver->resolve($field),
                format: $field->format,
                required: $field->required,
                nullable: $field->nullable,
                source: 'openapi',
                description: $field->description,
                notes: null,
            ),
            $this->schemaEntityReader->getResultFields($schemaFile, $entityKey),
        );

        return new ResultItemPayload(
            method: $method,
            object: $object,
            generatedFrom: ['openapi'],
            fields: $fields,
            sections: [],
        );
    }

    private function resolveSourceType(ResultFieldDescriptor $field): string
    {
        return match ($field->format) {
            'date-time' => 'datetime',
            'date' => 'date',
            default => $field->type,
        };
    }
}
