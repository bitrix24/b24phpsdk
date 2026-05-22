<?php

declare(strict_types=1);

namespace Bitrix24\SDK\OpenApi\Domain\ResultItem\Provider;

use Bitrix24\SDK\OpenApi\Domain\OpenApiSchemaEntityReader;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Field\ResultFieldCollection;

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
