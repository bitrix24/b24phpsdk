<?php

declare(strict_types=1);

namespace Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload;

use InvalidArgumentException;

final class ResultItemPayloadBuilder
{
    public function build(?ResultItemPayload $openApiPayload, ?ResultItemPayload $docsPayload): ResultItemPayload
    {
        if ($openApiPayload === null && $docsPayload === null) {
            throw new InvalidArgumentException('At least one payload source must be provided');
        }

        if ($openApiPayload === null) {
            return $this->withGeneratedFrom($docsPayload, ['b24restdocs']);
        }

        if ($docsPayload === null) {
            return $this->withGeneratedFrom($openApiPayload, ['openapi']);
        }

        if (
            $openApiPayload->method !== $docsPayload->method
            || $openApiPayload->object !== $docsPayload->object
        ) {
            throw new InvalidArgumentException('Cannot merge payloads with different method/object identity');
        }

        return new ResultItemPayload(
            method: $openApiPayload->method,
            object: $openApiPayload->object,
            generatedFrom: ['openapi', 'b24restdocs'],
            fields: $this->mergeFields($openApiPayload->fields, $docsPayload->fields),
            sections: $this->mergeSections($openApiPayload->sections, $docsPayload->sections),
            version: $openApiPayload->version,
        );
    }

    /**
     * @param list<ResultItemPayloadField> $openApiFields
     * @param list<ResultItemPayloadField> $docsFields
     * @return list<ResultItemPayloadField>
     */
    private function mergeFields(array $openApiFields, array $docsFields): array
    {
        $fields = [];
        $docsByCode = $this->indexFieldsByCode($docsFields);

        foreach ($openApiFields as $openApiField) {
            $docsField = $docsByCode[$openApiField->code] ?? null;
            if ($docsField === null) {
                $fields[] = $openApiField;
                continue;
            }

            unset($docsByCode[$openApiField->code]);
            $fields[] = $this->mergeField($openApiField, $docsField);
        }

        foreach ($docsByCode as $docsField) {
            $fields[] = $docsField;
        }

        return $fields;
    }

    /**
     * @param list<ResultItemPayloadSection> $openApiSections
     * @param list<ResultItemPayloadSection> $docsSections
     * @return list<ResultItemPayloadSection>
     */
    private function mergeSections(array $openApiSections, array $docsSections): array
    {
        $sections = [];
        $docsByName = [];
        foreach ($docsSections as $section) {
            $docsByName[$section->name] = $section;
        }

        foreach ($openApiSections as $openApiSection) {
            $docsSection = $docsByName[$openApiSection->name] ?? null;
            if ($docsSection === null) {
                $sections[] = $openApiSection;
                continue;
            }

            unset($docsByName[$openApiSection->name]);
            $sections[] = new ResultItemPayloadSection(
                name: $openApiSection->name,
                kind: $openApiSection->kind,
                source: 'openapi+b24restdocs',
                fields: $this->mergeFields($openApiSection->fields, $docsSection->fields),
            );
        }

        foreach ($docsByName as $docsSection) {
            $sections[] = $docsSection;
        }

        return $sections;
    }

    private function mergeField(ResultItemPayloadField $openApiField, ResultItemPayloadField $docsField): ResultItemPayloadField
    {
        $notes = array_values(array_filter([
            $openApiField->notes,
            $docsField->notes,
            $this->buildConflictNotes($openApiField, $docsField),
        ], static fn(?string $note): bool => $note !== null && trim($note) !== ''));

        return new ResultItemPayloadField(
            code: $openApiField->code,
            sourceType: $openApiField->sourceType,
            phpdocType: $openApiField->phpdocType,
            format: $docsField->format ?? $openApiField->format,
            required: $openApiField->required,
            nullable: $openApiField->nullable,
            source: 'openapi+b24restdocs',
            description: $docsField->description ?? $openApiField->description,
            notes: $notes === [] ? null : implode("\n", $notes),
        );
    }

    private function buildConflictNotes(ResultItemPayloadField $openApiField, ResultItemPayloadField $docsField): ?string
    {
        $conflicts = [];

        if ($openApiField->sourceType !== $docsField->sourceType) {
            $conflicts[] = sprintf(
                'docs source_type=%s, openapi source_type=%s',
                $docsField->sourceType,
                $openApiField->sourceType,
            );
        }

        if ($openApiField->phpdocType !== $docsField->phpdocType) {
            $conflicts[] = sprintf(
                'docs phpdoc_type=%s, openapi phpdoc_type=%s',
                $docsField->phpdocType,
                $openApiField->phpdocType,
            );
        }

        if ($openApiField->required !== $docsField->required || $openApiField->nullable !== $docsField->nullable) {
            $conflicts[] = sprintf(
                'docs required=%s, nullable=%s; openapi required=%s, nullable=%s',
                $docsField->required ? 'true' : 'false',
                $docsField->nullable ? 'true' : 'false',
                $openApiField->required ? 'true' : 'false',
                $openApiField->nullable ? 'true' : 'false',
            );
        }

        if (
            $openApiField->description !== null
            && $docsField->description !== null
            && $openApiField->description !== $docsField->description
        ) {
            $conflicts[] = sprintf(
                'docs description="%s", openapi description="%s"',
                $docsField->description,
                $openApiField->description,
            );
        }

        if (
            $openApiField->format !== null
            && $docsField->format !== null
            && $openApiField->format !== $docsField->format
        ) {
            $conflicts[] = sprintf(
                'docs format=%s, openapi format=%s',
                $docsField->format,
                $openApiField->format,
            );
        }

        if ($conflicts === []) {
            return null;
        }

        return 'Merge conflicts: ' . implode('; ', $conflicts);
    }

    /**
     * @param list<ResultItemPayloadField> $fields
     * @return array<string, ResultItemPayloadField>
     */
    private function indexFieldsByCode(array $fields): array
    {
        $indexedFields = [];
        foreach ($fields as $field) {
            $indexedFields[$field->code] = $field;
        }

        return $indexedFields;
    }

    /**
     * @param list<string> $generatedFrom
     */
    private function withGeneratedFrom(ResultItemPayload $payload, array $generatedFrom): ResultItemPayload
    {
        return new ResultItemPayload(
            method: $payload->method,
            object: $payload->object,
            generatedFrom: $generatedFrom,
            fields: $payload->fields,
            sections: $payload->sections,
            version: $payload->version,
        );
    }
}
