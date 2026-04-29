<?php

declare(strict_types=1);

namespace Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload;

readonly class ResultItemPayload
{
    /**
     * @param list<string> $generatedFrom
     * @param list<ResultItemPayloadField> $fields
     * @param list<ResultItemPayloadSection> $sections
     */
    public function __construct(
        public string $method,
        public string $object,
        public array $generatedFrom,
        public array $fields,
        public array $sections,
        public int $version = 1,
    ) {
    }
}
