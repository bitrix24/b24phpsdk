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

namespace Bitrix24\SDK\Tests\Unit\Services\CRM\CallList\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Bitrix24\SDK\Services\CRM\CallList\Result\CallListItemResult;
use Bitrix24\SDK\Services\CRM\CallList\Result\CallListParticipantItemResult;
use Bitrix24\SDK\Services\CRM\CallList\Result\CallListStatusItemResult;
use Bitrix24\SDK\Services\CRM\CallList\Result\CallListStatusesResult;
use Bitrix24\SDK\Tests\Unit\Services\CRM\CallList\ResponseFixture;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CallListItemResult::class)]
#[CoversClass(CallListParticipantItemResult::class)]
#[CoversClass(CallListStatusItemResult::class)]
#[CoversClass(CallListStatusesResult::class)]
class CallListResultsTest extends TestCase
{
    use ResponseFixture;

    public function testCallListCastsNumericFieldsAndImmutableDate(): void
    {
        $item = new CallListItemResult([
            'ID' => '42', 'ENTITY_TYPE_ID' => '3', 'ENTITY_TYPE' => 'CONTACT',
            'DATE_CREATE' => '2026-10-28T10:30:00+06:00', 'WEBFORM_ID' => '7', 'CREATED_BY_ID' => '1',
        ]);
        self::assertInstanceOf(AbstractAnnotatedItem::class, $item);
        self::assertSame(42, $item->ID);
        self::assertSame(3, $item->ENTITY_TYPE_ID);
        self::assertSame('CONTACT', $item->ENTITY_TYPE);
        self::assertSame(7, $item->WEBFORM_ID);
        self::assertSame(1, $item->CREATED_BY_ID);
        self::assertInstanceOf(CarbonImmutable::class, $item->DATE_CREATE);
        self::assertSame('2026-10-28T10:30:00+06:00', $item->DATE_CREATE->toIso8601String());
    }

    public function testNullableFieldsHandleMissingAndNullValues(): void
    {
        $item = new CallListItemResult(['ID' => '42']);
        self::assertNull($item->ENTITY_TYPE);
        self::assertNull($item->WEBFORM_ID);
        self::assertNull((new CallListItemResult(['ENTITY_TYPE' => null, 'WEBFORM_ID' => null]))->WEBFORM_ID);
        self::assertSame(0, (new CallListItemResult(['WEBFORM_ID' => '0']))->WEBFORM_ID);
    }

    public function testEmptyStatusesAreReturnedAsEmptyList(): void
    {
        self::assertSame([], (new CallListStatusesResult($this->response([], 'crm.calllist.statuslist')))->getStatuses());
    }
}
