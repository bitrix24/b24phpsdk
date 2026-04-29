<?php

declare(strict_types=1);

namespace Bitrix24\SDK\OpenApi\Domain\ResultItem\Field;

readonly class ResultFieldDescriptor
{
    public function __construct(
        public string $name,
        public string $type,
        public ?string $format = null,
        public bool $nullable = false,
        public ?string $description = null,
        public ?string $source = null,
        public bool $required = false,
    ) {
    }
}
