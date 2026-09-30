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

namespace Bitrix24\SDK\Tests\Unit\Services\Main\UserHistory\Service;

use Bitrix24\SDK\Core\Contracts\SortOrder;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\Main\UserHistory\Service\UserHistoryTailCursor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserHistoryTailCursor::class)]
class UserHistoryTailCursorTest extends TestCase
{
    #[DataProvider('positions')]
    public function testSerializesIdPositionAsInteger(int|string $value, int $expected): void
    {
        self::assertSame(
            ['field' => 'id', 'value' => $expected, 'order' => 'ASC'],
            (new UserHistoryTailCursor($value))->toArray()
        );
    }

    public static function positions(): array
    {
        return [[0, 0], [42, 42], ['42', 42], ['00042', 42], ['0', 0], [(string)PHP_INT_MAX, PHP_INT_MAX]];
    }

    public function testDescendingCursor(): void
    {
        self::assertSame(
            ['field' => 'id', 'value' => 42, 'order' => 'DESC'],
            (new UserHistoryTailCursor('42', order: SortOrder::Descending))->toArray()
        );
    }

    #[DataProvider('invalidPositions')]
    public function testRejectsInvalidPositions(int|string $value, string $field): void
    {
        $this->expectException(InvalidArgumentException::class);
        new UserHistoryTailCursor($value, $field);
    }

    public static function invalidPositions(): array
    {
        return [[PHP_INT_MAX . '0', 'id'], [-1, 'id'], ['-1', 'id'], ['', 'id'], [' ', 'id'], [0, ' ']];
    }
}
