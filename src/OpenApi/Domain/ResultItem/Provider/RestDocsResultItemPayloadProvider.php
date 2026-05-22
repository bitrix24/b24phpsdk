<?php

declare(strict_types=1);

namespace Bitrix24\SDK\OpenApi\Domain\ResultItem\Provider;

use Bitrix24\SDK\OpenApi\Domain\ResultItem\Field\ResultFieldDescriptor;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload\ResultItemPayload;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload\ResultItemPayloadField;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload\ResultItemPayloadSection;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\PhpDoc\ResultItemPhpDocTypeResolver;
use RuntimeException;

final class RestDocsResultItemPayloadProvider
{
    public function __construct(
        private readonly ResultItemPhpDocTypeResolver $typeResolver = new ResultItemPhpDocTypeResolver(),
    ) {
    }

    public function provide(string $markdownFile, string $method, string $object = 'result-item'): ResultItemPayload
    {
        $markdown = file_get_contents($markdownFile);
        if ($markdown === false) {
            throw new RuntimeException(sprintf('Unable to read REST docs markdown file "%s"', $markdownFile));
        }

        $objects = $this->extractObjects($markdown);
        $rootKey = $this->normalizeIdentifier($object);
        if (!array_key_exists($rootKey, $objects)) {
            $rootObject = $this->extractReturnedDataObject($markdown, $rootKey);
            if ($rootObject === null && $rootKey === 'result_item') {
                $rootObject = $this->extractSingleReturnedDataObject($markdown);
            }

            if ($rootObject === null) {
                throw new RuntimeException(sprintf(
                    'Object "%s" was not found in REST docs markdown file "%s"',
                    $object,
                    $markdownFile,
                ));
            }

            return new ResultItemPayload(
                method: $method,
                object: $rootObject['name'],
                generatedFrom: ['b24restdocs'],
                fields: $rootObject['fields'],
                sections: array_values(array_map(
                    fn(array $section): ResultItemPayloadSection => new ResultItemPayloadSection(
                        name: $section['name'],
                        kind: 'object',
                        source: 'b24restdocs',
                        fields: $section['fields'],
                    ),
                    $objects,
                )),
            );
        }

        $rootObject = $objects[$rootKey];
        unset($objects[$rootKey]);

        return new ResultItemPayload(
            method: $method,
            object: $object,
            generatedFrom: ['b24restdocs'],
            fields: $rootObject['fields'],
            sections: array_values(array_map(
                fn(array $section): ResultItemPayloadSection => new ResultItemPayloadSection(
                    name: $section['name'],
                    kind: 'object',
                    source: 'b24restdocs',
                    fields: $section['fields'],
                ),
                $objects,
            )),
        );
    }

    /**
     * @return array{name: string, fields: list<ResultItemPayloadField>}|null
     */
    private function extractReturnedDataObject(string $markdown, string $object): ?array
    {
        $tableBlock = $this->findReturnedDataTableBlock($markdown);
        if ($tableBlock === null) {
            return null;
        }

        $fields = $this->extractNestedObjectFieldsFromReturnedData($tableBlock, $object);
        if ($fields === []) {
            return null;
        }

        return [
            'name' => $object,
            'fields' => $fields,
        ];
    }

    /**
     * @return array{name: string, fields: list<ResultItemPayloadField>}|null
     */
    private function extractSingleReturnedDataObject(string $markdown): ?array
    {
        $objects = $this->extractReturnedDataObjects($markdown);
        if (count($objects) === 1) {
            return array_values($objects)[0];
        }

        $tableBlock = $this->findReturnedDataTableBlock($markdown);
        if ($tableBlock === null) {
            return null;
        }

        return $this->extractDirectResultObjectFromReturnedData($tableBlock);
    }

    /**
     * @return array<string, array{name: string, fields: list<ResultItemPayloadField>}>
     */
    private function extractReturnedDataObjects(string $markdown): array
    {
        $tableBlock = $this->findReturnedDataTableBlock($markdown);
        if ($tableBlock === null) {
            return [];
        }

        return $this->extractNestedObjectsFromReturnedData($tableBlock);
    }

    private function findReturnedDataTableBlock(string $markdown): ?string
    {
        $lines = preg_split('/\R/u', $markdown) ?: [];

        foreach ($lines as $index => $line) {
            if (preg_match('/^#{2,6}\s+Returned Data\s*$/i', trim($line)) !== 1) {
                continue;
            }

            return $this->findNextTableBlock($lines, $index + 1);
        }

        return null;
    }

    /**
     * @return array{name: string, fields: list<ResultItemPayloadField>}|null
     */
    private function extractDirectResultObjectFromReturnedData(string $tableBlock): ?array
    {
        $fields = $this->parseTableBlock($tableBlock);
        $hasResultRoot = false;
        $rootFields = [];

        foreach ($fields as $field) {
            $normalizedFieldCode = $this->normalizeIdentifier($field->code);
            if ($normalizedFieldCode === 'result') {
                $hasResultRoot = true;
                continue;
            }

            if ($normalizedFieldCode === 'time' || str_contains($field->code, '.')) {
                continue;
            }

            $rootFields[] = $field;
        }

        if (!$hasResultRoot || $rootFields === []) {
            return null;
        }

        return [
            'name' => 'result',
            'fields' => $rootFields,
        ];
    }

    /**
     * @return list<ResultItemPayloadField>
     */
    private function extractNestedObjectFieldsFromReturnedData(string $tableBlock, string $object): array
    {
        $fields = [];
        $normalizedPrefix = $this->normalizeIdentifier($object . '.');

        foreach ($this->parseTableBlock($tableBlock) as $field) {
            $fieldCode = $this->normalizeIdentifier($field->code);
            if (!str_starts_with($fieldCode, $normalizedPrefix)) {
                continue;
            }

            $code = str_starts_with($field->code, $object . '.')
                ? substr($field->code, strlen($object) + 1)
                : substr($fieldCode, strlen($normalizedPrefix));
            if ($code === '' || str_contains($code, '.')) {
                continue;
            }

            $fields[] = new ResultItemPayloadField(
                code: $code,
                sourceType: $field->sourceType,
                phpdocType: $field->phpdocType,
                format: $field->format,
                required: $field->required,
                nullable: $field->nullable,
                source: $field->source,
                description: $field->description,
                notes: $field->notes,
            );
        }

        return $fields;
    }

    /**
     * @return array<string, array{name: string, fields: list<ResultItemPayloadField>}>
     */
    private function extractNestedObjectsFromReturnedData(string $tableBlock): array
    {
        $objects = [];

        foreach ($this->parseTableBlock($tableBlock) as $field) {
            if (!str_contains($field->code, '.')) {
                continue;
            }

            [$object, $fieldCode] = explode('.', $field->code, 2);
            if ($object === '' || $fieldCode === '' || str_contains($fieldCode, '.')) {
                continue;
            }

            $normalizedObject = $this->normalizeIdentifier($object);
            $objects[$normalizedObject] ??= [
                'name' => $object,
                'fields' => [],
            ];
            $objects[$normalizedObject]['fields'][] = new ResultItemPayloadField(
                code: $fieldCode,
                sourceType: $field->sourceType,
                phpdocType: $field->phpdocType,
                format: $field->format,
                required: $field->required,
                nullable: $field->nullable,
                source: $field->source,
                description: $field->description,
                notes: $field->notes,
            );
        }

        return $objects;
    }

    /**
     * @return array<string, array{name: string, fields: list<ResultItemPayloadField>}>
     */
    private function extractObjects(string $markdown): array
    {
        $lines = preg_split('/\R/u', $markdown) ?: [];
        $objects = [];

        foreach ($lines as $index => $line) {
            if (!preg_match('/^####\s+(?:Object\s+)?(.+?)\s+\{#([^}]+)\}\s*$/', trim($line), $matches)) {
                continue;
            }

            $name = $this->normalizeIdentifier($matches[2]);
            $tableBlock = $this->findNextTableBlock($lines, $index + 1);
            if ($tableBlock === null) {
                continue;
            }

            $objects[$name] = [
                'name' => $name,
                'fields' => $this->parseTableBlock($tableBlock),
            ];
        }

        return $objects;
    }

    /**
     * @param list<string> $lines
     */
    private function findNextTableBlock(array $lines, int $startIndex): ?string
    {
        for ($index = $startIndex, $count = count($lines); $index < $count; $index++) {
            $line = trim($lines[$index]);
            if ($line === '#|') {
                $buffer = [];
                for ($tableIndex = $index; $tableIndex < $count; $tableIndex++) {
                    $buffer[] = $lines[$tableIndex];
                    if (trim($lines[$tableIndex]) === '|#') {
                        return implode("\n", $buffer);
                    }
                }
            }

            if (preg_match('/^####\s+Object\s+/', $line) === 1) {
                return null;
            }
        }

        return null;
    }

    /**
     * @return list<ResultItemPayloadField>
     */
    private function parseTableBlock(string $tableBlock): array
    {
        $fields = [];
        $rowBuffer = [];

        foreach (preg_split('/\R/u', $tableBlock) ?: [] as $line) {
            $trimmedLine = trim($line);
            if ($trimmedLine === '#|' || $trimmedLine === '|#') {
                continue;
            }

            if (str_starts_with($trimmedLine, '||')) {
                $rowBuffer = [$trimmedLine];
            } elseif ($rowBuffer !== []) {
                $rowBuffer[] = $trimmedLine;
            } else {
                continue;
            }

            if (str_ends_with($trimmedLine, '||')) {
                $field = $this->parseRow(implode("\n", $rowBuffer));
                $rowBuffer = [];

                if ($field !== null) {
                    $fields[] = $field;
                }
            }
        }

        return $fields;
    }

    private function parseRow(string $row): ?ResultItemPayloadField
    {
        $normalizedRow = trim($row);
        $normalizedRow = preg_replace('/^\|\|\s*/', '', $normalizedRow) ?? $normalizedRow;
        $normalizedRow = preg_replace('/\s*\|\|$/', '', $normalizedRow) ?? $normalizedRow;

        $parts = preg_split('/\s+\|\s+/', $normalizedRow, 2);
        if ($parts === false || count($parts) !== 2) {
            return null;
        }

        $name = $this->extractRowName($parts[0]);
        $rawType = $this->extractRawType($parts[0]);
        $description = $this->cleanMarkdown($parts[1]);

        if ($name === '' || $rawType === '' || (strtolower($name) === 'name' && strtolower($rawType) === 'type')) {
            return null;
        }

        [$fieldType, $sourceType, $format] = $this->normalizeType($rawType);
        [$fieldDescription, $notes] = $this->splitDescriptionAndNotes($description);
        $nullable = $this->isNullable($description);

        return new ResultItemPayloadField(
            code: $name,
            sourceType: $sourceType,
            phpdocType: $this->typeResolver->resolve(new ResultFieldDescriptor(
                name: $name,
                type: $fieldType,
                format: $format,
                nullable: $nullable,
                description: $fieldDescription,
                source: 'b24restdocs',
            )),
            format: $format,
            required: !$nullable,
            nullable: $nullable,
            source: 'b24restdocs',
            description: $fieldDescription,
            notes: $notes,
        );
    }

    private function extractRowName(string $cell): string
    {
        if (preg_match('/\*\*([^*]+)\*\*/', $cell, $matches) === 1) {
            return trim($matches[1]);
        }

        return $this->firstNonEmptyLine($this->cleanMarkdown($cell));
    }

    private function extractRawType(string $cell): string
    {
        if (preg_match('/`([^`]+)`/', $cell, $matches) === 1) {
            return strtolower(trim($matches[1]));
        }

        return strtolower($this->firstNonEmptyLine($this->cleanMarkdown($cell)));
    }

    /**
     * @return array{0: string, 1: string|null}
     */
    private function splitDescriptionAndNotes(string $description): array
    {
        if (preg_match('/^(.+?)\.\s+(If .+)$/', $description, $matches) === 1) {
            return [trim($matches[1]), trim($matches[2])];
        }

        return [trim($description), null];
    }

    private function isNullable(string $description): bool
    {
        $normalized = strtolower($description);

        return str_contains($normalized, 'null')
            || str_contains($normalized, 'not specified');
    }

    /**
     * @return array{0: string, 1: string, 2: string|null}
     */
    private function normalizeType(string $rawType): array
    {
        return match (strtolower(trim($rawType))) {
            'datetime' => ['string', 'datetime', 'date-time'],
            'date' => ['string', 'date', 'date'],
            'integer', 'int' => ['integer', 'integer', null],
            'boolean', 'bool' => ['boolean', 'boolean', null],
            'object' => ['object', 'object', null],
            'array' => ['array', 'array', null],
            default => ['string', 'string', null],
        };
    }

    private function cleanMarkdown(string $value): string
    {
        $value = preg_replace('/\[(.*?)\]\((.*?)\)/', '$1', $value) ?? $value;
        $value = preg_replace('/`([^`]+)`/', '$1', $value) ?? $value;
        $value = str_replace(['**', '__'], '', $value);
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return trim($value);
    }

    private function firstNonEmptyLine(string $value): string
    {
        foreach (preg_split('/\R/u', $value) ?: [] as $line) {
            $trimmedLine = trim($line);
            if ($trimmedLine !== '') {
                return $trimmedLine;
            }
        }

        return '';
    }

    private function normalizeIdentifier(string $value): string
    {
        $normalized = strtolower(trim($value));
        $normalized = str_replace('-', '_', $normalized);

        return preg_replace('/[^a-z0-9_]+/', '_', $normalized) ?? $normalized;
    }
}
