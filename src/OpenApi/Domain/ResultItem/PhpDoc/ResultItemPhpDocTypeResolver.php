<?php

declare(strict_types=1);

namespace Bitrix24\SDK\OpenApi\Domain\ResultItem\PhpDoc;

use Bitrix24\SDK\OpenApi\Domain\ResultItem\Field\ResultFieldDescriptor;
use Carbon\CarbonImmutable;

readonly class ResultItemPhpDocTypeResolver
{
    public function resolve(ResultFieldDescriptor $field): string
    {
        $phpType = match ($field->type) {
            'integer' => 'int',
            'boolean' => 'bool',
            'array', 'object' => 'array',
            'string' => $this->resolveStringType($field),
            default => 'mixed',
        };

        if ($field->nullable && !str_contains($phpType, '|null')) {
            return $phpType . '|null';
        }

        return $phpType;
    }

    private function resolveStringType(ResultFieldDescriptor $field): string
    {
        return match ($field->format) {
            'date', 'date-time' => CarbonImmutable::class,
            default => 'string',
        };
    }
}
