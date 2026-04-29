<?php

declare(strict_types=1);

namespace Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload;

readonly class ResultItemPayloadField
{
    public function __construct(
        public string $code,
        public string $sourceType,
        public string $phpdocType,
        public ?string $format,
        public bool $required,
        public bool $nullable,
        public string $source,
        public ?string $description = null,
        public ?string $notes = null,
    ) {
    }
}
