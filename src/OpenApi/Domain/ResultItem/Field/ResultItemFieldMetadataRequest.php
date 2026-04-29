<?php

declare(strict_types=1);

namespace Bitrix24\SDK\OpenApi\Domain\ResultItem\Field;

readonly class ResultItemFieldMetadataRequest
{
    /**
     * @param array<string, mixed> $sampleParams
     */
    public function __construct(
        public string $methodName,
        public string $schemaFile,
        public ?string $entityKey = null,
        public ?string $webhook = null,
        public array $sampleParams = [],
        public string $responsePath = '',
    ) {
    }
}
