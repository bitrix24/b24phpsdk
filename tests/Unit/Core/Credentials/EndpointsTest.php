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

use Bitrix24\SDK\Core\Credentials\DefaultOAuthServerUrl;
use Bitrix24\SDK\Core\Credentials\Endpoints;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Throwable;

#[CoversClass(Endpoints::class)]
class EndpointsTest extends TestCase
{
    private const ENV_VAR_NAME = 'BITRIX24_PHP_SDK_DEFAULT_AUTH_SERVER_URL';

    protected function setUp(): void
    {
        parent::setUp();
        // Set a default value for environment variable to ensure tests work consistently
        $_ENV[self::ENV_VAR_NAME] = 'https://oauth.bitrix.info/';
    }

    protected function tearDown(): void
    {
        // Clean up environment variable after each test
        if (isset($_ENV[self::ENV_VAR_NAME])) {
            unset($_ENV[self::ENV_VAR_NAME]);
        }
        parent::tearDown();
    }

    #[DataProvider('constructorDataProvider')]
    #[Test]
    #[TestDox('test init from constructor')]
    public function testConstructorConstrains(
        string $clientUrl,
        ?string $authServerUrl,
        ?Throwable $throwable
    ): void {
        if ($throwable instanceof Throwable) {
            $this->expectException($throwable::class);
        }

        $endpoints = new Endpoints($clientUrl, $authServerUrl);
        $this->assertEquals($clientUrl, $endpoints->getClientUrl());

        if ($authServerUrl === null) {
            $this->assertEquals(DefaultOAuthServerUrl::default(), $endpoints->getAuthServerUrl());
        } else {
            $this->assertEquals($authServerUrl, $endpoints->getAuthServerUrl());
        }
    }

    public static function constructorDataProvider(): Generator
    {
        yield 'valid with both URLs' => [
            'https://example.bitrix24.com',
            'https://oauth.bitrix.info/',
            null,
        ];
        yield 'invalid client URL - empty string' => [
            '',
            'https://oauth.bitrix.info/',
            new InvalidArgumentException(),
        ];
        yield 'invalid client URL - not a URL' => [
            'not-a-url',
            'https://oauth.bitrix.info/',
            new InvalidArgumentException(),
        ];
        yield 'invalid client URL - missing protocol' => [
            'example.bitrix24.com',
            'https://oauth.bitrix.info/',
            new InvalidArgumentException(),
        ];
    }

    #[Test]
    #[TestDox('tests getClientUrl() returns correct client URL')]
    public function testGetClientUrl(): void
    {
        $clientUrl = 'https://test.bitrix24.com';
        $authServerUrl = 'https://oauth.bitrix.info/';

        $endpoints = new Endpoints($clientUrl, $authServerUrl);

        $this->assertEquals($clientUrl, $endpoints->getClientUrl());
    }

    #[Test]
    #[TestDox('tests getAuthServerUrl() returns correct auth server URL')]
    public function testGetAuthServerUrl(): void
    {
        $clientUrl = 'https://test.bitrix24.com';
        $authServerUrl = 'https://oauth.bitrix.info/';

        $endpoints = new Endpoints($clientUrl, $authServerUrl);

        $this->assertEquals($authServerUrl, $endpoints->getAuthServerUrl());
    }

    #[Test]
    #[TestDox('tests constructor with explicit auth server URL')]
    public function testConstructorWithExplicitAuthServerUrl(): void
    {
        $clientUrl = 'https://test.bitrix24.com';
        $authServerUrl = 'https://custom.oauth.server/';

        $endpoints = new Endpoints($clientUrl, $authServerUrl);

        $this->assertEquals($clientUrl, $endpoints->getClientUrl());
        $this->assertEquals($authServerUrl, $endpoints->getAuthServerUrl());
    }

    #[DataProvider('initFromArrayDataProvider')]
    #[Test]
    #[TestDox('test initFromArray() method')]
    public function testInitFromArray(array $auth, ?Throwable $throwable): void
    {
        if ($throwable instanceof Throwable) {
            $this->expectException($throwable::class);
            $this->expectExceptionMessage($throwable->getMessage());
        }

        $endpoints = Endpoints::initFromArray($auth);

        $this->assertEquals($auth['client_endpoint'], $endpoints->getClientUrl());
        $this->assertEquals($auth['server_endpoint'], $endpoints->getAuthServerUrl());
    }

    public static function initFromArrayDataProvider(): Generator
    {
        yield 'valid array' => [
            [
                'client_endpoint' => 'https://example.bitrix24.com',
                'server_endpoint' => 'https://oauth.bitrix.info/',
            ],
            null,
        ];
        yield 'missing client_endpoint' => [
            [
                'server_endpoint' => 'https://oauth.bitrix.info/',
            ],
            new InvalidArgumentException('field client_endpoint not found in array'),
        ];
        yield 'missing server_endpoint' => [
            [
                'client_endpoint' => 'https://example.bitrix24.com',
            ],
            new InvalidArgumentException('field server_endpoint not found in array'),
        ];
        yield 'empty array' => [
            [],
            new InvalidArgumentException('field client_endpoint not found in array'),
        ];
    }

    #[Test]
    #[TestDox('tests initFromArray() with invalid client URL')]
    public function testInitFromArrayWithInvalidClientUrl(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Endpoints::initFromArray([
            'client_endpoint' => 'invalid-url',
            'server_endpoint' => 'https://oauth.bitrix.info/',
        ]);
    }

    #[Test]
    #[TestDox('tests initFromArray() casts server_endpoint to string')]
    public function testInitFromArrayCastsServerEndpointToString(): void
    {
        $endpoints = Endpoints::initFromArray([
            'client_endpoint' => 'https://example.bitrix24.com',
            'server_endpoint' => 'https://oauth.bitrix.info/',
        ]);

        $this->assertEquals('https://example.bitrix24.com', $endpoints->getClientUrl());
        $this->assertEquals('https://oauth.bitrix.info/', $endpoints->getAuthServerUrl());
    }

    #[Test]
    #[TestDox('tests that client URL is validated')]
    public function testClientUrlValidation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('clientUrl endpoint URL «invalid» is invalid');

        new Endpoints('invalid', 'https://oauth.bitrix.info/');
    }

    #[Test]
    #[TestDox('tests various valid URL formats for client URL')]
    public function testValidClientUrlFormats(): void
    {
        $validUrls = [
            'https://example.bitrix24.com',
            'http://example.bitrix24.com',
            'https://subdomain.example.bitrix24.com',
            'https://example.bitrix24.com/',
            'https://example.bitrix24.com:8080',
            'https://example.bitrix24.com/path',
        ];

        foreach ($validUrls as $url) {
            $endpoints = new Endpoints($url, 'https://oauth.bitrix.info/');
            $this->assertEquals($url, $endpoints->getClientUrl());
        }
    }

    #[Test]
    #[TestDox('tests various valid URL formats for auth server URL')]
    public function testValidAuthServerUrlFormats(): void
    {
        $validUrls = [
            'https://oauth.bitrix.info/',
            'http://oauth.bitrix.info/',
            'https://custom.oauth.server/',
            'https://oauth.bitrix.info:8080/',
        ];

        foreach ($validUrls as $url) {
            $endpoints = new Endpoints('https://example.bitrix24.com', $url);
            $this->assertEquals($url, $endpoints->getAuthServerUrl());
        }
    }
}