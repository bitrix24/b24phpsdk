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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\V3\FormField\Service;

use Bitrix24\SDK\Services\CRM\V3\FormField\Service\FormField;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FormField::class)]
class FormFieldTest extends TestCase
{
    private FormField $formField;

    #[\Override]
    protected function setUp(): void
    {
        $this->formField = Factory::getServiceBuilder()->getCRMScope()->v3()->formField();
    }

    #[Test]
    public function testListReturnsFormFields(): void
    {
        $names = array_map(static fn ($field): string => $field->name, $this->formField->list()->getFields());

        foreach (['id', 'name', 'active', 'system', 'updatedTime', 'appearance', 'crm', 'fields'] as $name) {
            $this->assertContains($name, $names);
        }
    }

    #[Test]
    public function testGetReturnsFieldDescriptor(): void
    {
        $field = $this->formField->get('updatedTime')->field();

        $this->assertSame('updatedTime', $field->name);
        $this->assertSame('date-time', $field->type);
        $this->assertTrue($field->filterable);
    }
}
