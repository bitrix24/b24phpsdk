<?php

declare(strict_types=1);

namespace Bitrix24\SDK\OpenApi\Domain\ResultItem\Provider;

use Bitrix24\SDK\Infrastructure\Console\Commands\Metadata\Bitrix24MethodResultFetcher;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Field\ResultFieldCollection;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Field\ResultFieldDescriptor;

class LiveApiResultFieldProvider
{
    public function __construct(
        private Bitrix24MethodResultFetcher $resultFetcher,
    ) {
    }

    public function provide(
        string $webhook,
        string $methodName,
        array $sampleParams = [],
        string $responsePath = '',
    ): ?ResultFieldCollection {
        if (trim($webhook) === '') {
            return null;
        }

        $payload = $this->resultFetcher->fetch($webhook, $methodName, $sampleParams);
        $resultItem = $this->extractResultItem($payload, $responsePath);
        if (!is_array($resultItem)) {
            return null;
        }

        $fields = [];
        foreach ($resultItem as $fieldName => $value) {
            $fields[] = $this->inferFieldDescriptor((string) $fieldName, $value);
        }

        return $fields === [] ? null : new ResultFieldCollection($fields, 'api');
    }

    private function extractResultItem(mixed $payload, string $responsePath): mixed
    {
        $value = $payload;
        if ($responsePath !== '') {
            foreach (explode('.', $responsePath) as $segment) {
                if ($segment === '') {
                    continue;
                }

                if (is_array($value) && array_key_exists($segment, $value)) {
                    $value = $value[$segment];
                    continue;
                }

                if (ctype_digit($segment) && is_array($value) && array_key_exists((int)$segment, $value)) {
                    $value = $value[(int)$segment];
                    continue;
                }

                return null;
            }
        }

        if (is_array($value) && array_is_list($value)) {
            return $value[0] ?? null;
        }

        return $value;
    }

    private function inferFieldDescriptor(string $fieldName, mixed $value): ResultFieldDescriptor
    {
        if (is_int($value)) {
            return new ResultFieldDescriptor($fieldName, 'integer', null, false, source: 'api');
        }

        if (is_bool($value)) {
            return new ResultFieldDescriptor($fieldName, 'boolean', null, false, source: 'api');
        }

        if (is_array($value)) {
            return new ResultFieldDescriptor(
                $fieldName,
                array_is_list($value) ? 'array' : 'object',
                null,
                false,
                source: 'api'
            );
        }

        if (is_string($value)) {
            [$type, $format] = $this->inferStringType($value);

            return new ResultFieldDescriptor($fieldName, $type, $format, false, source: 'api');
        }

        if ($value === null) {
            return new ResultFieldDescriptor($fieldName, 'mixed', null, true, source: 'api');
        }

        return new ResultFieldDescriptor($fieldName, 'mixed', null, false, source: 'api');
    }

    /**
     * @return array{0: string, 1: string|null}
     */
    private function inferStringType(string $value): array
    {
        if ((bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return ['string', 'date'];
        }

        if ((bool) preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/', $value)) {
            return ['string', 'date-time'];
        }

        return ['string', null];
    }
}
