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

namespace Bitrix24\SDK\Tests\Unit\Core\Response\DTO;

use Bitrix24\SDK\Core\Response\DTO\Pagination;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Bitrix24\SDK\Core\Response\DTO\Time;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(ResponseData::class)]
class ResponseDataTest extends TestCase
{
    #[Test]
    #[TestDox('getTime() returns a zero-value Time when constructed with initWithZeroValues')]
    public function testGetTimeReturnsZeroTimeWhenAbsent(): void
    {
        $responseData = new ResponseData([], Time::initWithZeroValues(), new Pagination(null, null));
        $this->assertSame(0.0, $responseData->getTime()->start);
        $this->assertSame(0.0, $responseData->getTime()->finish);
        $this->assertSame(0.0, $responseData->getTime()->duration);
    }
}
