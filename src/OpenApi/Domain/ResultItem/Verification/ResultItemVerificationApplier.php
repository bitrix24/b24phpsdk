<?php

declare(strict_types=1);

namespace Bitrix24\SDK\OpenApi\Domain\ResultItem\Verification;

use Bitrix24\SDK\OpenApi\Domain\ResultItem\Field\ResultFieldDescriptor;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload\ResultItemPayload;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload\ResultItemPayloadField;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload\ResultItemPayloadSection;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\PhpDoc\ResultItemPhpDocTypeResolver;
use InvalidArgumentException;

final class ResultItemVerificationApplier
{
    public function __construct(
        private readonly ResultItemPhpDocTypeResolver $typeResolver = new ResultItemPhpDocTypeResolver(),
    ) {
    }

    public function apply(ResultItemPayload $payload, ResultItemVerificationReport $report): ResultItemPayload
    {
        if ($payload->method !== $report->method) {
            throw new InvalidArgumentException('Cannot apply a verification report to a different method payload.');
        }

        $fields = $payload->fields;
        $sections = $payload->sections;

        foreach ($report->unexpectedFields as $unexpectedField) {
            if (($unexpectedField['action'] ?? null) !== 'add_field') {
                continue;
            }

            $section = $this->readNullableString($unexpectedField, 'section');
            if ($section === null) {
                if ($this->findFieldIndex($fields, $this->readString($unexpectedField, 'code')) !== null) {
                    continue;
                }

                $fields[] = $this->createFieldFromUnexpectedField($unexpectedField);
                continue;
            }

            $sectionIndex = $this->findSectionIndex($sections, $section);
            if ($sectionIndex === null) {
                continue;
            }

            if (
                $this->findFieldIndex(
                    $sections[$sectionIndex]->fields,
                    $this->readString($unexpectedField, 'code'),
                ) !== null
            ) {
                continue;
            }

            $sectionFields = $sections[$sectionIndex]->fields;
            $sectionFields[] = $this->createFieldFromUnexpectedField($unexpectedField);
            $sections[$sectionIndex] = new ResultItemPayloadSection(
                name: $sections[$sectionIndex]->name,
                kind: $sections[$sectionIndex]->kind,
                source: $sections[$sectionIndex]->source,
                fields: $sectionFields,
            );
        }

        foreach ($report->nullabilityObservations as $observation) {
            if (($observation['action'] ?? null) !== 'mark_nullable') {
                continue;
            }

            $section = $this->readNullableString($observation, 'section');
            $code = $this->readString($observation, 'code');

            if ($section === null) {
                $fieldIndex = $this->findFieldIndex($fields, $code);
                if ($fieldIndex === null) {
                    continue;
                }

                $fields[$fieldIndex] = $this->withNullable($fields[$fieldIndex], true);
                continue;
            }

            $sectionIndex = $this->findSectionIndex($sections, $section);
            if ($sectionIndex === null) {
                continue;
            }

            $sectionFieldIndex = $this->findFieldIndex($sections[$sectionIndex]->fields, $code);
            if ($sectionFieldIndex === null) {
                continue;
            }

            $sectionFields = $sections[$sectionIndex]->fields;
            $sectionFields[$sectionFieldIndex] = $this->withNullable($sectionFields[$sectionFieldIndex], true);
            $sections[$sectionIndex] = new ResultItemPayloadSection(
                name: $sections[$sectionIndex]->name,
                kind: $sections[$sectionIndex]->kind,
                source: $sections[$sectionIndex]->source,
                fields: $sectionFields,
            );
        }

        return new ResultItemPayload(
            method: $payload->method,
            object: $payload->object,
            generatedFrom: $payload->generatedFrom,
            fields: array_values($fields),
            sections: array_values($sections),
            version: $payload->version,
        );
    }

    /**
     * @param array<string, mixed> $unexpectedField
     */
    private function createFieldFromUnexpectedField(array $unexpectedField): ResultItemPayloadField
    {
        $nullable = $this->readNullableBool($unexpectedField, 'nullable')
            ?? str_contains($this->readString($unexpectedField, 'phpdoc_type'), '|null');
        $phpdocType = $this->normalizePhpdocType(
            sourceType: $this->readString($unexpectedField, 'source_type'),
            format: $this->readNullableString($unexpectedField, 'format'),
            phpdocType: $this->readString($unexpectedField, 'phpdoc_type'),
            nullable: $nullable,
        );

        return new ResultItemPayloadField(
            code: $this->readString($unexpectedField, 'code'),
            sourceType: $this->readString($unexpectedField, 'source_type'),
            phpdocType: $phpdocType,
            format: $this->readNullableString($unexpectedField, 'format'),
            required: false,
            nullable: $nullable,
            source: 'api',
            description: null,
            notes: 'Observed in live API verification report',
        );
    }

    private function withNullable(ResultItemPayloadField $field, bool $nullable): ResultItemPayloadField
    {
        return new ResultItemPayloadField(
            code: $field->code,
            sourceType: $field->sourceType,
            phpdocType: $this->normalizePhpdocType(
                sourceType: $field->sourceType,
                format: $field->format,
                phpdocType: $field->phpdocType,
                nullable: $nullable,
            ),
            format: $field->format,
            required: $field->required,
            nullable: $nullable,
            source: $field->source,
            description: $field->description,
            notes: $field->notes,
        );
    }

    /**
     * @param list<ResultItemPayloadField> $fields
     */
    private function findFieldIndex(array $fields, string $code): ?int
    {
        foreach ($fields as $index => $field) {
            if ($field->code === $code) {
                return $index;
            }
        }

        return null;
    }

    /**
     * @param list<ResultItemPayloadSection> $sections
     */
    private function findSectionIndex(array $sections, string $sectionName): ?int
    {
        foreach ($sections as $index => $section) {
            if ($section->name === $sectionName) {
                return $index;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $entry
     */
    private function readString(array $entry, string $key): string
    {
        $value = $entry[$key] ?? null;
        if (!is_string($value) || trim($value) === '') {
            throw new InvalidArgumentException(sprintf('Expected non-empty string key "%s" in verification report entry.', $key));
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $entry
     */
    private function readNullableString(array $entry, string $key): ?string
    {
        $value = $entry[$key] ?? null;
        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            throw new InvalidArgumentException(sprintf('Expected nullable string key "%s" in verification report entry.', $key));
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $entry
     */
    private function readNullableBool(array $entry, string $key): ?bool
    {
        $value = $entry[$key] ?? null;
        if ($value === null) {
            return null;
        }

        if (!is_bool($value)) {
            throw new InvalidArgumentException(sprintf('Expected nullable bool key "%s" in verification report entry.', $key));
        }

        return $value;
    }

    private function normalizePhpdocType(
        string $sourceType,
        ?string $format,
        string $phpdocType,
        bool $nullable,
    ): string {
        if ($nullable) {
            if (str_contains($phpdocType, '|null')) {
                return $phpdocType;
            }

            return $phpdocType . '|null';
        }

        if (!str_contains($phpdocType, '|null')) {
            return $phpdocType;
        }

        $nonNullablePhpdocType = implode(
            '|',
            array_values(array_filter(
                explode('|', $phpdocType),
                static fn(string $type): bool => $type !== 'null',
            )),
        );

        if ($nonNullablePhpdocType !== '') {
            return $nonNullablePhpdocType;
        }

        return $this->typeResolver->resolve(new ResultFieldDescriptor(
            name: 'field',
            type: $this->normalizeDescriptorType($sourceType),
            format: $format,
            nullable: false,
        ));
    }

    private function normalizeDescriptorType(string $sourceType): string
    {
        return match ($sourceType) {
            'datetime', 'date' => 'string',
            default => $sourceType,
        };
    }
}
