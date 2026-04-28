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

use Bitrix24\SDK\Attributes\OpenApiEntity;
use Bitrix24\SDK\Services\AbstractItemBuilder;
use Bitrix24\SDK\Services\AbstractSelectBuilder;
use ReflectionClass;
use ReflectionException;
use Throwable;

class V3BuilderCoverageAuditor
{
    public function __construct(private readonly OpenApiSchemaEntityReader $schemaEntityReader)
    {
    }

    /**
     * @param list<class-string> $sdkClassNames All PHP classes loaded from src/Services/<scope>/
     */
    public function audit(string $schemaFile, array $sdkClassNames): V3BuilderCoverageReport
    {
        // Step 1: fetch all entity keys from the OpenAPI snapshot
        $entityKeys = $this->schemaEntityReader->getEntityKeys($schemaFile);
        $entityKeySet = array_flip($entityKeys);

        // Step 2: filter to classes bearing #[OpenApiEntity]
        $annotatedClasses = $this->filterAnnotatedClasses($sdkClassNames);

        // Filter schema entity keys to those belonging to the same module prefixes
        // as the annotated classes (e.g. bitrix.tasks.* when scanning task scope).
        // This prevents entities from unrelated scopes (bitrix.main.*, bitrix.rest.*)
        // from appearing as unmapped when auditing a single scope.
        $modulePrefixes = $this->deriveModulePrefixes($annotatedClasses);
        if ($modulePrefixes !== []) {
            $entityKeys = array_values(array_filter(
                $entityKeys,
                static function (string $key) use ($modulePrefixes): bool {
                    foreach ($modulePrefixes as $prefix) {
                        if (str_starts_with($key, $prefix . '.')) {
                            return true;
                        }
                    }
                    return false;
                }
            ));
            $entityKeySet = array_flip($entityKeys);
        }

        // Exclude sub-entities and orphaned DTOs:
        // - sub-entities: $ref targets used only inside components/schemas (embedded value types)
        // - orphaned DTOs: defined in schema but not referenced in any API path
        // Only keep entity keys that appear in at least one API path $ref,
        // OR are explicitly claimed by an SDK class (which may pre-declare a root entity).
        $sdkDeclaredKeys = array_flip(
            array_map(static fn ($e) => $e[1]->entityKey, $annotatedClasses)
        );
        $apiUsedKeys = array_flip(
            $this->schemaEntityReader->getEntityKeysUsedInApiPaths($schemaFile)
        );
        $entityKeys = array_values(array_filter(
            $entityKeys,
            static fn (string $key): bool =>
                isset($apiUsedKeys[$key]) || isset($sdkDeclaredKeys[$key])
        ));
        $entityKeySet = array_flip($entityKeys);

        // Step 3: group by entityKey to detect duplicates; build clean mapping
        $grouped = [];
        foreach ($annotatedClasses as [$className, $attr]) {
            $grouped[$attr->entityKey][] = [$className, $attr];
        }

        // Step 7: detect duplicates
        $duplicateEntityKeyMappings = [];
        /** @var array<string, array{0: class-string, 1: OpenApiEntity}> $mapping */
        $mapping = [];
        foreach ($grouped as $entityKey => $entries) {
            if (count($entries) > 1) {
                $duplicateEntityKeyMappings[] = [
                    'entityKey' => $entityKey,
                    'resultClasses' => array_values(array_map(static fn ($e) => $e[0], $entries)),
                ];
                continue; // skip duplicates from further validation
            }
            $mapping[$entityKey] = $entries[0];
        }

        // Step 5: detect SDK-only mappings (entityKey not in snapshot)
        $sdkOnlyMappings = [];
        $validMapping = [];
        foreach ($mapping as $entityKey => [$className, $attr]) {
            if (!isset($entityKeySet[$entityKey])) {
                $sdkOnlyMappings[] = ['resultClass' => $className, 'entityKey' => $entityKey];
            } else {
                $validMapping[$entityKey] = [$className, $attr];
            }
        }

        // Step 4: validate each mapped entity
        $missingSelectBuilders = [];
        $missingItemBuilders = [];
        $invalidBuilderReferences = [];
        $selectCoverageMismatches = [];
        $entitiesWithSelectBuilder = 0;
        $entitiesWithItemBuilder = 0;

        foreach ($validMapping as $entityKey => [$className, $attr]) {
            $this->validateSelectBuilder(
                $entityKey,
                $attr,
                $schemaFile,
                $missingSelectBuilders,
                $invalidBuilderReferences,
                $selectCoverageMismatches,
                $entitiesWithSelectBuilder,
            );

            $this->validateItemBuilder(
                $entityKey,
                $attr,
                $missingItemBuilders,
                $invalidBuilderReferences,
                $entitiesWithItemBuilder,
            );
        }

        // Unmapped: entityKeys in snapshot without any SDK class
        $unmappedEntities = [];
        foreach ($entityKeys as $entityKey) {
            if (!isset($mapping[$entityKey])) {
                $unmappedEntities[] = $entityKey;
            }
        }

        return new V3BuilderCoverageReport(
            totalOpenApiEntities: count($entityKeys),
            mappedEntities: count($validMapping),
            entitiesWithSelectBuilder: $entitiesWithSelectBuilder,
            entitiesWithItemBuilder: $entitiesWithItemBuilder,
            unmappedEntities: $unmappedEntities,
            missingSelectBuilders: $missingSelectBuilders,
            missingItemBuilders: $missingItemBuilders,
            invalidBuilderReferences: $invalidBuilderReferences,
            selectCoverageMismatches: $selectCoverageMismatches,
            sdkOnlyMappings: $sdkOnlyMappings,
            duplicateEntityKeyMappings: $duplicateEntityKeyMappings,
        );
    }

    /**
     * Extracts unique two-segment module prefixes from entity keys of annotated classes.
     * e.g. 'bitrix.tasks.taskdto' → 'bitrix.tasks'
     *
     * @param list<array{0: class-string, 1: OpenApiEntity}> $annotatedClasses
     * @return list<string>
     */
    private function deriveModulePrefixes(array $annotatedClasses): array
    {
        $prefixes = [];
        foreach ($annotatedClasses as [$_className, $attr]) {
            $parts = explode('.', $attr->entityKey);
            if (count($parts) >= 2) {
                $prefix = $parts[0] . '.' . $parts[1];
                $prefixes[$prefix] = true;
            }
        }

        return array_keys($prefixes);
    }

    /**
     * @param list<class-string> $classNames
     * @return list<array{0: class-string, 1: OpenApiEntity}>
     */
    private function filterAnnotatedClasses(array $classNames): array
    {
        $result = [];
        foreach ($classNames as $className) {
            try {
                $attrs = (new ReflectionClass($className))->getAttributes(OpenApiEntity::class);
                if ($attrs !== []) {
                    /** @var OpenApiEntity $instance */
                    $instance = $attrs[0]->newInstance();
                    $result[] = [$className, $instance];
                }
            } catch (ReflectionException) {
                // skip unloadable classes
            }
        }

        return $result;
    }

    /**
     * @param list<string>                                                 $missingSelectBuilders
     * @param list<array{entityKey: string, class: string, reason: string}> $invalidBuilderReferences
     * @param list<array{entityKey: string, builderClass: string, missingFields: list<string>}> $selectCoverageMismatches
     */
    private function validateSelectBuilder(
        string $entityKey,
        OpenApiEntity $attr,
        string $schemaFile,
        array &$missingSelectBuilders,
        array &$invalidBuilderReferences,
        array &$selectCoverageMismatches,
        int &$entitiesWithSelectBuilder,
    ): void {
        if ($attr->selectBuilder === null) {
            $missingSelectBuilders[] = $entityKey;
            return;
        }

        $entitiesWithSelectBuilder++;

        if (!class_exists($attr->selectBuilder)) {
            $invalidBuilderReferences[] = [
                'entityKey' => $entityKey,
                'class' => $attr->selectBuilder,
                'reason' => 'class does not exist',
            ];
            return;
        }

        if (!is_subclass_of($attr->selectBuilder, AbstractSelectBuilder::class)) {
            $invalidBuilderReferences[] = [
                'entityKey' => $entityKey,
                'class' => $attr->selectBuilder,
                'reason' => sprintf('does not extend %s', AbstractSelectBuilder::class),
            ];
            return;
        }

        // Step 6: wrap instantiation in try/catch
        try {
            $builderClass = $attr->selectBuilder;
            /** @var AbstractSelectBuilder $builder */
            $builder = new $builderClass();
            $covered = $builder->allSystemFields()->buildSelect();
        } catch (Throwable $e) {
            $invalidBuilderReferences[] = [
                'entityKey' => $entityKey,
                'class' => $attr->selectBuilder,
                'reason' => sprintf('instantiation failed: %s', $e->getMessage()),
            ];
            return;
        }

        $schemaFields = $this->schemaEntityReader->getSelectableFields($schemaFile, $entityKey);
        $missing = array_values(array_diff($schemaFields, $covered));
        if ($missing !== []) {
            $selectCoverageMismatches[] = [
                'entityKey' => $entityKey,
                'builderClass' => $attr->selectBuilder,
                'missingFields' => $missing,
            ];
        }
    }

    /**
     * @param list<string>                                                 $missingItemBuilders
     * @param list<array{entityKey: string, class: string, reason: string}> $invalidBuilderReferences
     */
    private function validateItemBuilder(
        string $entityKey,
        OpenApiEntity $attr,
        array &$missingItemBuilders,
        array &$invalidBuilderReferences,
        int &$entitiesWithItemBuilder,
    ): void {
        if ($attr->itemBuilder === null) {
            $missingItemBuilders[] = $entityKey;
            return;
        }

        $entitiesWithItemBuilder++;

        if (!class_exists($attr->itemBuilder)) {
            $invalidBuilderReferences[] = [
                'entityKey' => $entityKey,
                'class' => $attr->itemBuilder,
                'reason' => 'class does not exist',
            ];
            return;
        }

        if (!is_subclass_of($attr->itemBuilder, AbstractItemBuilder::class)) {
            $invalidBuilderReferences[] = [
                'entityKey' => $entityKey,
                'class' => $attr->itemBuilder,
                'reason' => sprintf('does not extend %s', AbstractItemBuilder::class),
            ];
        }
    }
}
