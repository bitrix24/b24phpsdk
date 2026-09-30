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

namespace Bitrix24\SDK\Tests\Unit\Services\Main\Result;

use Bitrix24\SDK\Services\Main\Result\EventLogItemResult;
use Carbon\CarbonImmutable;
use Darsyn\IP\Exception\InvalidIpAddressException;
use Darsyn\IP\Version\Multi;
use ErrorException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(EventLogItemResult::class)]
class EventLogItemResultTest extends TestCase
{
    public function testSelectedOutFieldsAreNullWithoutWarnings(): void
    {
        $eventLogItemResult = new EventLogItemResult(['id' => '42']);
        set_error_handler(static function (int $severity, string $message): never {
            throw new ErrorException($message, 0, $severity);
        });
        try {
            foreach (['timestampX', 'remoteAddr', 'userId', 'guestId', 'severity', 'description'] as $field) {
                self::assertNull($eventLogItemResult->$field, $field);
            }
        } finally {
            restore_error_handler();
        }
    }

    public function testFullResultCastsValuesAndPreservesRawData(): void
    {
        $data = ['id' => '42', 'timestampX' => '2026-09-30T10:20:30+06:00', 'userId' => '7', 'guestId' => '0', 'severity' => 'INFO', 'remoteAddr' => '192.0.2.1'];
        $eventLogItemResult = new EventLogItemResult($data);
        self::assertSame(42, $eventLogItemResult->id);
        self::assertSame(7, $eventLogItemResult->userId);
        self::assertSame(0, $eventLogItemResult->guestId);
        self::assertInstanceOf(CarbonImmutable::class, $eventLogItemResult->timestampX);
        self::assertSame($data['timestampX'], $eventLogItemResult->timestampX->format(DATE_ATOM));
        self::assertInstanceOf(Multi::class, $eventLogItemResult->remoteAddr);
        self::assertSame('192.0.2.1', $eventLogItemResult->remoteAddr->getProtocolAppropriateAddress());
        self::assertSame($data, iterator_to_array($eventLogItemResult));
    }

    #[DataProvider('emptyValues')]
    public function testNullableFields(mixed $value): void
    {
        $eventLogItemResult = new EventLogItemResult(['id' => 42, 'timestampX' => $value, 'remoteAddr' => $value, 'userId' => $value, 'guestId' => $value]);
        self::assertNull($eventLogItemResult->timestampX);
        self::assertNull($eventLogItemResult->remoteAddr);
        self::assertNull($eventLogItemResult->userId);
        self::assertNull($eventLogItemResult->guestId);
    }

    public static function emptyValues(): iterable
    {
        yield [null];
        yield [''];
    }

    public function testIpv6(): void
    {
        self::assertSame('2001:db8::1', (new EventLogItemResult(['remoteAddr' => '2001:db8::1']))->remoteAddr->getProtocolAppropriateAddress());
    }

    public function testInvalidAddressStillThrows(): void
    {
        $this->expectException(InvalidIpAddressException::class);
        (new EventLogItemResult(['remoteAddr' => 'invalid']))->remoteAddr;
    }
}
