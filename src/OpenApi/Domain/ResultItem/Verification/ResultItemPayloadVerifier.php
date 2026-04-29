<?php

declare(strict_types=1);

namespace Bitrix24\SDK\OpenApi\Domain\ResultItem\Verification;

use Bitrix24\SDK\Infrastructure\Console\Commands\Metadata\Bitrix24MethodResultFetcher;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Field\ResultFieldDescriptor;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload\ResultItemPayload;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload\ResultItemPayloadField;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\PhpDoc\ResultItemPhpDocTypeResolver;

final readonly class ResultItemPayloadVerifier
{
    public function __construct(
        private Bitrix24MethodResultFetcher $resultFetcher,
        private ResultItemPhpDocTypeResolver $typeResolver = new ResultItemPhpDocTypeResolver(),
    ) {
    }

    /**
     * @param array<string, mixed> $sampleParams
     */
    public function verify(
        ResultItemPayload $payload,
        string $webhook,
        array $sampleParams = [],
        string $responsePath = '',
    ): ResultItemVerificationReport {
        $runtimePayload = $this->resultFetcher->fetch($webhook, $payload->method, $sampleParams);
        $runtimeResultItem = $this->extractResultItem($runtimePayload, $responsePath);

        $confirmedFields = [];
        $missingFields = [];
        $unexpectedFields = [];
        $typeMismatches = [];
        $nullabilityObservations = [];

        $topLevelExpectedFieldCodes = [];
        foreach ($payload->fields as $field) {
            $topLevelExpectedFieldCodes[] = $field->code;
            $runtimeValue = $this->readRuntimeValue($runtimeResultItem, $field->code);
            $this->compareExpectedField(
                $field,
                $runtimeValue['exists'],
                $runtimeValue['value'],
                null,
                $confirmedFields,
                $missingFields,
                $typeMismatches,
                $nullabilityObservations,
            );
        }

        $sectionNames = [];
        foreach ($payload->sections as $section) {
            $sectionNames[] = $section->name;
            $runtimeSection = $this->readRuntimeValue($runtimeResultItem, $section->name);

            foreach ($section->fields as $field) {
                if (!$runtimeSection['exists'] || !is_array($runtimeSection['value'])) {
                    $missingFields[] = [
                        'code' => $field->code,
                        'section' => $section->name,
                    ];
                    continue;
                }

                $runtimeValue = $this->readRuntimeValue($runtimeSection['value'], $field->code);
                $this->compareExpectedField(
                    $field,
                    $runtimeValue['exists'],
                    $runtimeValue['value'],
                    $section->name,
                    $confirmedFields,
                    $missingFields,
                    $typeMismatches,
                    $nullabilityObservations,
                );
            }

            if ($runtimeSection['exists'] && is_array($runtimeSection['value'])) {
                $unexpectedFields = [
                    ...$unexpectedFields,
                    ...$this->collectUnexpectedFields(
                        $runtimeSection['value'],
                        $section->fields,
                        $section->name,
                    ),
                ];
            }
        }

        if (is_array($runtimeResultItem)) {
            foreach ($runtimeResultItem as $runtimeKey => $runtimeValue) {
                $runtimeKey = (string) $runtimeKey;
                if (
                    in_array($runtimeKey, $topLevelExpectedFieldCodes, true)
                    || in_array($runtimeKey, $sectionNames, true)
                ) {
                    continue;
                }

                $unexpectedFields[] = $this->buildUnexpectedFieldEntry($runtimeKey, $runtimeValue, null);
            }
        }

        return new ResultItemVerificationReport(
            method: $payload->method,
            confirmedFields: $confirmedFields,
            missingFields: $missingFields,
            unexpectedFields: $unexpectedFields,
            typeMismatches: $typeMismatches,
            nullabilityObservations: $nullabilityObservations,
        );
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

                if (ctype_digit($segment) && is_array($value) && array_key_exists((int) $segment, $value)) {
                    $value = $value[(int) $segment];
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

    /**
     * @param list<ResultItemPayloadField> $expectedFields
     * @return list<array<string, mixed>>
     */
    private function collectUnexpectedFields(array $runtimeValues, array $expectedFields, ?string $section): array
    {
        $expectedCodes = [];
        foreach ($expectedFields as $expectedField) {
            $expectedCodes[] = $expectedField->code;
        }

        $unexpectedFields = [];
        foreach ($runtimeValues as $runtimeKey => $runtimeValue) {
            $runtimeKey = (string) $runtimeKey;
            if (in_array($runtimeKey, $expectedCodes, true)) {
                continue;
            }

            $unexpectedFields[] = $this->buildUnexpectedFieldEntry($runtimeKey, $runtimeValue, $section);
        }

        return $unexpectedFields;
    }

    /**
     * @param array<string, mixed> $confirmedFields
     * @param array<string, mixed> $missingFields
     * @param array<string, mixed> $typeMismatches
     * @param array<string, mixed> $nullabilityObservations
     */
    private function compareExpectedField(
        ResultItemPayloadField $field,
        bool $exists,
        mixed $runtimeValue,
        ?string $section,
        array &$confirmedFields,
        array &$missingFields,
        array &$typeMismatches,
        array &$nullabilityObservations,
    ): void {
        if (!$exists) {
            $missingFields[] = [
                'code' => $field->code,
                'section' => $section,
            ];

            return;
        }

        if ($runtimeValue === null) {
            $nullabilityObservations[] = [
                'action' => 'mark_nullable',
                'code' => $field->code,
                'section' => $section,
            ];

            return;
        }

        $actualField = $this->inferRuntimeField($field->code, $runtimeValue);

        if (
            $field->sourceType === $actualField->sourceType
            && $field->phpdocType === $actualField->phpdocType
            && $field->format === $actualField->format
        ) {
            $confirmedFields[] = [
                'code' => $field->code,
                'section' => $section,
            ];

            return;
        }

        $typeMismatches[] = [
            'action' => 'review_type_mismatch',
            'code' => $field->code,
            'section' => $section,
            'expected_source_type' => $field->sourceType,
            'actual_source_type' => $actualField->sourceType,
            'expected_phpdoc_type' => $field->phpdocType,
            'actual_phpdoc_type' => $actualField->phpdocType,
            'expected_format' => $field->format,
            'actual_format' => $actualField->format,
        ];
    }

    /**
     * @return array{exists: bool, value: mixed}
     */
    private function readRuntimeValue(mixed $runtimeValue, string $key): array
    {
        if (is_array($runtimeValue) && array_key_exists($key, $runtimeValue)) {
            return [
                'exists' => true,
                'value' => $runtimeValue[$key],
            ];
        }

        return [
            'exists' => false,
            'value' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildUnexpectedFieldEntry(string $code, mixed $runtimeValue, ?string $section): array
    {
        $runtimeField = $this->inferRuntimeField($code, $runtimeValue);

        return [
            'action' => in_array($runtimeField->sourceType, ['array', 'object'], true)
                ? 'review_structural_addition'
                : 'add_field',
            'code' => $code,
            'section' => $section,
            'source_type' => $runtimeField->sourceType,
            'phpdoc_type' => $runtimeField->phpdocType,
            'format' => $runtimeField->format,
            'nullable' => $runtimeField->nullable,
        ];
    }

    private function inferRuntimeField(string $code, mixed $value): ResultItemPayloadField
    {
        $descriptor = $this->inferRuntimeDescriptor($code, $value);

        return new ResultItemPayloadField(
            code: $code,
            sourceType: $this->resolveSourceType($descriptor),
            phpdocType: $this->typeResolver->resolve($descriptor),
            format: $descriptor->format,
            required: false,
            nullable: $descriptor->nullable,
            source: 'api',
            description: null,
            notes: null,
        );
    }

    private function inferRuntimeDescriptor(string $fieldName, mixed $value): ResultFieldDescriptor
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
                source: 'api',
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

    private function resolveSourceType(ResultFieldDescriptor $field): string
    {
        return match ($field->format) {
            'date-time' => 'datetime',
            'date' => 'date',
            default => $field->type,
        };
    }
}
