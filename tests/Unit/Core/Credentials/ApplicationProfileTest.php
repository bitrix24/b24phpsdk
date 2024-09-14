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

namespace Bitrix24\SDK\Tests\Unit\Core\Credentials;

use Bitrix24\SDK\Core\Credentials\ApplicationProfile;
use Bitrix24\SDK\Core\Credentials\Credentials;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(ApplicationProfile::class)]
class ApplicationProfileTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    #[DataProvider('arrayDataProvider')]
    #[Test]
    #[TestDox('test init from array')]
    public function testFromArray(array $arr, ?string $expectedException): void
    {
        if ($expectedException !== null) {
            $this->expectException($expectedException);
        }

        $applicationProfile = ApplicationProfile::initFromArray($arr);

        $this->assertEquals($applicationProfile->clientId, $arr['BITRIX24_PHP_SDK_APPLICATION_CLIENT_ID']);
        $this->assertEquals($applicationProfile->clientSecret, $arr['BITRIX24_PHP_SDK_APPLICATION_CLIENT_SECRET']);
        $this->assertTrue(Scope::initFromString($arr['BITRIX24_PHP_SDK_APPLICATION_SCOPE'])->equal($applicationProfile->scope));
    }

    #[DataProvider('withEmptyArgs')]
    #[Test]
    #[TestDox('test init from constructor')]
    public function testConstructor(array $arr, ?string $expectedException): void
    {
        if ($expectedException !== null) {
            $this->expectException($expectedException);
        }

        $applicationProfile = new ApplicationProfile(
            (string)$arr['BITRIX24_PHP_SDK_APPLICATION_CLIENT_ID'],
            (string)$arr['BITRIX24_PHP_SDK_APPLICATION_CLIENT_SECRET'],
            Scope::initFromString((string)$arr['BITRIX24_PHP_SDK_APPLICATION_SCOPE']),
        );
        $this->assertEquals($applicationProfile->clientId, $arr['BITRIX24_PHP_SDK_APPLICATION_CLIENT_ID']);
        $this->assertEquals($applicationProfile->clientSecret, $arr['BITRIX24_PHP_SDK_APPLICATION_CLIENT_SECRET']);
        $this->assertTrue(Scope::initFromString((string)$arr['BITRIX24_PHP_SDK_APPLICATION_SCOPE'])->equal($applicationProfile->scope));

    }


    public static function arrayDataProvider(): Generator
    {
        yield 'valid' => [
            [
                'BITRIX24_PHP_SDK_APPLICATION_CLIENT_ID' => '1',
                'BITRIX24_PHP_SDK_APPLICATION_CLIENT_SECRET' => '2',
                'BITRIX24_PHP_SDK_APPLICATION_SCOPE' => 'user',
            ],
            null,
        ];
        yield 'without client id' => [
            [
                '' => '1',
                'BITRIX24_PHP_SDK_APPLICATION_CLIENT_SECRET' => '2',
                'BITRIX24_PHP_SDK_APPLICATION_SCOPE' => 'user',
            ],
            InvalidArgumentException::class,
        ];
        yield 'without client secret' => [
            [
                'BITRIX24_PHP_SDK_APPLICATION_CLIENT_ID' => '1',
                '' => '2',
                'BITRIX24_PHP_SDK_APPLICATION_SCOPE' => 'user',
            ],
            InvalidArgumentException::class,
        ];
        yield 'without client application scope' => [
            [
                'BITRIX24_PHP_SDK_APPLICATION_CLIENT_ID' => '1',
                'BITRIX24_PHP_SDK_APPLICATION_CLIENT_SECRET' => '2',
                '' => 'user',
            ],
            InvalidArgumentException::class,
        ];
        yield 'with empty scope' => [
            [
                'BITRIX24_PHP_SDK_APPLICATION_CLIENT_ID' => '1',
                'BITRIX24_PHP_SDK_APPLICATION_CLIENT_SECRET' => '2',
                'BITRIX24_PHP_SDK_APPLICATION_SCOPE' => '',
            ],
            null
        ];
        yield 'with empty APPLICATION_CLIENT_ID' => [
            [
                'BITRIX24_PHP_SDK_APPLICATION_CLIENT_ID' => '   ',
                'BITRIX24_PHP_SDK_APPLICATION_CLIENT_SECRET' => '2',
                'BITRIX24_PHP_SDK_APPLICATION_SCOPE' => '',
            ],
            InvalidArgumentException::class,
        ];
        yield 'with empty APPLICATION_CLIENT_SECRET' => [
            [
                'BITRIX24_PHP_SDK_APPLICATION_CLIENT_ID' => '1',
                'BITRIX24_PHP_SDK_APPLICATION_CLIENT_SECRET' => '   ',
                'BITRIX24_PHP_SDK_APPLICATION_SCOPE' => '',
            ],
            InvalidArgumentException::class,
        ];
    }

    public static function withEmptyArgs(): Generator
    {

        yield 'with empty APPLICATION_CLIENT_ID' => [
            [
                'BITRIX24_PHP_SDK_APPLICATION_CLIENT_ID' => '   ',
                'BITRIX24_PHP_SDK_APPLICATION_CLIENT_SECRET' => '2',
                'BITRIX24_PHP_SDK_APPLICATION_SCOPE' => '',
            ],
            InvalidArgumentException::class,
        ];
        yield 'with empty APPLICATION_CLIENT_SECRET' => [
            [
                'BITRIX24_PHP_SDK_APPLICATION_CLIENT_ID' => '1',
                'BITRIX24_PHP_SDK_APPLICATION_CLIENT_SECRET' => '   ',
                'BITRIX24_PHP_SDK_APPLICATION_SCOPE' => '',
            ],
            InvalidArgumentException::class,
        ];
    }
}
