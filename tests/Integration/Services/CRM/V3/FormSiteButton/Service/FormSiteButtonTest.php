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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\V3\FormSiteButton\Service;

use Bitrix24\SDK\Core\Contracts\SortOrder;
use Bitrix24\SDK\Services\CRM\V3\CRMV3ServiceBuilder;
use Bitrix24\SDK\Services\CRM\V3\Form\Service\FormPlacementListPagination;
use Bitrix24\SDK\Services\CRM\V3\FormSiteButton\Service\FormSiteButton;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FormSiteButton::class)]
class FormSiteButtonTest extends TestCase
{
    private CRMV3ServiceBuilder $crm;

    #[\Override]
    protected function setUp(): void
    {
        $this->crm = Factory::getServiceBuilder()->getCRMScope()->v3();
    }

    #[Test]
    public function testListReportsPlacementStateOfEachForm(): void
    {
        $forms = $this->crm->form()->list()->getForms();
        self::assertNotEmpty($forms, 'test portal must have at least one CRM form');

        foreach ($forms as $form) {
            foreach ($this->crm->formSiteButton()->list($form->id)->getSiteButtons() as $button) {
                $this->assertSame($form->id, $button->formId);
                $this->assertSame(in_array($form->id, $button->formsId ?? [], true), $button->bound, sprintf('button %d, form %d', $button->id, $form->id));
            }
        }
    }

    #[Test]
    public function testListSupportsIdOrderAndLimitOffsetPagination(): void
    {
        $formId = $this->crm->form()->list()->getForms()[0]->id;
        $all = $this->crm->formSiteButton()->list($formId, SortOrder::Ascending)->getSiteButtons();
        self::assertNotEmpty($all, 'test portal must have a website widget');

        $firstPage = $this->crm->formSiteButton()->list($formId, SortOrder::Ascending, new FormPlacementListPagination(limit: 1, offset: 0))->getSiteButtons();
        $this->assertCount(1, $firstPage);
        $this->assertSame($all[0]->id, $firstPage[0]->id);
    }
}
