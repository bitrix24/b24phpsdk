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

use Bitrix24\SDK\CodeGenerator\SelectBuilderCodeGenerator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Bitrix24\SDK\CodeGenerator\SelectBuilderCodeGenerator::class)]
class SelectBuilderCodeGeneratorTest extends TestCase
{
    private SelectBuilderCodeGenerator $generator;

    #[\Override]
    protected function setUp(): void
    {
        $this->generator = new SelectBuilderCodeGenerator();
    }

    #[Test]
    #[TestDox('generated class has correct namespace and class name')]
    public function testGeneratesCorrectClassSignature(): void
    {
        $code = $this->generator->generate(
            'Bitrix24\SDK\Services\Task\Service',
            'TaskSelectBuilder',
            ['id', 'title']
        );

        $this->assertStringContainsString('namespace Bitrix24\SDK\Services\Task\Service;', $code);
        $this->assertStringContainsString('class TaskSelectBuilder extends AbstractSelectBuilder', $code);
        $this->assertStringContainsString('use Bitrix24\SDK\Services\AbstractSelectBuilder;', $code);
    }

    #[Test]
    #[TestDox('id field is placed in constructor, not as a method')]
    public function testIdIsInConstructorNotAsMethod(): void
    {
        $code = $this->generator->generate(
            'Bitrix24\SDK\Services\Task\Service',
            'TaskSelectBuilder',
            ['id', 'title']
        );

        $this->assertStringContainsString("\$this->select[] = 'id';", $code);
        $this->assertStringNotContainsString('public function id()', $code);
    }

    #[Test]
    #[TestDox('simple scalar fields get a zero-parameter method each')]
    public function testSimpleFieldsGetSingleMethods(): void
    {
        $code = $this->generator->generate(
            'Bitrix24\SDK\Services\Task\Service',
            'TaskSelectBuilder',
            ['id', 'title', 'active']
        );

        $this->assertStringContainsString("public function title(): self", $code);
        $this->assertStringContainsString("\$this->select[] = 'title';", $code);
        $this->assertStringContainsString("public function active(): self", $code);
        $this->assertStringContainsString("\$this->select[] = 'active';", $code);
    }

    #[Test]
    #[TestDox('dot-notation fields sharing a prefix are grouped into one array_merge method')]
    public function testDotNotationFieldsGroupedIntoOneMethod(): void
    {
        $code = $this->generator->generate(
            'Bitrix24\SDK\Services\Task\Service',
            'TaskSelectBuilder',
            ['id', 'chat.id', 'chat.entityId', 'chat.entityType']
        );

        $this->assertStringContainsString('public function chat(): self', $code);
        $this->assertStringContainsString("array_merge(\$this->select, ['chat.id', 'chat.entityId', 'chat.entityType'])", $code);
        $this->assertStringNotContainsString('public function chat.id()', $code);
    }

    #[Test]
    #[TestDox('methods are emitted in alphabetical order for determinism')]
    public function testMethodsAreSortedAlphabetically(): void
    {
        $code = $this->generator->generate(
            'Bitrix24\SDK\Services\Task\Service',
            'TaskSelectBuilder',
            ['id', 'title', 'active', 'description']
        );

        $posActive = strpos($code, 'function active');
        $posDescription = strpos($code, 'function description');
        $posTitle = strpos($code, 'function title');

        $this->assertNotFalse($posActive);
        $this->assertNotFalse($posDescription);
        $this->assertNotFalse($posTitle);
        $this->assertLessThan($posDescription, $posActive);
        $this->assertLessThan($posTitle, $posDescription);
    }

    #[Test]
    #[TestDox('when only id is provided no field methods are generated')]
    public function testGenerateWithOnlyId(): void
    {
        $code = $this->generator->generate(
            'Bitrix24\SDK\Services\Task\Service',
            'TaskSelectBuilder',
            ['id']
        );

        $this->assertStringContainsString("\$this->select[] = 'id';", $code);
        // constructor is present but no additional field methods
        $this->assertSame(1, substr_count($code, 'public function '));
    }

    #[Test]
    #[TestDox('generated code contains declare strict_types=1')]
    public function testGeneratedCodeHasStrictTypes(): void
    {
        $code = $this->generator->generate(
            'Bitrix24\SDK\Services\Task\Service',
            'TaskSelectBuilder',
            ['id']
        );

        $this->assertStringContainsString('declare(strict_types=1);', $code);
    }
}
