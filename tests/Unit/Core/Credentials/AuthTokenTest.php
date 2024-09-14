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

use Bitrix24\SDK\Core\Credentials\AuthToken;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Throwable;

#[CoversClass(AuthToken::class)]
class AuthTokenTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    #[DataProvider('constructorDataProvider')]
    #[Test]
    #[TestDox('test init from constructor')]
    public function testConstructorConstrains(string $accessToken, ?string $refreshToken, int $expire, ?Throwable $throwable): void
    {
        if ($throwable instanceof Throwable) {
            $this->expectException($throwable::class);
        }

        $authToken = new AuthToken($accessToken, $refreshToken, $expire);
        $this->assertEquals($accessToken, $authToken->accessToken);
        $this->assertEquals($refreshToken, $authToken->refreshToken);
        $this->assertEquals($expire, $authToken->expires);

        if ($refreshToken === null) {
            $this->assertTrue($authToken->isOneOff());
        }
    }

    public static function constructorDataProvider(): Generator
    {
        yield 'valid' => [
            'access_token',
            'refresh_token',
            1,
            null,
        ];
        yield 'one off token' => [
            'access_token',
            null,
            1,
            null,
        ];
        yield 'empty access token' => [
            '',
            'refresh_token',
            1,
            new InvalidArgumentException(),
        ];
        yield 'empty refresh token' => [
            'access_token',
            '',
            1,
            new InvalidArgumentException(),
        ];
    }
}