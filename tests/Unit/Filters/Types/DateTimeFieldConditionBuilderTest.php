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

namespace Bitrix24\SDK\Tests\Unit\Filters\Types;

use Bitrix24\SDK\Filters\Types\DateTimeFieldConditionBuilder;
use Bitrix24\SDK\Services\Main\Service\EventLogFilter;
use Carbon\CarbonImmutable;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(DateTimeFieldConditionBuilder::class)]
class DateTimeFieldConditionBuilderTest extends TestCase
{
    #[DataProvider('datesAndOperators')]
    public function testSerializesDateWithoutChangingTimezone(string $method, string $operator, DateTimeInterface|string $value): void
    {
        $filter = (new EventLogFilter())->timestampX()->$method($value);
        self::assertSame([['timestampX', $operator, '2026-09-30T10:20:30+06:00']], $filter->toArray());
    }

    public static function datesAndOperators(): iterable
    {
        foreach (['eq' => '=', 'neq' => '!=', 'gt' => '>', 'gte' => '>=', 'lt' => '<', 'lte' => '<='] as $method => $operator) {
            foreach (self::dates() as $name => [$date]) {
                yield $method.' '.$name => [$method, $operator, $date];
            }
        }
    }

    #[DataProvider('dates')]
    public function testBetweenAcceptsMutableImmutableAndStringDates(DateTimeInterface|string $value): void
    {
        $filter = (new EventLogFilter())->timestampX()->between($value, $value);
        self::assertSame([['timestampX', 'between', ['2026-09-30T10:20:30+06:00', '2026-09-30T10:20:30+06:00']]], $filter->toArray());
    }

    public static function dates(): iterable
    {
        $date = '2026-09-30T10:20:30+06:00';
        yield 'CarbonImmutable' => [new CarbonImmutable($date)];
        yield 'DateTimeImmutable' => [new DateTimeImmutable($date)];
        yield 'DateTime' => [new DateTime($date)];
        yield 'string' => [$date];
    }
}
