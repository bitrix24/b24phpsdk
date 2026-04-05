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

use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

readonly class OaSchemaEntityReader
{
    public function __construct(private Filesystem $filesystem)
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
        $keys = array_keys($schema['components']['schemas'] ?? []);
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
     * @return array<string, mixed>
     */
    private function loadSchema(string $schemaFile): array
    {
        if (!$this->filesystem->exists($schemaFile)) {
            throw new RuntimeException(sprintf('OpenAPI schema file "%s" not found', $schemaFile));
        }

        $payload = file_get_contents($schemaFile);
        if ($payload === false) {
            throw new RuntimeException(sprintf('Unable to read OpenAPI schema file "%s"', $schemaFile));
        }

        /** @var array<string, mixed> */
        return json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
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
     * @return array<string, mixed>
     */
    private function resolveRef(array $schema, string $ref): array
    {
        // $ref format: #/components/schemas/<key>
        $key = ltrim(str_replace('/components/schemas/', '', $ref), '#/');
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
}
