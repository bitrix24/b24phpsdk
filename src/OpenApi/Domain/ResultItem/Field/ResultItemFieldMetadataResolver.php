<?php

declare(strict_types=1);

namespace Bitrix24\SDK\OpenApi\Domain\ResultItem\Field;

use Bitrix24\SDK\OpenApi\Domain\ResultItem\Provider\OpenApiResultFieldProvider;
use RuntimeException;

class ResultItemFieldMetadataResolver
{
    public function __construct(
        private readonly OpenApiResultFieldProvider $openApiProvider,
    ) {
    }

    public function resolve(ResultItemFieldMetadataRequest $request): ResultFieldCollection
    {
        $openApiFields = $this->openApiProvider->provide($request->schemaFile, $request->entityKey);
        if ($openApiFields instanceof ResultFieldCollection) {
            return $openApiFields;
        }

        throw new RuntimeException(sprintf(
            'Unable to resolve result field metadata for method "%s" from OpenAPI',
            $request->methodName
        ));
    }
}
