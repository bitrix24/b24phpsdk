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

namespace Bitrix24\SDK\Tests\Unit\Services\CRM\Common\Result;

use Bitrix24\SDK\Services\CRM\Common\Result\AbstractCrmItem;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Common\Result\AbstractCrmItem::class)]
class AbstractCrmItemTest extends TestCase
{
    public function testMovedTimeReturnsCarbonImmutable(): void
    {
        $testDateTime = '2024-03-17T10:30:45+00:00';
        $item = new TestCrmItem(['MOVED_TIME' => $testDateTime]);

        $this->assertInstanceOf(CarbonImmutable::class, $item->MOVED_TIME);
        $this->assertEquals($testDateTime, $item->MOVED_TIME->format(DATE_ATOM));
    }

    public function testMovedTimeReturnsNullWhenEmpty(): void
    {
        $item = new TestCrmItem(['MOVED_TIME' => '']);

        $this->assertNull($item->MOVED_TIME);
    }

    public function testMovedTimeCamelCaseReturnsCarbonImmutable(): void
    {
        $testDateTime = '2024-03-17T10:30:45+00:00';
        $item = new TestCrmItem(['movedTime' => $testDateTime]);

        $this->assertInstanceOf(CarbonImmutable::class, $item->movedTime);
        $this->assertEquals($testDateTime, $item->movedTime->format(DATE_ATOM));
    }

    public function testDateCreateReturnsCarbonImmutable(): void
    {
        $testDateTime = '2024-03-17T10:30:45+00:00';
        $item = new TestCrmItem(['DATE_CREATE' => $testDateTime]);

        $this->assertInstanceOf(CarbonImmutable::class, $item->DATE_CREATE);
        $this->assertEquals($testDateTime, $item->DATE_CREATE->format(DATE_ATOM));
    }

    public function testDateModifyReturnsCarbonImmutable(): void
    {
        $testDateTime = '2024-03-17T10:30:45+00:00';
        $item = new TestCrmItem(['DATE_MODIFY' => $testDateTime]);

        $this->assertInstanceOf(CarbonImmutable::class, $item->DATE_MODIFY);
        $this->assertEquals($testDateTime, $item->DATE_MODIFY->format(DATE_ATOM));
    }

    public function testLastActivityTimeReturnsCarbonImmutable(): void
    {
        $testDateTime = '2024-03-17T10:30:45+00:00';
        $item = new TestCrmItem(['LAST_ACTIVITY_TIME' => $testDateTime]);

        $this->assertInstanceOf(CarbonImmutable::class, $item->LAST_ACTIVITY_TIME);
        $this->assertEquals($testDateTime, $item->LAST_ACTIVITY_TIME->format(DATE_ATOM));
    }

    public function testMovedByIdReturnsInt(): void
    {
        $item = new TestCrmItem(['MOVED_BY_ID' => '123']);

        $this->assertIsInt($item->MOVED_BY_ID);
        $this->assertEquals(123, $item->MOVED_BY_ID);
    }

    public function testMovedByIdReturnsNullWhenEmpty(): void
    {
        $item = new TestCrmItem(['MOVED_BY_ID' => '']);

        $this->assertNull($item->MOVED_BY_ID);
    }
}

/**
 * @property-read CarbonImmutable|null $MOVED_TIME
 * @property-read CarbonImmutable|null $movedTime
 * @property-read CarbonImmutable|null $DATE_CREATE
 * @property-read CarbonImmutable|null $DATE_MODIFY
 * @property-read CarbonImmutable|null $LAST_ACTIVITY_TIME
 * @property-read int|null $MOVED_BY_ID
 */
class TestCrmItem extends AbstractCrmItem
{
}
