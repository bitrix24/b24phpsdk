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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\V3\MeasureField\Service;

use Bitrix24\SDK\Services\Catalog\V3\MeasureField\Service\MeasureField;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(MeasureField::class)]
class MeasureFieldTest extends TestCase
{
    private MeasureField $measureField;

    #[\Override]
    protected function setUp(): void
    {
        $this->measureField = Factory::getServiceBuilder()->getCatalogScope()->v3()->measureField();
    }

    #[Test]
    public function testListReturnsAllMeasureFields(): void
    {
        $names = array_map(static fn ($field): string => $field->name, $this->measureField->list()->getFields());

        foreach (['id', 'code', 'measureTitle', 'symbol', 'symbolIntl', 'symbolLetterIntl', 'isDefault'] as $name) {
            $this->assertContains($name, $names);
        }
    }

    #[Test]
    public function testGetReturnsFieldDescriptor(): void
    {
        $field = $this->measureField->get('code')->field();

        $this->assertSame('code', $field->name);
        $this->assertSame('int', $field->type);
        $this->assertContains('add', $field->requiredGroups ?? []);
    }
}
