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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\V3\FormOpenLine\Service;

use Bitrix24\SDK\Core\Contracts\SortOrder;
use Bitrix24\SDK\Services\CRM\V3\CRMV3ServiceBuilder;
use Bitrix24\SDK\Services\CRM\V3\Form\Service\FormPlacementListPagination;
use Bitrix24\SDK\Services\CRM\V3\FormOpenLine\Service\FormOpenLine;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FormOpenLine::class)]
class FormOpenLineTest extends TestCase
{
    private CRMV3ServiceBuilder $crm;

    #[\Override]
    protected function setUp(): void
    {
        $this->crm = Factory::getServiceBuilder()->getCRMScope()->v3();
    }

    #[Test]
    public function testListReturnsTheSameOpenChannelsForEveryForm(): void
    {
        $forms = $this->crm->form()->list()->getForms();
        self::assertNotEmpty($forms, 'test portal must have at least one CRM form');

        $openLineIds = null;
        foreach ($forms as $form) {
            $openLines = $this->crm->formOpenLine()->list($form->id, SortOrder::Ascending)->getOpenLines();
            foreach ($openLines as $openLine) {
                $this->assertSame($form->id, $openLine->formId);
            }

            $ids = array_map(static fn ($openLine): int => $openLine->id, $openLines);
            $openLineIds ??= $ids;
            $this->assertSame($openLineIds, $ids, sprintf('form %d', $form->id));
        }
    }

    #[Test]
    public function testListSupportsLimitOffsetPagination(): void
    {
        $formId = $this->crm->form()->list()->getForms()[0]->id;
        $all = $this->crm->formOpenLine()->list($formId)->getOpenLines();
        if ($all === []) {
            $this->markTestSkipped('test portal has no Open Channels');
        }

        $this->assertCount(1, $this->crm->formOpenLine()->list($formId, null, new FormPlacementListPagination(limit: 1))->getOpenLines());
    }
}
