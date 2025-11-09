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

    #[Test]
    #[TestDox('tests constructor adds https:// protocol when missing')]
    public function testConstructorAddsHttpsProtocolWhenMissing(): void
    {
        $clientUrlWithoutProtocol = 'example.bitrix24.com';
        $authServerUrl = 'https://oauth.bitrix.info/';

        $endpoints = new Endpoints($clientUrlWithoutProtocol, $authServerUrl);

        $this->assertEquals('https://example.bitrix24.com', $endpoints->getClientUrl());
        $this->assertEquals($authServerUrl, $endpoints->getAuthServerUrl());
    }

    #[Test]
    #[TestDox('tests constructor preserves existing protocol')]
    public function testConstructorPreservesExistingProtocol(): void
    {
        $clientUrlWithHttp = 'http://example.bitrix24.com';
        $authServerUrl = 'https://oauth.bitrix.info/';

        $endpoints = new Endpoints($clientUrlWithHttp, $authServerUrl);

        $this->assertEquals('http://example.bitrix24.com', $endpoints->getClientUrl());
        $this->assertEquals($authServerUrl, $endpoints->getAuthServerUrl());
    }

    #[Test]
    #[TestDox('tests constructor with domain without protocol and trailing slash')]
    public function testConstructorWithDomainWithoutProtocolAndTrailingSlash(): void
    {
        $clientUrl = 'test.bitrix24.ru';
        $authServerUrl = 'https://oauth.bitrix.info/';

        $endpoints = new Endpoints($clientUrl, $authServerUrl);

        $this->assertEquals('https://test.bitrix24.ru', $endpoints->getClientUrl());
    }

    #[Test]
    #[TestDox('tests constructor with subdomain without protocol')]
    public function testConstructorWithSubdomainWithoutProtocol(): void
    {
        $clientUrl = 'subdomain.company.bitrix24.com';
        $authServerUrl = 'https://oauth.bitrix.info/';

        $endpoints = new Endpoints($clientUrl, $authServerUrl);

        $this->assertEquals('https://subdomain.company.bitrix24.com', $endpoints->getClientUrl());
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
    #[TestDox('tests initFromArray() adds https protocol to client URL without protocol')]
    public function testInitFromArrayAddsHttpsProtocol(): void
    {
        $endpoints = Endpoints::initFromArray([
            'client_endpoint' => 'example.bitrix24.com',
            'server_endpoint' => 'https://oauth.bitrix.info/',
        ]);

        $this->assertEquals('https://example.bitrix24.com', $endpoints->getClientUrl());
        $this->assertEquals('https://oauth.bitrix.info/', $endpoints->getAuthServerUrl());
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
    #[TestDox('tests that empty client URL throws exception')]
    public function testEmptyClientUrlThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Endpoints('', 'https://oauth.bitrix.info/');
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

        foreach ($validUrls as $validUrl) {
            $endpoints = new Endpoints($validUrl, 'https://oauth.bitrix.info/');
            $this->assertEquals($validUrl, $endpoints->getClientUrl());
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

        foreach ($validUrls as $validUrl) {
            $endpoints = new Endpoints('https://example.bitrix24.com', $validUrl);
            $this->assertEquals($validUrl, $endpoints->getAuthServerUrl());
        }
    }

    #[Test]
    #[TestDox('tests changeClientUrl() returns new instance with updated client URL')]
    public function testChangeClientUrl(): void
    {
        $originalClientUrl = 'https://original.bitrix24.com';
        $newClientUrl = 'https://new.bitrix24.com';
        $authServerUrl = 'https://oauth.bitrix.info/';

        $originalEndpoints = new Endpoints($originalClientUrl, $authServerUrl);
        $newEndpoints = $originalEndpoints->changeClientUrl($newClientUrl);

        // Verify original instance is unchanged
        $this->assertEquals($originalClientUrl, $originalEndpoints->getClientUrl());
        $this->assertEquals($authServerUrl, $originalEndpoints->getAuthServerUrl());

        // Verify new instance has updated client URL
        $this->assertEquals($newClientUrl, $newEndpoints->getClientUrl());
        $this->assertEquals($authServerUrl, $newEndpoints->getAuthServerUrl());
    }

    #[Test]
    #[TestDox('tests changeClientUrl() returns different instance')]
    public function testChangeClientUrlReturnsDifferentInstance(): void
    {
        $originalClientUrl = 'https://original.bitrix24.com';
        $newClientUrl = 'https://new.bitrix24.com';
        $authServerUrl = 'https://oauth.bitrix.info/';

        $originalEndpoints = new Endpoints($originalClientUrl, $authServerUrl);
        $newEndpoints = $originalEndpoints->changeClientUrl($newClientUrl);

        // Verify they are different instances
        $this->assertNotSame($originalEndpoints, $newEndpoints);
    }

    #[Test]
    #[TestDox('tests changeClientUrl() preserves auth server URL')]
    public function testChangeClientUrlPreservesAuthServerUrl(): void
    {
        $originalClientUrl = 'https://original.bitrix24.com';
        $newClientUrl = 'https://new.bitrix24.com';
        $authServerUrl = 'https://custom.oauth.server/';

        $originalEndpoints = new Endpoints($originalClientUrl, $authServerUrl);
        $newEndpoints = $originalEndpoints->changeClientUrl($newClientUrl);

        $this->assertEquals($authServerUrl, $newEndpoints->getAuthServerUrl());
    }

    #[Test]
    #[TestDox('tests changeClientUrl() throws exception for empty URL')]
    public function testChangeClientUrlThrowsExceptionForEmptyUrl(): void
    {
        $originalClientUrl = 'https://original.bitrix24.com';
        $authServerUrl = 'https://oauth.bitrix.info/';

        $originalEndpoints = new Endpoints($originalClientUrl, $authServerUrl);

        $this->expectException(InvalidArgumentException::class);

        $originalEndpoints->changeClientUrl('');
    }

    #[DataProvider('changeClientUrlDataProvider')]
    #[Test]
    #[TestDox('test changeClientUrl() with various inputs')]
    public function testChangeClientUrlWithVariousInputs(
        string $originalClientUrl,
        string $newClientUrl,
        string $authServerUrl,
        ?Throwable $throwable
    ): void {
        if ($throwable instanceof Throwable) {
            $this->expectException($throwable::class);
        }

        $originalEndpoints = new Endpoints($originalClientUrl, $authServerUrl);
        $newEndpoints = $originalEndpoints->changeClientUrl($newClientUrl);

        // Check if protocol was added
        $expectedNewUrl = $newClientUrl;
        $parseResult = parse_url($newClientUrl);
        if (!array_key_exists('scheme', $parseResult)) {
            $expectedNewUrl = 'https://' . $newClientUrl;
        }

        $this->assertEquals($expectedNewUrl, $newEndpoints->getClientUrl());
        $this->assertEquals($authServerUrl, $newEndpoints->getAuthServerUrl());
        $this->assertEquals($originalClientUrl, $originalEndpoints->getClientUrl());
    }

    public static function changeClientUrlDataProvider(): Generator
    {
        yield 'change to different domain' => [
            'https://old.bitrix24.com',
            'https://new.bitrix24.com',
            'https://oauth.bitrix.info/',
            null,
        ];
        yield 'change to subdomain' => [
            'https://company.bitrix24.com',
            'https://newcompany.bitrix24.com',
            'https://oauth.bitrix.info/',
            null,
        ];
        yield 'change with custom auth server' => [
            'https://old.bitrix24.com',
            'https://new.bitrix24.com',
            'https://custom.oauth.server/',
            null,
        ];
        yield 'change to URL with port' => [
            'https://old.bitrix24.com',
            'https://new.bitrix24.com:8080',
            'https://oauth.bitrix.info/',
            null,
        ];
        yield 'change to URL with path' => [
            'https://old.bitrix24.com',
            'https://new.bitrix24.com/path',
            'https://oauth.bitrix.info/',
            null,
        ];
        yield 'change to URL without protocol (adds https://)' => [
            'https://old.bitrix24.com',
            'new.bitrix24.com',
            'https://oauth.bitrix.info/',
            null,
        ];
        yield 'invalid new URL - empty string' => [
            'https://old.bitrix24.com',
            '',
            'https://oauth.bitrix.info/',
            new InvalidArgumentException(),
        ];
    }
}