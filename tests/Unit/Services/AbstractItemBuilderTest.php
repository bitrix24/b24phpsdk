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

use Bitrix24\SDK\Services\AbstractItemBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractItemBuilder::class)]
class AbstractItemBuilderTest extends TestCase
{
    #[Test]
    #[TestDox('getSupportedFieldNames() returns only 1-param public instance methods of the concrete subclass')]
    public function testGetSupportedFieldNamesReturnsSubclassMethods(): void
    {
        $builder = new class extends AbstractItemBuilder {
            public function title(string $title): self
            {
                $this->fields['title'] = $title;
                return $this;
            }

            public function responsible(int $userId): self
            {
                $this->fields['responsibleId'] = $userId;
                return $this;
            }
        };

        $names = $builder->getSupportedFieldNames();

        $this->assertSame(['responsible', 'title'], $names);
    }

    #[Test]
    #[TestDox('getSupportedFieldNames() excludes static methods')]
    public function testGetSupportedFieldNamesExcludesStaticMethods(): void
    {
        $builder = new class extends AbstractItemBuilder {
            public function title(string $title): self
            {
                $this->fields['title'] = $title;
                return $this;
            }

            public static function create(string $title): static
            {
                $static = new static();
                $static->fields['title'] = $title;
                return $static;
            }
        };

        $names = $builder->getSupportedFieldNames();

        $this->assertSame(['title'], $names);
        $this->assertNotContains('create', $names);
    }

    #[Test]
    #[TestDox('getSupportedFieldNames() excludes methods with 0 or 2+ parameters')]
    public function testGetSupportedFieldNamesExcludesWrongParamCount(): void
    {
        $builder = new class extends AbstractItemBuilder {
            public function title(string $title): self
            {
                $this->fields['title'] = $title;
                return $this;
            }

            public function noParams(): self
            {
                return $this;
            }

            public function twoParams(string $a, string $b): self
            {
                $this->fields['a'] = $a . $b;
                return $this;
            }
        };

        $names = $builder->getSupportedFieldNames();

        $this->assertSame(['title'], $names);
    }

    #[Test]
    #[TestDox('getSupportedFieldNames() excludes base-class methods (build, withUserField, getSupportedFieldNames)')]
    public function testGetSupportedFieldNamesExcludesBaseClassMethods(): void
    {
        $builder = new class extends AbstractItemBuilder {
            public function deadLine(\DateTimeImmutable $date): self
            {
                $this->fields['deadLine'] = $date->format('c');
                return $this;
            }
        };

        $names = $builder->getSupportedFieldNames();

        $this->assertNotContains('build', $names);
        $this->assertNotContains('withUserField', $names);
        $this->assertNotContains('getSupportedFieldNames', $names);
        $this->assertSame(['deadLine'], $names);
    }

    #[Test]
    #[TestDox('getSupportedFieldNames() returns names sorted alphabetically')]
    public function testGetSupportedFieldNamesAreSortedAlphabetically(): void
    {
        $builder = new class extends AbstractItemBuilder {
            public function zebra(string $v): self
            {
                $this->fields['zebra'] = $v;
                return $this;
            }

            public function apple(string $v): self
            {
                $this->fields['apple'] = $v;
                return $this;
            }

            public function mango(string $v): self
            {
                $this->fields['mango'] = $v;
                return $this;
            }
        };

        $names = $builder->getSupportedFieldNames();

        $this->assertSame(['apple', 'mango', 'zebra'], $names);
    }

    #[Test]
    #[TestDox('methods with optional parameters (1 total) are included')]
    public function testGetSupportedFieldNamesIncludesMethodsWithOptionalParams(): void
    {
        $builder = new class extends AbstractItemBuilder {
            public function title(string $title): self
            {
                $this->fields['title'] = $title;
                return $this;
            }

            public function needsControl(bool $v = false): self
            {
                $this->fields['needsControl'] = $v;
                return $this;
            }
        };

        $names = $builder->getSupportedFieldNames();

        $this->assertContains('needsControl', $names);
        $this->assertContains('title', $names);
    }
}
