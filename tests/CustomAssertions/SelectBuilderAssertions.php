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

namespace Bitrix24\SDK\Tests\CustomAssertions;

use Bitrix24\SDK\Attributes\OpenApiEntity;
use Bitrix24\SDK\OpenApi\Domain\Schema\OpenApiSchemaEntityReader;
use Bitrix24\SDK\Services\AbstractSelectBuilder;
use PHPUnit\Framework\Assert;
use Symfony\Component\Filesystem\Filesystem;

class SelectBuilderAssertions extends Assert
{
    private const string SCHEMA_FILE = 'docs/open-api/openapi.json';

    /**
     * Assert that every field from the OpenAPI schema entity
     * (resolved via #[OpenApiEntity] on $resultClass) is selectable
     * via allSystemFields()->buildSelect() on $builder.
     *
     * @param class-string $resultClass  *ItemResult annotated with #[OpenApiEntity]
     */
    public static function assertCoversOpenApiSchema(
        AbstractSelectBuilder $builder,
        string $resultClass
    ): void {
        $attrs = (new \ReflectionClass($resultClass))->getAttributes(OpenApiEntity::class);

        self::assertNotEmpty(
            $attrs,
            sprintf('Class %s has no #[OpenApiEntity] attribute', $resultClass)
        );

        /** @var OpenApiEntity $openApiEntity */
        $openApiEntity = $attrs[0]->newInstance();
        $entityKey = $openApiEntity->entityKey;

        $schemaFields = (new OpenApiSchemaEntityReader(new Filesystem()))
            ->getSelectableFields(self::SCHEMA_FILE, $entityKey);

        $selected = $builder->allSystemFields()->buildSelect();

        foreach ($schemaFields as $field) {
            self::assertContains(
                $field,
                $selected,
                sprintf(
                    'field «%s» from OpenAPI schema «%s» is not covered by %s — ' .
                    'run: php bin/console b24-dev:generate-select-builder %s',
                    $field,
                    $entityKey,
                    $builder::class,
                    $entityKey
                )
            );
        }
    }
}
