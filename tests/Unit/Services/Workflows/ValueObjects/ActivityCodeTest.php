<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\Workflows\ValueObjects;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\Workflows\ValueObjects\ActivityCode;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(ActivityCode::class)]
class ActivityCodeTest extends TestCase
{
    #[Test]
    #[TestDox('valid activity code is accepted and returned unchanged')]
    #[DataProvider('validCodeProvider')]
    public function testValidCode(string $code): void
    {
        $this->assertSame($code, (new ActivityCode($code))->getCode());
    }

    #[Test]
    #[TestDox('invalid activity code throws InvalidArgumentException')]
    #[DataProvider('invalidCodeProvider')]
    public function testInvalidCode(string $code): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ActivityCode($code);
    }

    public static function validCodeProvider(): Generator
    {
        yield 'letters' => ['activityCode'];
        yield 'digits' => ['activity123'];
        yield 'dot, hyphen, underscore' => ['my.activity-code_1'];
    }

    public static function invalidCodeProvider(): Generator
    {
        yield 'empty' => [''];
        yield 'space' => ['activity code'];
        yield 'slash' => ['activity/code'];
        yield 'unicode' => ['действие'];
    }
}
