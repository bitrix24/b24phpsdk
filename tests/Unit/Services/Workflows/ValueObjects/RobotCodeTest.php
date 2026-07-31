<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\Workflows\ValueObjects;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\Workflows\ValueObjects\RobotCode;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(RobotCode::class)]
class RobotCodeTest extends TestCase
{
    #[Test]
    #[TestDox('valid robot code is accepted and returned unchanged')]
    #[DataProvider('validCodeProvider')]
    public function testValidCode(string $code): void
    {
        $this->assertSame($code, (new RobotCode($code))->getCode());
    }

    #[Test]
    #[TestDox('invalid robot code throws InvalidArgumentException')]
    #[DataProvider('invalidCodeProvider')]
    public function testInvalidCode(string $code): void
    {
        $this->expectException(InvalidArgumentException::class);
        new RobotCode($code);
    }

    public static function validCodeProvider(): Generator
    {
        yield 'letters' => ['robotCode'];
        yield 'digits' => ['robot123'];
        yield 'dot, hyphen, underscore' => ['my.robot-code_1'];
    }

    public static function invalidCodeProvider(): Generator
    {
        yield 'empty' => [''];
        yield 'space' => ['robot code'];
        yield 'slash' => ['robot/code'];
        yield 'unicode' => ['робот'];
    }
}
