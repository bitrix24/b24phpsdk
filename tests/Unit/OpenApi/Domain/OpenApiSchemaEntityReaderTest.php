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

namespace Bitrix24\SDK\Tests\Unit\OpenApi\Domain;

use Bitrix24\SDK\OpenApi\Domain\OpenApiSchemaEntityReader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

#[CoversClass(OpenApiSchemaEntityReader::class)]
class OpenApiSchemaEntityReaderTest extends TestCase
{
    private const string FIXTURE = __DIR__ . '/Fixtures/openapi-entity-schemas.json';

    private OpenApiSchemaEntityReader $reader;

    #[\Override]
    protected function setUp(): void
    {
        $this->reader = new OpenApiSchemaEntityReader(new Filesystem());
    }

    #[Test]
    #[TestDox('getEntityKeys returns all schema keys sorted alphabetically')]
    public function testGetEntityKeysSorted(): void
    {
        $keys = $this->reader->getEntityKeys(self::FIXTURE);

        $this->assertSame([
            'bitrix.test.addressdto',
            'bitrix.test.complexdto',
            'bitrix.test.simpledto',
            'bitrix.test.tagdto',
        ], $keys);
    }

    #[Test]
    #[TestDox('getSelectableFields returns id first, then other scalar fields sorted')]
    public function testGetSelectableFieldsForSimpleDto(): void
    {
        $fields = $this->reader->getSelectableFields(self::FIXTURE, 'bitrix.test.simpledto');

        $this->assertSame(['id', 'active', 'title'], $fields);
    }

    #[Test]
    #[TestDox('getSelectableFields expands $ref property one level deep using dot notation')]
    public function testGetSelectableFieldsExpandsRefOneLevel(): void
    {
        $fields = $this->reader->getSelectableFields(self::FIXTURE, 'bitrix.test.complexdto');

        $this->assertContains('address.city', $fields);
        $this->assertContains('address.street', $fields);
        $this->assertNotContains('address', $fields);
    }

    #[Test]
    #[TestDox('getSelectableFields does not expand array-of-$ref items, adds the field name as-is')]
    public function testGetSelectableFieldsDoesNotExpandArrayOfRefs(): void
    {
        $fields = $this->reader->getSelectableFields(self::FIXTURE, 'bitrix.test.complexdto');

        $this->assertContains('tags', $fields);
        $this->assertNotContains('tags.id', $fields);
        $this->assertNotContains('tags.name', $fields);
    }

    #[Test]
    #[TestDox('getSelectableFields throws RuntimeException when schema file does not exist')]
    public function testThrowsOnMissingSchemaFile(): void
    {
        $this->expectException(RuntimeException::class);

        $this->reader->getSelectableFields('/nonexistent/schema.json', 'bitrix.test.simpledto');
    }

    #[Test]
    #[TestDox('getSelectableFields throws RuntimeException when entity key is absent from schema')]
    public function testThrowsOnUnknownEntityKey(): void
    {
        $this->expectException(RuntimeException::class);

        $this->reader->getSelectableFields(self::FIXTURE, 'bitrix.test.unknowndto');
    }
}
