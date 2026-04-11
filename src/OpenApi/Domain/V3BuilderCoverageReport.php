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

final class V3BuilderCoverageReport
{
    /**
     * @param list<string>                                                                       $unmappedEntities         entityKeys present in OA snapshot but without an SDK mapping
     * @param list<string>                                                                       $missingSelectBuilders    entityKeys whose result class lacks a selectBuilder
     * @param list<string>                                                                       $missingItemBuilders      entityKeys whose result class lacks an itemBuilder
     * @param list<array{entityKey: string, class: string, reason: string}>                     $invalidBuilderReferences broken class references or wrong builder base types
     * @param list<array{entityKey: string, builderClass: string, missingFields: list<string>}> $selectCoverageMismatches SelectBuilder does not cover all OpenAPI fields
     * @param list<array{resultClass: string, entityKey: string}>                               $sdkOnlyMappings          SDK mappings pointing to unknown entityKeys
     * @param list<array{entityKey: string, resultClasses: list<string>}>                       $duplicateEntityKeyMappings multiple result classes sharing the same entityKey
     */
    public function __construct(
        public readonly int   $totalOpenApiEntities,
        public readonly int   $mappedEntities,
        public readonly int   $entitiesWithSelectBuilder,
        public readonly int   $entitiesWithItemBuilder,
        public readonly array $unmappedEntities,
        public readonly array $missingSelectBuilders,
        public readonly array $missingItemBuilders,
        public readonly array $invalidBuilderReferences,
        public readonly array $selectCoverageMismatches,
        public readonly array $sdkOnlyMappings,
        public readonly array $duplicateEntityKeyMappings,
    ) {
    }
}
