<?php

declare(strict_types=1);

namespace Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload;

use InvalidArgumentException;

final class ResultItemPayloadSerializer
{
    public function encode(ResultItemPayload $payload): string
    {
        return rtrim($this->dumpMapping($this->payloadToArray($payload), 0)) . "\n";
    }

    public function decode(string $yaml): ResultItemPayload
    {
        $lines = $this->prepareLines($yaml);
        $index = 0;
        $data = $this->parseMapping($lines, $index, 0, 'payload');

        $this->assertAllowedKeys($data, ['version', 'method', 'object', 'generated_from', 'fields', 'sections'], 'payload');
        $this->assertTopLevelShape($data);

        return new ResultItemPayload(
            method: $this->requireString($data, 'method'),
            object: $this->requireString($data, 'object'),
            generatedFrom: $this->decodeStringList($this->requireList($data, 'generated_from'), 'generated_from'),
            fields: $this->decodeFields($this->requireListOfArrays($data, 'fields'), 'fields'),
            sections: $this->decodeSections($this->requireListOfArrays($data, 'sections')),
            version: $this->requireInt($data, 'version'),
        );
    }

    /**
     * @return array{
     *     version:int,
     *     method:string,
     *     object:string,
     *     generated_from:list<string>,
     *     fields:list<array<string, mixed>>,
     *     sections:list<array<string, mixed>>
     * }
     */
    private function payloadToArray(ResultItemPayload $payload): array
    {
        return [
            'version' => $payload->version,
            'method' => $payload->method,
            'object' => $payload->object,
            'generated_from' => array_values($payload->generatedFrom),
            'fields' => array_map([$this, 'fieldToArray'], $payload->fields),
            'sections' => array_map([$this, 'sectionToArray'], $payload->sections),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function fieldToArray(ResultItemPayloadField $field): array
    {
        return [
            'code' => $field->code,
            'source_type' => $field->sourceType,
            'phpdoc_type' => $field->phpdocType,
            'format' => $field->format,
            'required' => $field->required,
            'nullable' => $field->nullable,
            'source' => $field->source,
            'description' => $field->description,
            'notes' => $field->notes,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function sectionToArray(ResultItemPayloadSection $section): array
    {
        return [
            'name' => $section->name,
            'kind' => $section->kind,
            'source' => $section->source,
            'fields' => array_map([$this, 'fieldToArray'], $section->fields),
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function dumpMapping(array $data, int $indent): string
    {
        $lines = [];
        foreach ($data as $key => $value) {
            $prefix = str_repeat(' ', $indent) . $key . ':';
            if (is_array($value)) {
                if ($this->isList($value)) {
                    if ($value === []) {
                        $lines[] = $prefix . ' []';
                        continue;
                    }

                    $lines[] = $prefix;
                    $lines[] = $this->dumpSequence($value, $indent + 2);
                    $lines[] = '';
                    continue;
                }

                $lines[] = $prefix;
                $lines[] = $this->dumpMapping($value, $indent + 2);
                continue;
            }

            $lines[] = $prefix . ' ' . $this->dumpScalar($value);
        }

        return implode("\n", $lines);
    }

    /**
     * @param array<int, mixed> $items
     */
    private function dumpSequence(array $items, int $indent): string
    {
        $isComplex = false;
        foreach ($items as $item) {
            if (is_array($item)) {
                $isComplex = true;
                break;
            }
        }

        if (!$isComplex) {
            $lines = [];
            foreach ($items as $item) {
                $lines[] = str_repeat(' ', $indent) . '- ' . $this->dumpScalar($item);
            }

            return implode("\n", $lines);
        }

        $blocks = [];
        foreach ($items as $item) {
            $prefix = str_repeat(' ', $indent) . '-';

            if (is_array($item)) {
                if ($this->isList($item)) {
                    $blocks[] = $prefix . "\n" . $this->dumpSequence($item, $indent + 2);
                    continue;
                }

                $itemLines = [];
                $keys = array_keys($item);
                $firstKey = array_shift($keys);
                if ($firstKey === null) {
                    $blocks[] = $prefix;
                    continue;
                }

                $firstValue = $item[$firstKey];
                if (is_array($firstValue)) {
                    $itemLines[] = $prefix . ' ' . $firstKey . ':';
                    $itemLines[] = $this->dumpNestedValue($firstValue, $indent + 4);
                } else {
                    $itemLines[] = $prefix . ' ' . $firstKey . ': ' . $this->dumpScalar($firstValue);
                }

                foreach ($keys as $key) {
                    $value = $item[$key];
                    if (is_array($value)) {
                        $itemLines[] = str_repeat(' ', $indent + 2) . $key . ':';
                        $itemLines[] = $this->dumpNestedValue($value, $indent + 4);
                        continue;
                    }

                    $itemLines[] = str_repeat(' ', $indent + 2) . $key . ': ' . $this->dumpScalar($value);
                }

                $blocks[] = implode("\n", $itemLines);
                continue;
            }

            $blocks[] = $prefix . ' ' . $this->dumpScalar($item);
        }

        return implode("\n\n", $blocks);
    }

    /**
     * @param array<string, mixed>|list<mixed> $value
     */
    private function dumpNestedValue(array $value, int $indent): string
    {
        return $this->isList($value)
            ? $this->dumpSequence($value, $indent)
            : $this->dumpMapping($value, $indent);
    }

    private function dumpScalar(mixed $value): string
    {
        if ($value === null) {
            return 'null';
        }

        if ($value === true) {
            return 'true';
        }

        if ($value === false) {
            return 'false';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        $string = (string) $value;
        if ($string === '') {
            return "''";
        }

        if (
            !in_array($string, ['null', 'true', 'false', '[]', '{}', '~'], true)
            && !preg_match('/^\s|\s$/', $string)
            && !str_contains($string, "\n")
            && !str_contains($string, ':')
            && !preg_match('/^[+-]?(?:\d+\.?\d*|\.\d+)$/', $string)
            && strpbrk($string, "#{}[],&*?|<>=!%@`") === false
        ) {
            return $string;
        }

        return '"' . str_replace(["\\", "\"", "\n"], ["\\\\", "\\\"", "\\n"], $string) . '"';
    }

    /**
     * @return list<array{line:int,text:string}>
     */
    private function prepareLines(string $yaml): array
    {
        $yaml = str_replace(["\r\n", "\r"], "\n", $yaml);

        $lines = explode("\n", $yaml);
        $prepared = [];
        foreach ($lines as $lineNumber => $line) {
            $prepared[] = [
                'line' => $lineNumber + 1,
                'text' => $line,
            ];
        }

        return $prepared;
    }

    /**
     * @param list<array{line:int,text:string}> $lines
     * @return array<string, mixed>
     */
    private function parseMapping(array $lines, int &$index, int $indent, string $context, ?array &$lineNumbers = null): array
    {
        $data = [];
        $this->parseMappingInto($data, $lines, $index, $indent, $context, $lineNumbers);

        return $data;
    }

    /**
     * @param list<array{line:int,text:string}> $lines
     * @param array<string, mixed> $data
     */
    private function parseMappingInto(array &$data, array $lines, int &$index, int $indent, string $context, ?array &$lineNumbers = null): void
    {
        while ($index < count($lines)) {
            $line = $lines[$index];
            if ($this->isBlankLine($line)) {
                $index++;
                continue;
            }

            $lineIndent = $this->lineIndent($line);

            if ($lineIndent < $indent) {
                break;
            }

            if ($lineIndent > $indent) {
                throw new InvalidArgumentException(sprintf(
                    'Malformed payload near line %d: unexpected indentation.',
                    $this->lineNumber($line)
                ));
            }

            $trimmed = trim($this->lineText($line));
            if (!str_contains($trimmed, ':')) {
                throw new InvalidArgumentException(sprintf(
                    'Malformed payload near line %d: expected a key/value pair.',
                    $this->lineNumber($line)
                ));
            }

            [$key, $rest] = explode(':', $trimmed, 2);
            $key = trim($key);
            $rest = ltrim($rest);
            $lineNumber = $this->lineNumber($line);
            $index++;

            $this->assertNoDuplicateKey($data, $key, $context, $lineNumber);
            if ($lineNumbers !== null) {
                $lineNumbers[$key] = $lineNumber;
            }

            if ($rest !== '') {
                $data[$key] = $this->parseScalar($rest);
                continue;
            }

            $nextIndent = $this->nextIndent($lines, $index);
            if ($nextIndent === null || $nextIndent <= $indent) {
                $data[$key] = null;
                continue;
            }

            $data[$key] = $this->parseBlock($lines, $index, $nextIndent, $context . '.' . $key);
        }
    }

    /**
     * @param list<array{line:int,text:string}> $lines
     * @return list<mixed>|array<string, mixed>
     */
    private function parseBlock(array $lines, int &$index, int $indent, string $context): array
    {
        $line = $lines[$index] ?? null;
        if ($line === null) {
            throw new InvalidArgumentException('Malformed payload: unexpected end of document.');
        }

        return str_starts_with(trim($this->lineText($line)), '-')
            ? $this->parseSequence($lines, $index, $indent, $context)
            : $this->parseMapping($lines, $index, $indent, $context);
    }

    /**
     * @param list<array{line:int,text:string}> $lines
     * @return list<mixed>
     */
    private function parseSequence(array $lines, int &$index, int $indent, string $context): array
    {
        $items = [];

        while ($index < count($lines)) {
            $line = $lines[$index];
            if ($this->isBlankLine($line)) {
                $index++;
                continue;
            }

            $lineIndent = $this->lineIndent($line);

            if ($lineIndent < $indent || !str_starts_with(trim($this->lineText($line)), '-')) {
                if ($lineIndent > $indent) {
                    throw new InvalidArgumentException(sprintf(
                        'Malformed payload near line %d: unexpected indentation.',
                        $this->lineNumber($line)
                    ));
                }

                break;
            }

            $trimmed = trim($this->lineText($line));
            $itemText = ltrim(substr($trimmed, 1));
            $lineNumber = $this->lineNumber($line);
            $index++;

            if ($itemText === '') {
                $nextIndent = $this->nextIndent($lines, $index);
                if ($nextIndent === null || $nextIndent <= $indent) {
                    throw new InvalidArgumentException(sprintf(
                        'Malformed payload near line %d: expected nested content.',
                        $lineNumber
                    ));
                }

                $items[] = $this->parseBlock($lines, $index, $nextIndent, $context . '[' . count($items) . ']');
                continue;
            }

            if (
                str_starts_with($itemText, '"')
                || str_starts_with($itemText, "'")
            ) {
                $items[] = $this->parseScalar($itemText);
                continue;
            }

            if (!str_contains($itemText, ':')) {
                $items[] = $this->parseScalar($itemText);
                continue;
            }

            [$key, $restRaw] = explode(':', $itemText, 2);
            if ($restRaw !== '' && !preg_match('/^\s/', $restRaw)) {
                $items[] = $this->parseScalar($itemText);
                continue;
            }

            $item = [];
            $itemKey = trim($key);
            $this->assertNoDuplicateKey($item, $itemKey, $context . '[' . count($items) . ']', $lineNumber);
            $item[$itemKey] = $restRaw !== '' ? $this->parseScalar(ltrim($restRaw)) : null;

            $nextIndent = $this->nextIndent($lines, $index);
            if ($nextIndent !== null && $nextIndent > $indent) {
                $nested = $this->parseBlock($lines, $index, $nextIndent, $context . '[' . count($items) . ']');
                if ($restRaw === '') {
                    $item[$itemKey] = $nested;

                    $nextSiblingIndent = $this->nextIndent($lines, $index);
                    if ($nextSiblingIndent !== null && $nextSiblingIndent > $indent) {
                        $tailLineNumbers = [];
                        $tail = $this->parseMapping($lines, $index, $nextSiblingIndent, $context . '[' . count($items) . ']', $tailLineNumbers);
                        foreach ($tail as $nestedKey => $nestedValue) {
                            $this->assertNoDuplicateKey(
                                $item,
                                (string) $nestedKey,
                                $context . '[' . count($items) . ']',
                                $tailLineNumbers[$nestedKey] ?? $lineNumber
                            );
                            $item[$nestedKey] = $nestedValue;
                        }
                    }
                } elseif (is_array($nested)) {
                    foreach ($nested as $nestedKey => $nestedValue) {
                        $this->assertNoDuplicateKey(
                            $item,
                            (string) $nestedKey,
                            $context . '[' . count($items) . ']',
                            $lineNumber
                        );
                        $item[$nestedKey] = $nestedValue;
                    }
                }
            }

            $items[] = $item;
        }

        return $items;
    }

    private function parseScalar(string $value): mixed
    {
        $value = trim($value);

        if ($value === 'null' || $value === '~') {
            return null;
        }

        if ($value === 'true') {
            return true;
        }

        if ($value === 'false') {
            return false;
        }

        if ($value === '[]') {
            return [];
        }

        if ($value === '{}') {
            throw new InvalidArgumentException('Malformed payload: empty mapping scalar "{}" is not allowed.');
        }

        if (preg_match('/^-?\d+$/', $value) === 1) {
            return (int) $value;
        }

        if (preg_match('/^-?\d+\.\d+$/', $value) === 1) {
            return (float) $value;
        }

        if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
            $decoded = '';
            $inner = substr($value, 1, -1);
            $length = strlen($inner);
            for ($i = 0; $i < $length; $i++) {
                $char = $inner[$i];
                if ($char === '\\' && isset($inner[$i + 1])) {
                    $next = $inner[$i + 1];
                    if ($next === 'n') {
                        $decoded .= "\n";
                        $i++;
                        continue;
                    }

                    if ($next === '"') {
                        $decoded .= '"';
                        $i++;
                        continue;
                    }

                    if ($next === '\\') {
                        $decoded .= '\\';
                        $i++;
                        continue;
                    }

                    $decoded .= '\\' . $next;
                    $i++;
                    continue;
                }

                $decoded .= $char;
            }

            return $decoded;
        }

        if (str_starts_with($value, "'") && str_ends_with($value, "'")) {
            return str_replace("''", "'", substr($value, 1, -1));
        }

        return $value;
    }

    /**
     * @param array{line:int,text:string} $line
     */
    private function lineIndent(array $line): int
    {
        return strspn($this->lineText($line), ' ');
    }

    /**
     * @param list<array{line:int,text:string}> $lines
     */
    private function nextIndent(array $lines, int $index): ?int
    {
        for ($i = $index; $i < count($lines); $i++) {
            $line = $lines[$i];
            if ($this->isBlankLine($line)) {
                continue;
            }

            return $this->lineIndent($line);
        }

        return null;
    }

    /**
     * @param array<string, mixed>|list<mixed> $value
     */
    private function isList(array $value): bool
    {
        return array_is_list($value);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function assertTopLevelShape(array $data): void
    {
        $this->requireString($data, 'method');
        $this->requireString($data, 'object');
        $this->requireList($data, 'generated_from');
        $this->requireListOfArrays($data, 'fields');
        $this->requireListOfArrays($data, 'sections');
        $this->requireInt($data, 'version');
    }

    /**
     * @param array<string, mixed> $data
     */
    private function decodeField(array $data, string $context): ResultItemPayloadField
    {
        $this->assertAllowedKeys(
            $data,
            ['code', 'source_type', 'phpdoc_type', 'format', 'required', 'nullable', 'source', 'description', 'notes'],
            $context
        );

        return new ResultItemPayloadField(
            code: $this->requireString($data, 'code', $context),
            sourceType: $this->requireString($data, 'source_type', $context),
            phpdocType: $this->requireString($data, 'phpdoc_type', $context),
            format: $this->requireNullableString($data, 'format', $context),
            required: $this->requireBool($data, 'required', $context),
            nullable: $this->requireBool($data, 'nullable', $context),
            source: $this->requireString($data, 'source', $context),
            description: $this->requireNullableString($data, 'description', $context),
            notes: $this->requireNullableString($data, 'notes', $context),
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function decodeSection(array $data, string $context): ResultItemPayloadSection
    {
        $this->assertAllowedKeys($data, ['name', 'kind', 'source', 'fields'], $context);

        return new ResultItemPayloadSection(
            name: $this->requireString($data, 'name', $context),
            kind: $this->requireString($data, 'kind', $context),
            source: $this->requireString($data, 'source', $context),
            fields: $this->decodeFields($this->requireListOfArrays($data, 'fields', $context), $context . '.fields'),
        );
    }

    /**
     * @param list<array<string, mixed>> $fields
     * @return list<ResultItemPayloadField>
     */
    private function decodeFields(array $fields, string $context): array
    {
        $decoded = [];
        foreach ($fields as $index => $field) {
            $decoded[] = $this->decodeField($field, sprintf('%s[%d]', $context, $index));
        }

        return $decoded;
    }

    /**
     * @param list<array<string, mixed>> $sections
     * @return list<ResultItemPayloadSection>
     */
    private function decodeSections(array $sections): array
    {
        $decoded = [];
        foreach ($sections as $index => $section) {
            $decoded[] = $this->decodeSection($section, sprintf('sections[%d]', $index));
        }

        return $decoded;
    }

    /**
     * @param array<string, mixed> $data
     * @return list<mixed>
     */
    private function requireList(array $data, string $key, string $context = 'payload'): array
    {
        $value = $this->requireKey($data, $key, $context);
        if (!is_array($value) || !array_is_list($value)) {
            throw new InvalidArgumentException(sprintf('Invalid %s.%s: expected a list.', $context, $key));
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     * @return list<array<string, mixed>>
     */
    private function requireListOfArrays(array $data, string $key, string $context = 'payload'): array
    {
        $value = $this->requireList($data, $key, $context);
        foreach ($value as $index => $item) {
            if (!is_array($item) || array_is_list($item)) {
                throw new InvalidArgumentException(sprintf('Invalid %s.%s[%d]: expected a mapping.', $context, $key, $index));
            }
        }

        /** @var list<array<string, mixed>> $value */
        return $value;
    }

    /**
     * @param array<string, mixed> $data
     * @param list<string> $allowedKeys
     */
    private function assertAllowedKeys(array $data, array $allowedKeys, string $context): void
    {
        foreach (array_keys($data) as $key) {
            if (!in_array($key, $allowedKeys, true)) {
                throw new InvalidArgumentException(sprintf('Unexpected key "%s" in %s.', $key, $context));
            }
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function assertNoDuplicateKey(array $data, string $key, string $context, int $lineNumber): void
    {
        if (array_key_exists($key, $data)) {
            throw new InvalidArgumentException(sprintf(
                'Duplicate key "%s" in %s near line %d.',
                $key,
                $context,
                $lineNumber
            ));
        }
    }

    /**
     * @param array{line:int,text:string} $line
     */
    private function isBlankLine(array $line): bool
    {
        return trim($line['text']) === '';
    }

    /**
     * @param array{line:int,text:string} $line
     */
    private function lineText(array $line): string
    {
        return $line['text'];
    }

    /**
     * @param array{line:int,text:string} $line
     */
    private function lineNumber(array $line): int
    {
        return $line['line'];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function requireString(array $data, string $key, string $context = 'payload'): string
    {
        $value = $this->requireKey($data, $key, $context);
        if (!is_string($value) || $value === '') {
            throw new InvalidArgumentException(sprintf('Invalid %s.%s: expected a non-empty string.', $context, $key));
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function requireNullableString(array $data, string $key, string $context = 'payload'): ?string
    {
        $value = $this->requireKey($data, $key, $context);
        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            throw new InvalidArgumentException(sprintf('Invalid %s.%s: expected a string or null.', $context, $key));
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function requireBool(array $data, string $key, string $context = 'payload'): bool
    {
        $value = $this->requireKey($data, $key, $context);
        if (!is_bool($value)) {
            throw new InvalidArgumentException(sprintf('Invalid %s.%s: expected true or false.', $context, $key));
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function requireInt(array $data, string $key, string $context = 'payload'): int
    {
        $value = $this->requireKey($data, $key, $context);
        if (!is_int($value)) {
            throw new InvalidArgumentException(sprintf('Invalid %s.%s: expected an integer.', $context, $key));
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function requireKey(array $data, string $key, string $context): mixed
    {
        if (!array_key_exists($key, $data)) {
            throw new InvalidArgumentException(sprintf('Missing required key "%s" in %s.', $key, $context));
        }

        return $data[$key];
    }

    /**
     * @param list<mixed> $values
     * @return list<string>
     */
    private function decodeStringList(array $values, string $context): array
    {
        $decoded = [];
        foreach ($values as $index => $value) {
            if (!is_string($value) || $value === '') {
                throw new InvalidArgumentException(sprintf('Invalid %s[%d]: expected a non-empty string.', $context, $index));
            }

            $decoded[] = $value;
        }

        return $decoded;
    }
}
