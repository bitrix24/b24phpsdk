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

namespace Bitrix24\SDK\Tests\Unit\Services\CRM\V3\Form\Service;

use Bitrix24\SDK\Services\CRM\V3\Form\Service\FormFilter;
use Bitrix24\SDK\Services\CRM\V3\Form\Service\FormItemBuilder;
use Bitrix24\SDK\Services\CRM\V3\Form\Service\FormListPagination;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FormFilter::class)]
#[CoversClass(FormItemBuilder::class)]
#[CoversClass(FormListPagination::class)]
class FormFilterTest extends TestCase
{
    #[Test]
    public function testFilterBuildsTypedConditionsWithNativeBoolean(): void
    {
        $filter = new FormFilter();
        $filter->id()->in([2, 4]);
        $filter->code()->eq('4_feedback');
        $filter->name()->eq('Feedback');
        $filter->active()->eq(false);
        $filter->createdTime()->gt(new CarbonImmutable('2024-01-01T00:00:00+03:00'));
        $filter->updatedTime()->lt(new CarbonImmutable('2026-01-01T00:00:00+03:00'));

        $this->assertSame(
            [
                ['id', 'in', [2, 4]],
                ['code', '=', '4_feedback'],
                ['name', '=', 'Feedback'],
                ['active', '=', false],
                ['createdTime', '>', '2024-01-01T00:00:00+03:00'],
                ['updatedTime', '<', '2026-01-01T00:00:00+03:00'],
            ],
            $filter->toArray()
        );
    }

    #[Test]
    public function testItemBuilderCollectsScalarAndBlockSettings(): void
    {
        $this->assertSame(
            [
                'name' => 'Consultation',
                'active' => true,
                'languageId' => 'en',
                'templateId' => 'contacts',
                'crm' => ['entityScheme' => 1],
                'appearance' => ['title' => 'Leave a request'],
                'responsible' => ['userIds' => [1], 'checkWorkTime' => true],
                'agreements' => ['enabled' => false],
                'submission' => ['success' => ['text' => 'Thanks']],
                'payment' => ['enabled' => false],
                'callback' => ['enabled' => false],
                'booking' => ['enabled' => false],
                'analytics' => ['yandexMetricaId' => '1'],
                'spamProtection' => ['enabled' => true],
                'integrations' => ['whatsAppEnabled' => false],
                'dependencies' => [],
                'presetFields' => [['entityName' => 'LEAD', 'fieldName' => 'SOURCE_ID', 'value' => 'WEB']],
                'fields' => [['name' => 'LEAD_NAME', 'type' => 'string']],
            ],
            (new FormItemBuilder())
                ->name('Consultation')
                ->active(true)
                ->languageId('en')
                ->templateId('contacts')
                ->crm(['entityScheme' => 1])
                ->appearance(['title' => 'Leave a request'])
                ->responsible(['userIds' => [1], 'checkWorkTime' => true])
                ->agreements(['enabled' => false])
                ->submission(['success' => ['text' => 'Thanks']])
                ->payment(['enabled' => false])
                ->callback(['enabled' => false])
                ->booking(['enabled' => false])
                ->analytics(['yandexMetricaId' => '1'])
                ->spamProtection(['enabled' => true])
                ->integrations(['whatsAppEnabled' => false])
                ->dependencies([])
                ->presetFields([['entityName' => 'LEAD', 'fieldName' => 'SOURCE_ID', 'value' => 'WEB']])
                ->fields([['name' => 'LEAD_NAME', 'type' => 'string']])
                ->build()
        );
    }

    #[Test]
    public function testPaginationDropsUnsetValues(): void
    {
        $this->assertSame([], (new FormListPagination())->toArray());
        $this->assertSame(['limit' => 5, 'offset' => 10], (new FormListPagination(limit: 5, offset: 10))->toArray());
    }
}
