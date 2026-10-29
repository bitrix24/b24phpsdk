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

namespace Bitrix24\SDK\Tests\Unit\Services\CRM\V3\FormSiteButton\Service;

use Bitrix24\SDK\Core\Contracts\SortOrder;
use Bitrix24\SDK\Services\CRM\V3\Form\Service\FormPlacementListPagination;
use Bitrix24\SDK\Services\CRM\V3\FormSiteButton\Service\FormSiteButton;
use Bitrix24\SDK\Tests\Unit\Services\CRM\V3\V3ServiceTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(FormSiteButton::class)]
#[CoversClass(FormPlacementListPagination::class)]
class FormSiteButtonTest extends TestCase
{
    use V3ServiceTestTrait;

    private const array SITE_BUTTON = [
        'formId' => 4,
        'id' => 2,
        'name' => 'CRM form',
        'system' => true,
        'bound' => true,
        'formsId' => [4],
        'previewUrl' => 'https://example.test/pub/form/4/?preview=button&preview_id=2',
    ];

    #[Test]
    public function testListSendsRequiredFormFilterAndMapsButtons(): void
    {
        $buttons = (new FormSiteButton($this->v3Core('crm.form.sitebutton.list', ['filter' => [['formId', '=', 4]]], ['items' => [self::SITE_BUTTON]]), new NullLogger()))
            ->list(4)
            ->getSiteButtons();

        $this->assertSame(4, $buttons[0]->formId);
        $this->assertSame(2, $buttons[0]->id);
        $this->assertTrue($buttons[0]->system);
        $this->assertTrue($buttons[0]->bound);
        $this->assertSame([4], $buttons[0]->formsId);
        $this->assertStringContainsString('preview=button', (string)$buttons[0]->previewUrl);
    }

    #[Test]
    public function testListSendsIdOrderAndLimitOffsetPagination(): void
    {
        (new FormSiteButton(
            $this->v3Core(
                'crm.form.sitebutton.list',
                ['filter' => [['formId', '=', 4]], 'order' => ['id' => 'DESC'], 'pagination' => ['limit' => 10, 'offset' => 20]],
                ['items' => []]
            ),
            new NullLogger()
        ))->list(4, SortOrder::Descending, new FormPlacementListPagination(limit: 10, offset: 20));
    }

    #[Test]
    public function testPaginationDropsUnsetValues(): void
    {
        $this->assertSame([], (new FormPlacementListPagination())->toArray());
        $this->assertSame(['limit' => 5], (new FormPlacementListPagination(limit: 5))->toArray());
    }

    #[Test]
    public function testMetadataTargetsRestV3(): void
    {
        $this->assertRestV3Endpoints(FormSiteButton::class, ['list' => 'crm.form.sitebutton.list'], 'https://apidocs.bitrix24.com/api-reference/crm/form/placement/');
    }
}
