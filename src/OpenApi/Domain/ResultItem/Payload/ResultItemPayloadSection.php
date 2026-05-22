<?php

declare(strict_types=1);

namespace Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload;

readonly class ResultItemPayloadSection
{
    /**
     * @param list<ResultItemPayloadField> $fields
     */
    public function __construct(
        public string $name,
        public string $kind,
        public string $source,
        public array $fields,
    ) {
    }
}
