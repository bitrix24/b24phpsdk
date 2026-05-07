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

use Bitrix24\SDK\Services\AbstractSelectBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractSelectBuilder::class)]
class AbstractSelectBuilderTest extends TestCase
{
    #[Test]
    #[TestDox('allSystemFields() includes all field methods declared in the concrete class')]
    public function testAllSystemFieldsCollectsAllDeclaredFields(): void
    {
        $builder = new class extends AbstractSelectBuilder {
            public function __construct()
            {
                $this->select[] = 'id';
            }

            public function name(): self
            {
                $this->select[] = 'name';
                return $this;
            }

            public function email(): self
            {
                $this->select[] = 'email';
                return $this;
            }
        };

        $result = $builder->allSystemFields()->buildSelect();

        $this->assertContains('id', $result);
        $this->assertContains('name', $result);
        $this->assertContains('email', $result);
        $this->assertCount(3, $result);
    }

    #[Test]
    #[TestDox('allSystemFields() called twice does not produce duplicate fields')]
    public function testAllSystemFieldsIsIdempotent(): void
    {
        $builder = new class extends AbstractSelectBuilder {
            public function __construct()
            {
                $this->select[] = 'id';
            }

            public function name(): self
            {
                $this->select[] = 'name';
                return $this;
            }

            public function email(): self
            {
                $this->select[] = 'email';
                return $this;
            }
        };

        $result = $builder->allSystemFields()->allSystemFields()->buildSelect();

        $this->assertCount(3, $result);
    }

    #[Test]
    #[TestDox('allSystemFields() chains correctly with withUserFields()')]
    public function testAllSystemFieldsChainWithUserFields(): void
    {
        $builder = new class extends AbstractSelectBuilder {
            public function __construct()
            {
                $this->select[] = 'id';
            }

            public function title(): self
            {
                $this->select[] = 'title';
                return $this;
            }
        };

        $result = $builder->allSystemFields()->withUserFields(['UF_CUSTOM'])->buildSelect();

        $this->assertContains('id', $result);
        $this->assertContains('title', $result);
        $this->assertContains('UF_CUSTOM', $result);
        $this->assertCount(3, $result);
    }

    #[Test]
    #[TestDox('allSystemFields() correctly handles methods that add multiple fields at once')]
    public function testAllSystemFieldsWithMultiFieldMethod(): void
    {
        $builder = new class extends AbstractSelectBuilder {
            public function __construct()
            {
                $this->select[] = 'id';
            }

            public function chat(): self
            {
                $this->select = array_merge($this->select, ['chat.id', 'chat.entityId']);
                return $this;
            }
        };

        $result = $builder->allSystemFields()->buildSelect();

        $this->assertContains('id', $result);
        $this->assertContains('chat.id', $result);
        $this->assertContains('chat.entityId', $result);
        $this->assertCount(3, $result);
    }

    #[Test]
    #[TestDox('allSystemFields() does not call methods requiring parameters')]
    public function testAllSystemFieldsSkipsParameterizedMethods(): void
    {
        $builder = new class extends AbstractSelectBuilder {
            public function __construct()
            {
                $this->select[] = 'id';
            }

            public function name(): self
            {
                $this->select[] = 'name';
                return $this;
            }

            public function customField(string $fieldName): self
            {
                $this->select[] = $fieldName;
                return $this;
            }
        };

        // customField('foo') is NOT called — only zero-param methods are auto-discovered
        $result = $builder->allSystemFields()->buildSelect();

        $this->assertContains('id', $result);
        $this->assertContains('name', $result);
        $this->assertNotContains('foo', $result);
        $this->assertCount(2, $result);
    }
}
