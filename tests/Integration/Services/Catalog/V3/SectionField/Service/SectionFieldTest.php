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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\V3\SectionField\Service;

use Bitrix24\SDK\Services\Catalog\V3\SectionField\Service\SectionField;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SectionField::class)]
class SectionFieldTest extends TestCase
{
    private SectionField $sectionField;

    #[\Override]
    protected function setUp(): void
    {
        $this->sectionField = Factory::getServiceBuilder()->getCatalogScope()->v3()->sectionField();
    }

    #[Test]
    public function testListReturnsAllSectionFields(): void
    {
        $names = array_map(static fn ($field): string => $field->name, $this->sectionField->list()->getFields());

        foreach (['id', 'iblockId', 'name', 'iblockSectionId', 'active', 'code', 'xmlId', 'sort', 'description', 'descriptionType'] as $name) {
            $this->assertContains($name, $names);
        }
    }

    #[Test]
    public function testGetReturnsFieldDescriptor(): void
    {
        $field = $this->sectionField->get('name')->field();

        $this->assertSame('name', $field->name);
        $this->assertSame('string', $field->type);
        $this->assertContains('add', $field->requiredGroups ?? []);
    }
}
