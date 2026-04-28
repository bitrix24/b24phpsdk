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
            throw new RuntimeException(sprintf(
                'Object "%s" was not found in REST docs markdown file "%s"',
                $object,
                $markdownFile,
            ));
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
     * @return array<string, array{name: string, fields: list<ResultItemPayloadField>}>
     */
    private function extractObjects(string $markdown): array
    {
        $lines = preg_split('/\R/u', $markdown) ?: [];
        $objects = [];

        foreach ($lines as $index => $line) {
            if (!preg_match('/^####\s+Object\s+(.+?)\s+\{#([^}]+)\}\s*$/', trim($line), $matches)) {
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
