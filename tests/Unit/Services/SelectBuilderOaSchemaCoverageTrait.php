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

namespace Bitrix24\SDK\Tests\Unit\Services;

use Bitrix24\SDK\Attributes\OpenApiEntity;
use Bitrix24\SDK\OpenApi\Domain\OpenApiSchemaEntityReader;
use Bitrix24\SDK\Services\AbstractSelectBuilder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Trait that verifies a concrete *SelectBuilder covers every field
 * listed in the OpenAPI schema snapshot for its entity.
 *
 * A test failure here means the SelectBuilder is out of sync with the schema —
 * run `php bin/console b24-dev:generate-select-builder` to regenerate.
 *
 * Usage: implement getItemResultClass() returning the FQN of the *ItemResult class
 * that carries the #[OpenApiEntity] attribute. The entity key is read from that attribute.
 */
trait SelectBuilderOaSchemaCoverageTrait
{
    private const string SCHEMA_FILE = 'docs/open-api/openapi.json';

    /**
     * Fully-qualified class name of the *ItemResult that carries #[OpenApiEntity].
     *
     * @return class-string
     */
    abstract protected function getItemResultClass(): string;

    abstract protected function getSelectBuilder(): AbstractSelectBuilder;

    private function resolveEntityKey(): string
    {
        $resultClass = $this->getItemResultClass();
        $attrs = (new \ReflectionClass($resultClass))->getAttributes(OpenApiEntity::class);
        $this->assertNotEmpty(
            $attrs,
            sprintf('Class %s has no #[OpenApiEntity] attribute', $resultClass)
        );

        /** @var OpenApiEntity $oaEntity */
        $oaEntity = $attrs[0]->newInstance();

        return $oaEntity->entityKey;
    }

    #[Test]
    #[TestDox('every field from the OA schema is selectable via allSystemFields()')]
    public function testAllOaSchemaFieldsCovered(): void
    {
        $entityKey = $this->resolveEntityKey();

        $schemaFields = (new OpenApiSchemaEntityReader(new Filesystem()))
            ->getSelectableFields(self::SCHEMA_FILE, $entityKey);

        $selected = $this->getSelectBuilder()->allSystemFields()->buildSelect();

        foreach ($schemaFields as $schemaField) {
            $this->assertContains(
                $schemaField,
                $selected,
                sprintf(
                    'Field "%s" from OA schema entity "%s" is not covered by %s. ' .
                    'Run: php bin/console b24-dev:generate-select-builder %s',
                    $schemaField,
                    $entityKey,
                    static::class,
                    $entityKey
                )
            );
        }
    }
}
