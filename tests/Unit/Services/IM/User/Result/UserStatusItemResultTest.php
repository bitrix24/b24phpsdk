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

use Bitrix24\SDK\Services\IM\User\Result\UserStatusItemResult;
use Bitrix24\SDK\Services\IM\User\UserStatusType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserStatusItemResult::class)]
class UserStatusItemResultTest extends TestCase
{
    #[Test]
    public function testStatusIsReturnedAsEnum(): void
    {
        $userStatusItemResult = new UserStatusItemResult(['STATUS' => 'online']);

        $this->assertSame(UserStatusType::Online, $userStatusItemResult->STATUS);
    }

    #[Test]
    public function testFalseStatusIsReturnedAsNull(): void
    {
        $userStatusItemResult = new UserStatusItemResult(['STATUS' => false]);

        $this->assertNull($userStatusItemResult->STATUS);
    }
}
