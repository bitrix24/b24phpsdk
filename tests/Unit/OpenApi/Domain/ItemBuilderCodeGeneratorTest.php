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

use Bitrix24\SDK\CodeGenerator\ItemBuilderCodeGenerator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(ItemBuilderCodeGenerator::class)]
class ItemBuilderCodeGeneratorTest extends TestCase
{
    private ItemBuilderCodeGenerator $generator;

    #[\Override]
    protected function setUp(): void
    {
        $this->generator = new ItemBuilderCodeGenerator();
    }

    #[Test]
    #[TestDox('generated class has correct namespace, class name and parent')]
    public function testGeneratesCorrectClassSignature(): void
    {
        $code = $this->generator->generate(
            'Bitrix24\SDK\Services\Task\Service',
            'TaskItemBuilder',
            ['title' => 'string']
        );

        $this->assertStringContainsString('namespace Bitrix24\SDK\Services\Task\Service;', $code);
        $this->assertStringContainsString('class TaskItemBuilder extends AbstractItemBuilder', $code);
        $this->assertStringContainsString('use Bitrix24\SDK\Services\AbstractItemBuilder;', $code);
    }

    #[Test]
    #[TestDox('string field generates a typed setter method')]
    public function testStringFieldGeneratesTypedSetter(): void
    {
        $code = $this->generator->generate(
            'Bitrix24\SDK\Services\Task\Service',
            'TaskItemBuilder',
            ['title' => 'string']
        );

        $this->assertStringContainsString('public function title(string $title): self', $code);
        $this->assertStringContainsString("\$this->fields['title'] = \$title;", $code);
    }

    #[Test]
    #[TestDox('OpenAPI types are mapped to PHP types correctly')]
    public function testOpenApiTypesMappedToPhpTypes(): void
    {
        $code = $this->generator->generate(
            'Bitrix24\SDK\Services\Task\Service',
            'TaskItemBuilder',
            [
                'title'        => 'string',
                'responsibleId'=> 'integer',
                'needsControl' => 'boolean',
                'tags'         => 'array',
            ]
        );

        $this->assertStringContainsString('public function title(string $title): self', $code);
        $this->assertStringContainsString('public function responsibleId(int $responsibleId): self', $code);
        $this->assertStringContainsString('public function needsControl(bool $needsControl): self', $code);
        $this->assertStringContainsString('public function tags(array $tags): self', $code);
    }

    #[Test]
    #[TestDox('fields with unknown OpenAPI types (e.g. object) are silently skipped')]
    public function testUnknownTypesAreSkipped(): void
    {
        $code = $this->generator->generate(
            'Bitrix24\SDK\Services\Task\Service',
            'TaskItemBuilder',
            [
                'title'      => 'string',
                'parentTask' => 'object',
            ]
        );

        $this->assertStringContainsString('public function title(string $title): self', $code);
        $this->assertStringNotContainsString('parentTask', $code);
    }

    #[Test]
    #[TestDox('methods are emitted in alphabetical order for determinism')]
    public function testMethodsAreSortedAlphabetically(): void
    {
        $code = $this->generator->generate(
            'Bitrix24\SDK\Services\Task\Service',
            'TaskItemBuilder',
            [
                'title'        => 'string',
                'active'       => 'boolean',
                'description'  => 'string',
            ]
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
    #[TestDox('generated code contains declare strict_types=1')]
    public function testGeneratedCodeHasStrictTypes(): void
    {
        $code = $this->generator->generate(
            'Bitrix24\SDK\Services\Task\Service',
            'TaskItemBuilder',
            ['title' => 'string']
        );

        $this->assertStringContainsString('declare(strict_types=1);', $code);
    }

    #[Test]
    #[TestDox('empty writable fields generates a valid class with no setter methods')]
    public function testGenerateWithNoFields(): void
    {
        $code = $this->generator->generate(
            'Bitrix24\SDK\Services\Task\Service',
            'TaskItemBuilder',
            []
        );

        $this->assertStringContainsString('class TaskItemBuilder extends AbstractItemBuilder', $code);
        $this->assertStringNotContainsString('public function ', $code);
    }
}
