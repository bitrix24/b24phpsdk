<?php

declare(strict_types=1);

namespace Bitrix24\SDK\OpenApi\Domain\ResultItem\Verification;

use InvalidArgumentException;

final class ResultItemVerificationReportSerializer
{
    public function encode(ResultItemVerificationReport $report): string
    {
        $lines = [
            'method: ' . $this->dumpScalar($report->method),
            $this->dumpEntryList('confirmed_fields', $report->confirmedFields),
            $this->dumpEntryList('missing_fields', $report->missingFields),
            $this->dumpEntryList('unexpected_fields', $report->unexpectedFields),
            $this->dumpEntryList('type_mismatches', $report->typeMismatches),
            $this->dumpEntryList('nullability_observations', $report->nullabilityObservations),
        ];

        return implode("\n", $lines) . "\n";
    }

    public function decode(string $yaml): ResultItemVerificationReport
    {
        $lines = preg_split('/\R/u', str_replace(["\r\n", "\r"], "\n", $yaml)) ?: [];
        $index = 0;
        $seenKeys = [];

        $method = null;
        $confirmedFields = [];
        $missingFields = [];
        $unexpectedFields = [];
        $typeMismatches = [];
        $nullabilityObservations = [];

        while ($index < count($lines)) {
            $line = $lines[$index];
            if (trim($line) === '') {
                $index++;
                continue;
            }

            if (!preg_match('/^([a-z_]+):(?:\s+(.*))?$/', $line, $matches)) {
                throw new InvalidArgumentException(sprintf('Malformed verification report line: "%s"', $line));
            }

            $key = $matches[1];
            $rawValue = $matches[2] ?? null;
            $lineNumber = $index + 1;

            if (array_key_exists($key, $seenKeys)) {
                throw new InvalidArgumentException(sprintf(
                    'Duplicate key "%s" in verification report near line %d.',
                    $key,
                    $lineNumber,
                ));
            }
            $seenKeys[$key] = true;

            if ($key === 'method') {
                if ($rawValue === null) {
                    throw new InvalidArgumentException('Missing value for "method" in verification report.');
                }

                $method = $this->parseScalar($rawValue);
                if (!is_string($method)) {
                    throw new InvalidArgumentException('Invalid "method" value in verification report.');
                }

                $index++;
                continue;
            }

            $entries = $this->parseEntryList($lines, $index, $rawValue, $key);

            match ($key) {
                'confirmed_fields' => $confirmedFields = $entries,
                'missing_fields' => $missingFields = $entries,
                'unexpected_fields' => $unexpectedFields = $entries,
                'type_mismatches' => $typeMismatches = $entries,
                'nullability_observations' => $nullabilityObservations = $entries,
                default => throw new InvalidArgumentException(sprintf(
                    'Unexpected key "%s" in verification report.',
                    $key,
                )),
            };
        }

        if ($method === null) {
            throw new InvalidArgumentException('Missing required key "method" in verification report.');
        }

        return new ResultItemVerificationReport(
            method: $method,
            confirmedFields: $confirmedFields,
            missingFields: $missingFields,
            unexpectedFields: $unexpectedFields,
            typeMismatches: $typeMismatches,
            nullabilityObservations: $nullabilityObservations,
        );
    }

    /**
     * @param list<array<string, mixed>> $entries
     */
    private function dumpEntryList(string $key, array $entries): string
    {
        if ($entries === []) {
            return sprintf('%s: []', $key);
        }

        $lines = [$key . ':'];
        foreach ($entries as $entry) {
            $firstKey = array_key_first($entry);
            if ($firstKey === null) {
                throw new InvalidArgumentException('Verification report entries must not be empty arrays.');
            }

            $lines[] = sprintf('  - %s: %s', $firstKey, $this->dumpScalar($entry[$firstKey]));
            foreach ($entry as $entryKey => $value) {
                if ($entryKey === $firstKey) {
                    continue;
                }

                $lines[] = sprintf('    %s: %s', $entryKey, $this->dumpScalar($value));
            }
        }

        return implode("\n", $lines);
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
        if (
            $string !== ''
            && !in_array($string, ['null', 'true', 'false', '[]', '{}', '~'], true)
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
     * @param list<string> $lines
     * @param int $index
     * @param string|null $rawValue
     * @param string $listKey
     * @return list<array<string, mixed>>
     */
    private function parseEntryList(array $lines, int &$index, ?string $rawValue, string $listKey): array
    {
        if ($rawValue !== null) {
            if (trim($rawValue) !== '[]') {
                throw new InvalidArgumentException(sprintf(
                    'Invalid list literal "%s" in verification report.',
                    $rawValue,
                ));
            }

            $index++;

            return [];
        }

        $index++;
        $entries = [];
        $currentEntry = null;
        $currentEntrySeenKeys = [];
        $entryIndex = -1;

        while ($index < count($lines)) {
            $line = $lines[$index];
            $lineNumber = $index + 1;
            if (trim($line) === '') {
                $index++;
                continue;
            }

            if (!str_starts_with($line, '  ')) {
                break;
            }

            if (preg_match('/^  - ([a-z_]+): (.+)$/', $line, $matches) === 1) {
                if ($currentEntry !== null) {
                    $entries[] = $currentEntry;
                }

                $entryIndex++;
                $currentEntry = [
                    $matches[1] => $this->parseScalar($matches[2]),
                ];
                $currentEntrySeenKeys = [$matches[1] => true];
                $index++;
                continue;
            }

            if (
                $currentEntry !== null
                && preg_match('/^    ([a-z_]+): (.+)$/', $line, $matches) === 1
            ) {
                if (array_key_exists($matches[1], $currentEntrySeenKeys)) {
                    throw new InvalidArgumentException(sprintf(
                        'Duplicate key "%s" in verification report.%s[%d] near line %d.',
                        $matches[1],
                        $listKey,
                        $entryIndex,
                        $lineNumber,
                    ));
                }

                $currentEntry[$matches[1]] = $this->parseScalar($matches[2]);
                $currentEntrySeenKeys[$matches[1]] = true;
                $index++;
                continue;
            }

            throw new InvalidArgumentException(sprintf('Malformed verification report line: "%s"', $line));
        }

        if ($currentEntry !== null) {
            $entries[] = $currentEntry;
        }

        return $entries;
    }

    private function parseScalar(string $value): mixed
    {
        $trimmed = trim($value);
        if ($trimmed === 'null') {
            return null;
        }

        if ($trimmed === 'true') {
            return true;
        }

        if ($trimmed === 'false') {
            return false;
        }

        if (
            strlen($trimmed) >= 2
            && $trimmed[0] === '"'
            && $trimmed[strlen($trimmed) - 1] === '"'
        ) {
            return str_replace(
                ['\\n', '\\"', '\\\\'],
                ["\n", '"', '\\'],
                substr($trimmed, 1, -1),
            );
        }

        if (
            strlen($trimmed) >= 2
            && $trimmed[0] === '\''
            && $trimmed[strlen($trimmed) - 1] === '\''
        ) {
            return substr($trimmed, 1, -1);
        }

        if (preg_match('/^-?\d+$/', $trimmed) === 1) {
            return (int) $trimmed;
        }

        if (preg_match('/^-?(?:\d+\.?\d*|\.\d+)$/', $trimmed) === 1) {
            return (float) $trimmed;
        }

        return $trimmed;
    }
}
