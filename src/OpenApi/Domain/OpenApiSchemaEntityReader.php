<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\OpenApi\Domain;

use Bitrix24\SDK\OpenApi\Domain\ResultItem\Field\ResultFieldDescriptor;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

class OpenApiSchemaEntityReader
{
    /** @var array<string, array<string, mixed>> */
    private array $schemaCache = [];

    public function __construct(private readonly Filesystem $filesystem)
    {
    }

    /**
     * Returns all entity keys from components.schemas, sorted alphabetically.
     *
     * @return list<string>
     */
    public function getEntityKeys(string $schemaFile): array
    {
        $schema = $this->loadSchema($schemaFile);
        /** @var array<string, mixed> $schemas */
        $schemas = $schema['components']['schemas'] ?? [];
        $keys = array_keys($schemas);
        sort($keys);

        return array_values($keys);
    }

    /**
     * Returns a flat sorted list of selectable field names for the given entity.
     *
     * Rules:
     * - 'id' is always first
     * - Simple scalar properties → flat field name
     * - $ref properties → expanded one level deep as 'fieldName.subField'
     * - array-of-$ref properties → flat field name only (no expansion)
     *
     * @return list<string>
     */
    public function getSelectableFields(string $schemaFile, string $entityKey): array
    {
        $schema = $this->loadSchema($schemaFile);
        $properties = $this->getEntityProperties($schema, $entityKey);

        $fields = [];
        foreach ($properties as $name => $definition) {
            if ($name === 'id') {
                continue;
            }

            if ($this->isRef($definition)) {
                $subProperties = $this->resolveRef($schema, $definition['$ref']);
                foreach (array_keys($subProperties) as $subName) {
                    $fields[] = $name . '.' . $subName;
                }
                continue;
            }

            if ($this->isArrayOfRefs($definition)) {
                $fields[] = $name;
                continue;
            }

            $fields[] = $name;
        }

        sort($fields);

        return array_values(array_merge(['id'], $fields));
    }

    /**
     * @return list<ResultFieldDescriptor>
     */
    public function getResultFields(string $schemaFile, string $entityKey): array
    {
        $schema = $this->loadSchema($schemaFile);
        $properties = $this->getEntityProperties($schema, $entityKey);
        $requiredFields = $this->getEntityRequiredFields($schema, $entityKey);

        $fields = [];
        foreach ($properties as $name => $definition) {
            $type = (string) ($definition['type'] ?? 'string');
            if ($this->isRef($definition)) {
                $type = 'object';
            }

            $fields[] = new ResultFieldDescriptor(
                name: (string) $name,
                type: $type,
                format: isset($definition['format']) ? (string) $definition['format'] : null,
                nullable: (bool) ($definition['nullable'] ?? false),
                description: $this->extractFieldDescription($definition),
                source: 'openapi',
                required: in_array((string) $name, $requiredFields, true),
            );
        }

        return $fields;
    }

    /**
     * Returns writable field names and their OpenAPI types for a given operation path.
     *
     * Reads from: paths/{operationPath}/post/requestBody/content/application/json/schema/properties/fields/properties
     *
     * Entries with '$ref' are mapped to the type 'object'.
     * Returns an alphabetically sorted map of fieldName → openApiType.
     *
     * @return array<string, string>
     * @throws RuntimeException when the operation path does not exist in the schema
     */
    public function getWritableFields(string $schemaFile, string $operationPath): array
    {
        $schema = $this->loadSchema($schemaFile);

        $node = $schema['paths'][$operationPath]['post']['requestBody']['content']['application/json']['schema']['properties']['fields']['properties'] ?? null;

        if ($node === null) {
            throw new RuntimeException(sprintf(
                'Operation path "%s" not found or has no writable fields in the schema',
                $operationPath
            ));
        }

        $result = [];
        foreach ($node as $fieldName => $definition) {
            if (isset($definition['$ref'])) {
                $result[$fieldName] = 'object';
                continue;
            }

            $result[$fieldName] = (string) ($definition['type'] ?? 'string');
        }

        ksort($result);

        return $result;
    }

    /**
     * Returns entity keys that appear as $ref targets anywhere inside the paths section.
     * These are the entity keys actually connected to an API method (request / response).
     * Sub-types referenced only inside components/schemas and orphaned DTOs with no path
     * reference are NOT included.
     *
     * @return list<string>
     */
    public function getEntityKeysUsedInApiPaths(string $schemaFile): array
    {
        $schema = $this->loadSchema($schemaFile);
        $paths = $schema['paths'] ?? [];

        $found = [];
        $this->collectRefs($paths, $found);

        return array_keys($found);
    }

    /**
     * Recursively collects all $ref values from a nested array node.
     *
     * @param mixed              $node
     * @param array<string,bool> $found
     */
    private function collectRefs(mixed $node, array &$found): void
    {
        if (!is_array($node)) {
            return;
        }

        if (isset($node['$ref']) && is_string($node['$ref'])) {
            $found[$this->extractKeyFromRef($node['$ref'])] = true;
        }

        foreach ($node as $value) {
            $this->collectRefs($value, $found);
        }
    }

    private function extractKeyFromRef(string $ref): string
    {
        // $ref format: #/components/schemas/<key>
        return ltrim(str_replace('/components/schemas/', '', $ref), '#/');
    }

    /**
     * @return array<string, mixed>
     */
    private function loadSchema(string $schemaFile): array
    {
        if (array_key_exists($schemaFile, $this->schemaCache)) {
            return $this->schemaCache[$schemaFile];
        }

        if (!$this->filesystem->exists($schemaFile)) {
            throw new RuntimeException(sprintf('OpenAPI schema file "%s" not found', $schemaFile));
        }

        $payload = file_get_contents($schemaFile);
        if ($payload === false) {
            throw new RuntimeException(sprintf('Unable to read OpenAPI schema file "%s"', $schemaFile));
        }

        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        $this->schemaCache[$schemaFile] = $decoded;

        return $this->schemaCache[$schemaFile];
    }

    /**
     * @param array<string, mixed> $schema
     * @return array<string, mixed>
     */
    private function getEntityProperties(array $schema, string $entityKey): array
    {
        $schemas = $schema['components']['schemas'] ?? [];
        if (!array_key_exists($entityKey, $schemas)) {
            throw new RuntimeException(sprintf('Entity "%s" not found in OpenAPI schema', $entityKey));
        }

        return $schemas[$entityKey]['properties'] ?? [];
    }

    /**
     * @param array<string, mixed> $schema
     * @return list<string>
     */
    private function getEntityRequiredFields(array $schema, string $entityKey): array
    {
        $schemas = $schema['components']['schemas'] ?? [];
        if (!array_key_exists($entityKey, $schemas) || !is_array($schemas[$entityKey])) {
            throw new RuntimeException(sprintf('Entity "%s" not found in OpenAPI schema', $entityKey));
        }

        $requiredFields = $schemas[$entityKey]['required'] ?? [];
        if (!is_array($requiredFields)) {
            return [];
        }

        return array_values(array_filter(
            $requiredFields,
            static fn(mixed $fieldName): bool => is_string($fieldName) && $fieldName !== ''
        ));
    }

    /**
     * @param array<string, mixed> $schema
     * @return array<string, mixed>
     */
    private function resolveRef(array $schema, string $ref): array
    {
        $key = $this->extractKeyFromRef($ref);
        $schemas = $schema['components']['schemas'] ?? [];

        return $schemas[$key]['properties'] ?? [];
    }

    /**
     * @param array<string, mixed> $definition
     */
    private function isRef(array $definition): bool
    {
        return isset($definition['$ref']) && !isset($definition['type']);
    }

    /**
     * @param array<string, mixed> $definition
     */
    private function isArrayOfRefs(array $definition): bool
    {
        return ($definition['type'] ?? '') === 'array'
            && isset($definition['items']['$ref']);
    }

    /**
     * @param array<string, mixed> $definition
     */
    private function extractFieldDescription(array $definition): ?string
    {
        if (isset($definition['description']) && is_string($definition['description']) && $definition['description'] !== '') {
            return $definition['description'];
        }

        if (isset($definition['title']) && is_string($definition['title']) && $definition['title'] !== '') {
            return $definition['title'];
        }

        return null;
    }
}
