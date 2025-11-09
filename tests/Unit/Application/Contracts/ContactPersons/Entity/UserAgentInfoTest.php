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

namespace Bitrix24\SDK\Tests\Unit\Application\Contracts\ContactPersons\Entity;

use Bitrix24\SDK\Application\Contracts\ContactPersons\Entity\UserAgentInfo;
use Bitrix24\SDK\Application\Contracts\ContactPersons\Entity\UTMs;
use Darsyn\IP\Version\Multi as IP;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserAgentInfo::class)]
class UserAgentInfoTest extends TestCase
{
    #[Test]
    #[TestDox('constructor should create UserAgentInfo with all parameters')]
    public function testConstructorWithAllParameters(): void
    {
        $ip = IP::factory('192.168.1.1');
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';
        $referrer = 'https://google.com/search?q=test';
        $fingerprint = 'unique-browser-fingerprint-12345';

        $userAgentInfo = new UserAgentInfo(
            ip: $ip,
            userAgent: $userAgent,
            referrer: $referrer,
            fingerprint: $fingerprint
        );

        $this->assertSame($ip, $userAgentInfo->ip);
        $this->assertEquals($userAgent, $userAgentInfo->userAgent);
        $this->assertEquals($referrer, $userAgentInfo->referrer);
        $this->assertEquals($fingerprint, $userAgentInfo->fingerprint);
    }

    #[Test]
    #[TestDox('constructor should create UserAgentInfo with only required parameter')]
    public function testConstructorWithOnlyIp(): void
    {
        $ip = IP::factory('10.0.0.1');

        $userAgentInfo = new UserAgentInfo(ip: $ip);

        $this->assertSame($ip, $userAgentInfo->ip);
        $this->assertNull($userAgentInfo->userAgent);
        $this->assertNull($userAgentInfo->referrer);
        $this->assertNull($userAgentInfo->fingerprint);
    }

    #[Test]
    #[TestDox('constructor should create UserAgentInfo with null IP')]
    public function testConstructorWithNullIp(): void
    {
        $userAgent = 'Mozilla/5.0';

        $userAgentInfo = new UserAgentInfo(
            ip: null,
            userAgent: $userAgent
        );

        $this->assertNull($userAgentInfo->ip);
        $this->assertEquals($userAgent, $userAgentInfo->userAgent);
    }

    #[Test]
    #[TestDox('constructor should handle IPv4 addresses')]
    public function testConstructorWithIpv4(): void
    {
        $ip = IP::factory('192.168.0.1');

        $userAgentInfo = new UserAgentInfo(ip: $ip);

        $this->assertInstanceOf(IP::class, $userAgentInfo->ip);
        $this->assertSame($ip, $userAgentInfo->ip);
    }

    #[Test]
    #[TestDox('constructor should handle IPv6 addresses')]
    public function testConstructorWithIpv6(): void
    {
        $ip = IP::factory('2001:0db8:85a3:0000:0000:8a2e:0370:7334');

        $userAgentInfo = new UserAgentInfo(ip: $ip);

        $this->assertInstanceOf(IP::class, $userAgentInfo->ip);
    }

    #[Test]
    #[TestDox('constructor should handle localhost IP')]
    public function testConstructorWithLocalhostIp(): void
    {
        $ip = IP::factory('127.0.0.1');

        $userAgentInfo = new UserAgentInfo(ip: $ip);

        $this->assertInstanceOf(IP::class, $userAgentInfo->ip);
        $this->assertSame($ip, $userAgentInfo->ip);
    }

    #[Test]
    #[DataProvider('userAgentStringsProvider')]
    #[TestDox('constructor should handle various user agent strings')]
    public function testConstructorWithVariousUserAgents(string $userAgent): void
    {
        $userAgentInfo = new UserAgentInfo(
            ip: null,
            userAgent: $userAgent
        );

        $this->assertEquals($userAgent, $userAgentInfo->userAgent);
    }

    #[Test]
    #[TestDox('getUTMs should return empty UTMs when referrer is null')]
    public function testGetUTMsWithNullReferrer(): void
    {
        $userAgentInfo = new UserAgentInfo(
            ip: IP::factory('192.168.1.1'),
            userAgent: 'Mozilla/5.0'
        );

        $utms = $userAgentInfo->getUTMs();

        $this->assertInstanceOf(UTMs::class, $utms);
        $this->assertNull($utms->source);
        $this->assertNull($utms->medium);
        $this->assertNull($utms->campaign);
        $this->assertNull($utms->term);
        $this->assertNull($utms->content);
    }

    #[Test]
    #[TestDox('getUTMs should parse UTMs from referrer with all parameters')]
    public function testGetUTMsWithFullReferrer(): void
    {
        $referrer = 'https://example.com/page?utm_source=google&utm_medium=cpc&utm_campaign=spring_sale&utm_term=shoes&utm_content=banner';

        $userAgentInfo = new UserAgentInfo(
            ip: null,
            referrer: $referrer
        );

        $utms = $userAgentInfo->getUTMs();

        $this->assertEquals('google', $utms->source);
        $this->assertEquals('cpc', $utms->medium);
        $this->assertEquals('spring_sale', $utms->campaign);
        $this->assertEquals('shoes', $utms->term);
        $this->assertEquals('banner', $utms->content);
    }

    #[Test]
    #[TestDox('getUTMs should parse UTMs from referrer with partial parameters')]
    public function testGetUTMsWithPartialReferrer(): void
    {
        $referrer = 'https://facebook.com/post?utm_source=facebook&utm_medium=social';

        $userAgentInfo = new UserAgentInfo(
            ip: null,
            referrer: $referrer
        );

        $utms = $userAgentInfo->getUTMs();

        $this->assertEquals('facebook', $utms->source);
        $this->assertEquals('social', $utms->medium);
        $this->assertNull($utms->campaign);
        $this->assertNull($utms->term);
        $this->assertNull($utms->content);
    }

    #[Test]
    #[TestDox('getUTMs should return empty UTMs when referrer has no UTM parameters')]
    public function testGetUTMsWithReferrerWithoutUtm(): void
    {
        $referrer = 'https://example.com/page?id=123&page=2';

        $userAgentInfo = new UserAgentInfo(
            ip: null,
            referrer: $referrer
        );

        $utms = $userAgentInfo->getUTMs();

        $this->assertNull($utms->source);
        $this->assertNull($utms->medium);
        $this->assertNull($utms->campaign);
    }

    #[Test]
    #[TestDox('getUTMs should handle Bitrix24 referrer example')]
    public function testGetUTMsWithBitrix24Referrer(): void
    {
        $referrer = 'https://bitrix24.com/apps/store?utm_source=bx24';

        $userAgentInfo = new UserAgentInfo(
            ip: IP::factory('192.168.1.1'),
            userAgent: 'Mozilla/5.0',
            referrer: $referrer
        );

        $utms = $userAgentInfo->getUTMs();

        $this->assertEquals('bx24', $utms->source);
        $this->assertNull($utms->medium);
        $this->assertNull($utms->campaign);
    }

    #[Test]
    #[DataProvider('referrerWithUTMsProvider')]
    #[TestDox('getUTMs should parse various referrer URLs with UTMs')]
    public function testGetUTMsWithVariousReferrers(
        string $referrer,
        ?string $expectedSource,
        ?string $expectedMedium,
        ?string $expectedCampaign
    ): void {
        $userAgentInfo = new UserAgentInfo(
            ip: null,
            referrer: $referrer
        );

        $utms = $userAgentInfo->getUTMs();

        $this->assertEquals($expectedSource, $utms->source);
        $this->assertEquals($expectedMedium, $utms->medium);
        $this->assertEquals($expectedCampaign, $utms->campaign);
    }

    #[Test]
    #[TestDox('UserAgentInfo should be readonly')]
    public function testUserAgentInfoIsReadonly(): void
    {
        $userAgentInfo = new UserAgentInfo(ip: null);

        $reflectionClass = new \ReflectionClass($userAgentInfo);
        $this->assertTrue($reflectionClass->isReadOnly(), 'UserAgentInfo class should be readonly');
    }

    #[Test]
    #[TestDox('constructor should handle complete real-world scenario')]
    public function testConstructorWithCompleteRealWorldScenario(): void
    {
        $ip = IP::factory('203.0.113.42');
        $userAgent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_7_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.2 Mobile/15E148 Safari/604.1';
        $referrer = 'https://google.com/search?q=bitrix24&utm_source=google&utm_medium=organic';
        $fingerprint = 'fp_a1b2c3d4e5f6';

        $userAgentInfo = new UserAgentInfo(
            ip: $ip,
            userAgent: $userAgent,
            referrer: $referrer,
            fingerprint: $fingerprint
        );

        // Verify all properties
        $this->assertInstanceOf(IP::class, $userAgentInfo->ip);
        $this->assertSame($ip, $userAgentInfo->ip);
        $this->assertEquals($userAgent, $userAgentInfo->userAgent);
        $this->assertEquals($referrer, $userAgentInfo->referrer);
        $this->assertEquals($fingerprint, $userAgentInfo->fingerprint);

        // Verify UTMs extracted from referrer
        $utms = $userAgentInfo->getUTMs();
        $this->assertEquals('google', $utms->source);
        $this->assertEquals('organic', $utms->medium);
    }

    #[Test]
    #[TestDox('getUTMs should return same UTMs object structure on multiple calls')]
    public function testGetUTMsConsistency(): void
    {
        $referrer = 'https://example.com?utm_source=test&utm_medium=email';

        $userAgentInfo = new UserAgentInfo(
            ip: null,
            referrer: $referrer
        );

        $utms1 = $userAgentInfo->getUTMs();
        $utms2 = $userAgentInfo->getUTMs();

        // Different objects but same values
        $this->assertNotSame($utms1, $utms2);
        $this->assertEquals($utms1->source, $utms2->source);
        $this->assertEquals($utms1->medium, $utms2->medium);
    }

    #[Test]
    #[TestDox('constructor should handle empty string values')]
    public function testConstructorWithEmptyStrings(): void
    {
        $userAgentInfo = new UserAgentInfo(
            ip: null,
            userAgent: '',
            referrer: '',
            fingerprint: ''
        );

        $this->assertEquals('', $userAgentInfo->userAgent);
        $this->assertEquals('', $userAgentInfo->referrer);
        $this->assertEquals('', $userAgentInfo->fingerprint);
    }

    #[Test]
    #[TestDox('getUTMs should handle empty referrer string')]
    public function testGetUTMsWithEmptyReferrer(): void
    {
        $userAgentInfo = new UserAgentInfo(
            ip: null,
            referrer: ''
        );

        $utms = $userAgentInfo->getUTMs();

        $this->assertInstanceOf(UTMs::class, $utms);
        $this->assertNull($utms->source);
    }

    #[Test]
    #[TestDox('constructor should handle very long user agent strings')]
    public function testConstructorWithLongUserAgent(): void
    {
        $longUserAgent = str_repeat('Mozilla/5.0 ', 100);

        $userAgentInfo = new UserAgentInfo(
            ip: null,
            userAgent: $longUserAgent
        );

        $this->assertEquals($longUserAgent, $userAgentInfo->userAgent);
    }

    #[Test]
    #[TestDox('constructor should handle special characters in fingerprint')]
    public function testConstructorWithSpecialCharactersInFingerprint(): void
    {
        $fingerprint = 'fp_!@#$%^&*()_+-=[]{}|;:,.<>?';

        $userAgentInfo = new UserAgentInfo(
            ip: null,
            fingerprint: $fingerprint
        );

        $this->assertEquals($fingerprint, $userAgentInfo->fingerprint);
    }

    public static function userAgentStringsProvider(): Generator
    {
        yield 'Chrome on Windows' => [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        ];

        yield 'Firefox on macOS' => [
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:109.0) Gecko/20100101 Firefox/121.0'
        ];

        yield 'Safari on iPhone' => [
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_2_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Mobile/15E148 Safari/604.1'
        ];

        yield 'Edge on Windows' => [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.0.0'
        ];

        yield 'Android Chrome' => [
            'Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36'
        ];

        yield 'Opera on Linux' => [
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 OPR/106.0.0.0'
        ];

        yield 'Bot user agent' => [
            'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'
        ];

        yield 'Empty user agent' => [
            ''
        ];
    }

    public static function referrerWithUTMsProvider(): Generator
    {
        yield 'Google search with organic' => [
            'https://google.com/search?q=bitrix24&utm_source=google&utm_medium=organic',
            'google',
            'organic',
            null
        ];

        yield 'Facebook post' => [
            'https://facebook.com/post/123?utm_source=facebook&utm_medium=social&utm_campaign=product_launch',
            'facebook',
            'social',
            'product_launch'
        ];

        yield 'Email newsletter' => [
            'https://example.com/landing?utm_source=newsletter&utm_medium=email&utm_campaign=weekly_digest',
            'newsletter',
            'email',
            'weekly_digest'
        ];

        yield 'Twitter link' => [
            'https://example.com/?utm_source=twitter&utm_medium=social',
            'twitter',
            'social',
            null
        ];

        yield 'LinkedIn sponsored' => [
            'https://example.com/whitepaper?utm_source=linkedin&utm_medium=paid&utm_campaign=b2b_campaign',
            'linkedin',
            'paid',
            'b2b_campaign'
        ];

        yield 'No UTM parameters' => [
            'https://example.com/page?id=123',
            null,
            null,
            null
        ];

        yield 'Direct visit (no query)' => [
            'https://example.com/',
            null,
            null,
            null
        ];
    }
}
