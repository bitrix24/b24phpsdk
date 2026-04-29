<?php

declare(strict_types=1);

namespace Bitrix24\SDK\OpenApi\Domain\ResultItem\Field;

readonly class ResultFieldCollection
{
    /**
     * @param list<ResultFieldDescriptor> $fields
     */
    public function __construct(
        public array $fields,
        public string $sourceName,
    ) {
    }
}
