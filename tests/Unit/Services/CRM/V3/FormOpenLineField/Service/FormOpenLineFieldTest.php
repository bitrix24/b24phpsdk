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

namespace Bitrix24\SDK\Tests\Unit\Services\CRM\V3\FormOpenLineField\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\LogicException;
use Bitrix24\SDK\Services\CRM\V3\FormOpenLineField\Service\FormOpenLineField;
use Bitrix24\SDK\Tests\Unit\Services\CRM\V3\V3ServiceTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(FormOpenLineField::class)]
class FormOpenLineFieldTest extends TestCase
{
    use V3ServiceTestTrait;

    private const array FORM_ID_FIELD = [
        'name' => 'formId',
        'type' => 'int',
        'title' => 'formId',
        'description' => null,
        'validationRules' => [],
        'requiredGroups' => null,
        'filterable' => true,
        'sortable' => false,
        'editable' => false,
        'editableGroups' => null,
        'multiple' => false,
        'elementType' => null,
    ];

    #[Test]
    public function testGetSendsNameAndSelectAndMapsDescriptor(): void
    {
        $core = $this->v3Core('crm.form.openline.field.get', ['name' => 'formId', 'select' => ['name', 'filterable']], ['item' => self::FORM_ID_FIELD]);
        $field = (new FormOpenLineField($core, new NullLogger()))->get('formId', ['name', 'filterable'])->field();

        $this->assertSame('formId', $field->name);
        $this->assertTrue($field->filterable);
    }

    #[Test]
    public function testListIndexesDescriptorsByName(): void
    {
        $result = (new FormOpenLineField($this->v3Core('crm.form.openline.field.list', [], ['items' => [self::FORM_ID_FIELD]]), new NullLogger()))->list();

        $this->assertSame('formId', $result->getFields()[0]->name);
        $this->assertSame(['formId' => self::FORM_ID_FIELD], $result->getFieldsDescription());
    }

    #[Test]
    public function testFieldsDescriptionRequiresName(): void
    {
        $this->expectException(LogicException::class);
        (new FormOpenLineField($this->v3Core('crm.form.openline.field.list', ['select' => ['type']], ['items' => [['type' => 'int']]]), new NullLogger()))
            ->list(['type'])
            ->getFieldsDescription();
    }

    #[Test]
    public function testGetThrowsOnEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        /** @phpstan-ignore argument.type */
        (new FormOpenLineField($this->createStub(CoreInterface::class), new NullLogger()))->get('');
    }

    #[Test]
    public function testMetadataTargetsRestV3(): void
    {
        $this->assertRestV3Endpoints(
            FormOpenLineField::class,
            ['get' => 'crm.form.openline.field.get', 'list' => 'crm.form.openline.field.list'],
            'https://apidocs.bitrix24.com/api-reference/crm/form/placement/'
        );
    }
}
