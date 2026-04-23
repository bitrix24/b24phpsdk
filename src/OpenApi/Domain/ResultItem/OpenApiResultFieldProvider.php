<?php

declare(strict_types=1);

namespace Bitrix24\SDK\OpenApi\Domain\ResultItem;

use Bitrix24\SDK\OpenApi\Domain\Schema\OpenApiSchemaEntityReader;

class OpenApiResultFieldProvider
{
    public function __construct(
        private OpenApiSchemaEntityReader $schemaEntityReader,
    ) {
    }

    public function provide(string $schemaFile, ?string $entityKey): ?ResultFieldCollection
    {
        if ($entityKey === null || trim($entityKey) === '') {
            return null;
        }

        $fields = $this->schemaEntityReader->getResultFields($schemaFile, $entityKey);
        if ($fields === []) {
            return null;
        }

        return new ResultFieldCollection($fields, 'openapi');
    }
}
