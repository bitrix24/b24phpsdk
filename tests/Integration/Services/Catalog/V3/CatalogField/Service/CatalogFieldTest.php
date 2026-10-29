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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\V3\CatalogField\Service;

use Bitrix24\SDK\Services\Catalog\V3\CatalogField\Service\CatalogField;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CatalogField::class)]
class CatalogFieldTest extends TestCase
{
    private CatalogField $catalogField;

    #[\Override]
    protected function setUp(): void
    {
        $this->catalogField = Factory::getServiceBuilder()->getCatalogScope()->v3()->catalogField();
    }

    #[Test]
    public function testListReturnsAllCatalogFields(): void
    {
        $names = array_map(static fn ($field): string => $field->name, $this->catalogField->list()->getFields());

        foreach (['id', 'iblockId', 'yandexExport', 'subscription', 'vatId', 'productIblockId', 'skuPropertyId', 'iblockTypeId', 'lid', 'name'] as $name) {
            $this->assertContains($name, $names);
        }
    }

    #[Test]
    public function testGetReturnsFieldDescriptor(): void
    {
        $field = $this->catalogField->get('iblockId')->field();

        $this->assertSame('iblockId', $field->name);
        $this->assertSame('int', $field->type);
        $this->assertContains('add', $field->requiredGroups ?? []);
    }
}
