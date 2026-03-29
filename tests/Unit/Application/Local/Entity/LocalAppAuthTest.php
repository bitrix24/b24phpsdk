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

namespace Bitrix24\SDK\Tests\Unit\Application\Local\Entity;

use Bitrix24\SDK\Application\Local\Entity\LocalAppAuth;
use Bitrix24\SDK\Core\Credentials\AuthToken;
use Bitrix24\SDK\Core\Credentials\DefaultOAuthServerUrl;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(LocalAppAuth::class)]
class LocalAppAuthTest extends TestCase
{
    private const string DOMAIN_URL = 'https://example.bitrix24.com';

    private const string APPLICATION_TOKEN = 'app_token_123';

    private const string OAUTH_SERVER_URL = 'https://oauth.bitrix24.tech/';

    private function makeAuthToken(): AuthToken
    {
        return new AuthToken('access_token_abc', 'refresh_token_xyz', 9999999999);
    }

    private function makePayload(?string $oauthServerUrl = self::OAUTH_SERVER_URL): array
    {
        $payload = [
            'auth_token' => [
                'access_token' => 'access_token_abc',
                'refresh_token' => 'refresh_token_xyz',
                'expires' => 9999999999,
            ],
            'domain_url' => self::DOMAIN_URL,
            'application_token' => self::APPLICATION_TOKEN,
        ];

        if ($oauthServerUrl !== null) {
            $payload['oauth_server_url'] = $oauthServerUrl;
        }

        return $payload;
    }

    #[Test]
    public function testGetOAuthServerUrlReturnsValuePassedToConstructor(): void
    {
        $localAppAuth = new LocalAppAuth(
            $this->makeAuthToken(),
            self::DOMAIN_URL,
            self::APPLICATION_TOKEN,
            self::OAUTH_SERVER_URL
        );

        $this->assertSame(self::OAUTH_SERVER_URL, $localAppAuth->getOAuthServerUrl());
    }

    #[Test]
    public function testToArrayIncludesOauthServerUrl(): void
    {
        $localAppAuth = new LocalAppAuth(
            $this->makeAuthToken(),
            self::DOMAIN_URL,
            self::APPLICATION_TOKEN,
            self::OAUTH_SERVER_URL
        );

        $data = $localAppAuth->toArray();

        $this->assertArrayHasKey('oauth_server_url', $data);
        $this->assertSame(self::OAUTH_SERVER_URL, $data['oauth_server_url']);
    }

    #[Test]
    public function testInitFromArrayRestoresOauthServerUrl(): void
    {
        $localAppAuth = LocalAppAuth::initFromArray($this->makePayload(self::OAUTH_SERVER_URL));

        $this->assertSame(self::OAUTH_SERVER_URL, $localAppAuth->getOAuthServerUrl());
    }

    #[Test]
    public function testInitFromArrayFallsBackToDefaultWhenOauthServerUrlAbsent(): void
    {
        $localAppAuth = LocalAppAuth::initFromArray($this->makePayload(null));

        $this->assertSame(DefaultOAuthServerUrl::default(), $localAppAuth->getOAuthServerUrl());
    }

    #[Test]
    public function testRoundTripPreservesAllFields(): void
    {
        $localAppAuth = new LocalAppAuth(
            $this->makeAuthToken(),
            self::DOMAIN_URL,
            self::APPLICATION_TOKEN,
            self::OAUTH_SERVER_URL
        );

        $restored = LocalAppAuth::initFromArray($localAppAuth->toArray());

        $this->assertSame($localAppAuth->getDomainUrl(), $restored->getDomainUrl());
        $this->assertSame($localAppAuth->getApplicationToken(), $restored->getApplicationToken());
        $this->assertSame($localAppAuth->getOAuthServerUrl(), $restored->getOAuthServerUrl());
        $this->assertSame($localAppAuth->getAuthToken()->accessToken, $restored->getAuthToken()->accessToken);
        $this->assertSame($localAppAuth->getAuthToken()->refreshToken, $restored->getAuthToken()->refreshToken);
        $this->assertSame($localAppAuth->getAuthToken()->expires, $restored->getAuthToken()->expires);
    }

    #[Test]
    #[DataProvider('oauthServerUrlVariantsProvider')]
    public function testInitFromArrayWithDifferentOauthServerUrls(string $url): void
    {
        $localAppAuth = LocalAppAuth::initFromArray($this->makePayload($url));

        $this->assertSame($url, $localAppAuth->getOAuthServerUrl());
    }

    public static function oauthServerUrlVariantsProvider(): Generator
    {
        yield 'east region' => [DefaultOAuthServerUrl::east()];
        yield 'west region' => [DefaultOAuthServerUrl::west()];
        yield 'custom server' => ['https://oauth.my-custom-server.com/'];
    }
}
