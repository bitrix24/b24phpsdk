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

namespace Bitrix24\SDK\Tests\Unit\Services\CRM\V3\FormConfigurationField\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\LogicException;
use Bitrix24\SDK\Services\CRM\V3\FormConfigurationField\Service\FormConfigurationField;
use Bitrix24\SDK\Tests\Unit\Services\CRM\V3\V3ServiceTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(FormConfigurationField::class)]
class FormConfigurationFieldTest extends TestCase
{
    use V3ServiceTestTrait;

    private const array ID_FIELD = [
        'name' => 'id',
        'type' => 'string',
        'title' => 'id',
        'description' => null,
        'validationRules' => [],
        'requiredGroups' => null,
        'filterable' => false,
        'sortable' => false,
        'editable' => false,
        'editableGroups' => null,
        'multiple' => false,
        'elementType' => null,
    ];

    #[Test]
    public function testGetSendsNameAndSelectAndMapsDescriptor(): void
    {
        $core = $this->v3Core('crm.form.configuration.field.get', ['name' => 'id', 'select' => ['name', 'type']], ['item' => self::ID_FIELD]);
        $field = (new FormConfigurationField($core, new NullLogger()))->get('id', ['name', 'type'])->field();

        $this->assertSame('id', $field->name);
        $this->assertSame('string', $field->type);
        $this->assertNull($field->editableGroups);
    }

    #[Test]
    public function testListIndexesDescriptorsByName(): void
    {
        $result = (new FormConfigurationField($this->v3Core('crm.form.configuration.field.list', [], ['items' => [self::ID_FIELD]]), new NullLogger()))->list();

        $this->assertSame('id', $result->getFields()[0]->name);
        $this->assertSame(['id' => self::ID_FIELD], $result->getFieldsDescription());
    }

    #[Test]
    public function testFieldsDescriptionRequiresName(): void
    {
        $this->expectException(LogicException::class);
        (new FormConfigurationField($this->v3Core('crm.form.configuration.field.list', ['select' => ['type']], ['items' => [['type' => 'string']]]), new NullLogger()))
            ->list(['type'])
            ->getFieldsDescription();
    }

    #[Test]
    public function testGetThrowsOnEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        /** @phpstan-ignore argument.type */
        (new FormConfigurationField($this->createStub(CoreInterface::class), new NullLogger()))->get('');
    }

    #[Test]
    public function testMetadataTargetsRestV3(): void
    {
        $this->assertRestV3Endpoints(
            FormConfigurationField::class,
            ['get' => 'crm.form.configuration.field.get', 'list' => 'crm.form.configuration.field.list'],
            'https://apidocs.bitrix24.com/api-reference/crm/form/setup/'
        );
    }
}
