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

namespace Bitrix24\SDK\Tests\Unit\Services\CRM\V3\FormEntityField\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\CRM\V3\FormEntityField\Result\EntityFieldOptionItemResult;
use Bitrix24\SDK\Services\CRM\V3\FormEntityField\Service\FormEntityField;
use Bitrix24\SDK\Tests\Unit\Services\CRM\V3\V3ServiceTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(FormEntityField::class)]
class FormEntityFieldTest extends TestCase
{
    use V3ServiceTestTrait;

    #[Test]
    public function testListFiltersByEntityNamesAndMapsOptions(): void
    {
        $core = $this->v3Core('crm.form.entityfield.list', ['filter' => [['entityName', 'in', ['LEAD', 'CONTACT']]]], ['items' => [
            ['name' => 'LEAD_HONORIFIC', 'label' => 'Salutation', 'type' => 'list', 'required' => false, 'multiple' => false, 'entityName' => 'LEAD', 'entityLabel' => 'Lead', 'items' => [['id' => 'HNR_EN_1', 'label' => 'Mr.']]],
        ]]);

        $fields = (new FormEntityField($core, new NullLogger()))->list(['LEAD', 'CONTACT'])->getEntityFields();

        $this->assertSame('LEAD_HONORIFIC', $fields[0]->name);
        $this->assertSame('list', $fields[0]->type);
        $this->assertFalse($fields[0]->required);
        $this->assertSame('LEAD', $fields[0]->entityName);
        $this->assertInstanceOf(EntityFieldOptionItemResult::class, $fields[0]->items[0]);
        $this->assertSame('HNR_EN_1', $fields[0]->items[0]->id);
    }

    #[Test]
    public function testListRequiresEntityNames(): void
    {
        // the API requires a filter by entityName
        $this->expectException(InvalidArgumentException::class);
        /** @phpstan-ignore argument.type */
        (new FormEntityField($this->createStub(CoreInterface::class), new NullLogger()))->list([]);
    }

    #[Test]
    public function testMetadataTargetsRestV3(): void
    {
        $this->assertRestV3Endpoints(FormEntityField::class, ['list' => 'crm.form.entityfield.list'], 'https://apidocs.bitrix24.com/api-reference/crm/form/setup/');
    }
}
