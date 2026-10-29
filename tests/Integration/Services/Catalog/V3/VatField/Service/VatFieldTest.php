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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\V3\VatField\Service;

use Bitrix24\SDK\Services\Catalog\V3\VatField\Service\VatField;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(VatField::class)]
class VatFieldTest extends TestCase
{
    private VatField $vatField;

    #[\Override]
    protected function setUp(): void
    {
        $this->vatField = Factory::getServiceBuilder()->getCatalogScope()->v3()->vatField();
    }

    #[Test]
    public function testListReturnsAllVatFields(): void
    {
        $names = array_map(static fn ($field): string => $field->name, $this->vatField->list()->getFields());

        foreach (['id', 'timestampX', 'active', 'sort', 'name', 'rate'] as $name) {
            $this->assertContains($name, $names);
        }
    }

    #[Test]
    public function testGetReturnsFieldDescriptor(): void
    {
        $field = $this->vatField->get('rate')->field();

        $this->assertSame('rate', $field->name);
        $this->assertSame('float', $field->type);
        $this->assertContains('add', $field->requiredGroups ?? []);
    }
}
