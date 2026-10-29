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

namespace Bitrix24\SDK\Tests\Unit\Services\CRM\V3\FormOpenLine\Service;

use Bitrix24\SDK\Core\Contracts\SortOrder;
use Bitrix24\SDK\Services\CRM\V3\Form\Service\FormPlacementListPagination;
use Bitrix24\SDK\Services\CRM\V3\FormOpenLine\Service\FormOpenLine;
use Bitrix24\SDK\Tests\Unit\Services\CRM\V3\V3ServiceTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(FormOpenLine::class)]
class FormOpenLineTest extends TestCase
{
    use V3ServiceTestTrait;

    #[Test]
    public function testListSendsRequiredFormFilterAndMapsOpenLines(): void
    {
        $openLines = (new FormOpenLine(
            $this->v3Core('crm.form.openline.list', ['filter' => [['formId', '=', 6]], 'order' => ['id' => 'ASC'], 'pagination' => ['offset' => 0]], ['items' => [[
                'formId' => 6, 'id' => 2, 'name' => 'Open Channel', 'bound' => true,
                'welcomeFormEnabled' => true, 'welcomeFormId' => 6, 'welcomeFormDelayed' => true, 'previewUrl' => null,
            ]]]),
            new NullLogger()
        ))->list(6, SortOrder::Ascending, new FormPlacementListPagination(offset: 0))->getOpenLines();

        $this->assertSame(6, $openLines[0]->formId);
        $this->assertSame(2, $openLines[0]->id);
        $this->assertTrue($openLines[0]->bound);
        $this->assertTrue($openLines[0]->welcomeFormEnabled);
        $this->assertSame(6, $openLines[0]->welcomeFormId);
        $this->assertTrue($openLines[0]->welcomeFormDelayed);
        $this->assertNull($openLines[0]->previewUrl);
    }

    #[Test]
    public function testMetadataTargetsRestV3(): void
    {
        $this->assertRestV3Endpoints(FormOpenLine::class, ['list' => 'crm.form.openline.list'], 'https://apidocs.bitrix24.com/api-reference/crm/form/placement/');
    }
}
