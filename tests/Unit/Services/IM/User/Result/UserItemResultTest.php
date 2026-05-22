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

namespace Bitrix24\SDK\Tests\Unit\Services\IM\User\Result;

use Bitrix24\SDK\Services\IM\User\Result\UserItemResult;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserItemResult::class)]
final class UserItemResultTest extends TestCase
{
    #[Test]
    public function testLastActivityDateIsReturnedAsCarbonImmutable(): void
    {
        $userItemResult = new UserItemResult([
            'last_activity_date' => '2026-04-29T11:15:24+03:00',
        ]);

        self::assertInstanceOf(CarbonImmutable::class, $userItemResult->last_activity_date);
        self::assertSame('2026-04-29T11:15:24+03:00', $userItemResult->last_activity_date->toAtomString());
    }
}
