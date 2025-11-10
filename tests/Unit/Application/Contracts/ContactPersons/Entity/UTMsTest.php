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

use Bitrix24\SDK\Application\Contracts\ContactPersons\Entity\UTMs;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(UTMs::class)]
class UTMsTest extends TestCase
{
    #[Test]
    #[TestDox('constructor should create UTMs object with all parameters')]
    public function testConstructorWithAllParameters(): void
    {
        $utms = new UTMs(
            source: 'google',
            medium: 'cpc',
            campaign: 'spring_sale',
            term: 'running shoes',
            content: 'banner_blue'
        );

        $this->assertEquals('google', $utms->source);
        $this->assertEquals('cpc', $utms->medium);
        $this->assertEquals('spring_sale', $utms->campaign);
        $this->assertEquals('running shoes', $utms->term);
        $this->assertEquals('banner_blue', $utms->content);
    }

    #[Test]
    #[TestDox('constructor should create UTMs object with default null values')]
    public function testConstructorWithDefaultValues(): void
    {
        $utms = new UTMs();

        $this->assertNull($utms->source);
        $this->assertNull($utms->medium);
        $this->assertNull($utms->campaign);
        $this->assertNull($utms->term);
        $this->assertNull($utms->content);
    }

    #[Test]
    #[TestDox('constructor should create UTMs object with partial parameters')]
    public function testConstructorWithPartialParameters(): void
    {
        $utms = new UTMs(
            source: 'facebook',
            medium: 'social',
            campaign: 'summer_campaign'
        );

        $this->assertEquals('facebook', $utms->source);
        $this->assertEquals('social', $utms->medium);
        $this->assertEquals('summer_campaign', $utms->campaign);
        $this->assertNull($utms->term);
        $this->assertNull($utms->content);
    }

    #[Test]
    #[TestDox('fromUrl should parse URL with all UTM parameters')]
    public function testFromUrlWithAllParameters(): void
    {
        $url = 'https://example.com/page?utm_source=google&utm_medium=cpc&utm_campaign=spring_sale&utm_term=running+shoes&utm_content=banner_blue';

        $utms = UTMs::fromUrl($url);

        $this->assertEquals('google', $utms->source);
        $this->assertEquals('cpc', $utms->medium);
        $this->assertEquals('spring_sale', $utms->campaign);
        $this->assertEquals('running shoes', $utms->term);
        $this->assertEquals('banner_blue', $utms->content);
    }

    #[Test]
    #[TestDox('fromUrl should parse URL with partial UTM parameters')]
    public function testFromUrlWithPartialParameters(): void
    {
        $url = 'https://example.com/page?utm_source=facebook&utm_medium=social';

        $utms = UTMs::fromUrl($url);

        $this->assertEquals('facebook', $utms->source);
        $this->assertEquals('social', $utms->medium);
        $this->assertNull($utms->campaign);
        $this->assertNull($utms->term);
        $this->assertNull($utms->content);
    }

    #[Test]
    #[TestDox('fromUrl should return empty UTMs for URL without query string')]
    public function testFromUrlWithoutQueryString(): void
    {
        $url = 'https://example.com/page';

        $utms = UTMs::fromUrl($url);

        $this->assertNull($utms->source);
        $this->assertNull($utms->medium);
        $this->assertNull($utms->campaign);
        $this->assertNull($utms->term);
        $this->assertNull($utms->content);
    }

    #[Test]
    #[TestDox('fromUrl should return empty UTMs for URL without UTM parameters')]
    public function testFromUrlWithoutUtmParameters(): void
    {
        $url = 'https://example.com/page?param1=value1&param2=value2';

        $utms = UTMs::fromUrl($url);

        $this->assertNull($utms->source);
        $this->assertNull($utms->medium);
        $this->assertNull($utms->campaign);
        $this->assertNull($utms->term);
        $this->assertNull($utms->content);
    }

    #[Test]
    #[TestDox('fromUrl should parse URL with mixed case UTM parameters')]
    public function testFromUrlWithMixedCaseParameters(): void
    {
        $url = 'https://example.com/page?UTM_SOURCE=Google&utm_MEDIUM=CPC&Utm_Campaign=Spring_Sale';

        $utms = UTMs::fromUrl($url);

        // UTM parameters should be converted to lowercase
        $this->assertEquals('google', $utms->source);
        $this->assertEquals('cpc', $utms->medium);
        $this->assertEquals('spring_sale', $utms->campaign);
    }

    #[Test]
    #[TestDox('fromUrl should parse URL with UTM parameters and other query parameters')]
    public function testFromUrlWithMixedParameters(): void
    {
        $url = 'https://example.com/page?id=123&utm_source=twitter&page=2&utm_medium=social&sort=date';

        $utms = UTMs::fromUrl($url);

        $this->assertEquals('twitter', $utms->source);
        $this->assertEquals('social', $utms->medium);
        $this->assertNull($utms->campaign);
    }

    #[Test]
    #[TestDox('fromUrl should handle URL with fragment')]
    public function testFromUrlWithFragment(): void
    {
        $url = 'https://example.com/page?utm_source=linkedin&utm_medium=social#section1';

        $utms = UTMs::fromUrl($url);

        $this->assertEquals('linkedin', $utms->source);
        $this->assertEquals('social', $utms->medium);
    }

    #[Test]
    #[TestDox('fromUrl should handle URL encoded values')]
    public function testFromUrlWithEncodedValues(): void
    {
        $url = 'https://example.com/page?utm_source=email&utm_campaign=new%20product&utm_content=top%20banner';

        $utms = UTMs::fromUrl($url);

        $this->assertEquals('email', $utms->source);
        $this->assertEquals('new product', $utms->campaign);
        $this->assertEquals('top banner', $utms->content);
    }

    #[Test]
    #[DataProvider('realWorldUrlsProvider')]
    #[TestDox('fromUrl should parse real-world URLs')]
    public function testFromUrlWithRealWorldUrls(
        string $url,
        ?string $expectedSource,
        ?string $expectedMedium,
        ?string $expectedCampaign,
        ?string $expectedTerm,
        ?string $expectedContent
    ): void {
        $utms = UTMs::fromUrl($url);

        $this->assertEquals($expectedSource, $utms->source);
        $this->assertEquals($expectedMedium, $utms->medium);
        $this->assertEquals($expectedCampaign, $utms->campaign);
        $this->assertEquals($expectedTerm, $utms->term);
        $this->assertEquals($expectedContent, $utms->content);
    }

    #[Test]
    #[TestDox('fromUrl should handle empty string')]
    public function testFromUrlWithEmptyString(): void
    {
        $url = '';

        $utms = UTMs::fromUrl($url);

        $this->assertNull($utms->source);
        $this->assertNull($utms->medium);
        $this->assertNull($utms->campaign);
        $this->assertNull($utms->term);
        $this->assertNull($utms->content);
    }

    #[Test]
    #[TestDox('fromUrl should handle malformed URLs gracefully')]
    public function testFromUrlWithMalformedUrl(): void
    {
        $url = 'not-a-valid-url';

        $utms = UTMs::fromUrl($url);

        // Should return empty UTMs object without throwing exception
        $this->assertInstanceOf(UTMs::class, $utms);
    }

    #[Test]
    #[TestDox('fromUrl should handle URL with only query string')]
    public function testFromUrlWithOnlyQueryString(): void
    {
        $url = '?utm_source=google&utm_medium=cpc';

        $utms = UTMs::fromUrl($url);

        $this->assertEquals('google', $utms->source);
        $this->assertEquals('cpc', $utms->medium);
    }

    #[Test]
    #[TestDox('fromUrl should handle Bitrix24 referrer URL example')]
    public function testFromUrlWithBitrix24Example(): void
    {
        $url = 'https://bitrix24.com/apps/store?utm_source=bx24';

        $utms = UTMs::fromUrl($url);

        $this->assertEquals('bx24', $utms->source);
        $this->assertNull($utms->medium);
        $this->assertNull($utms->campaign);
        $this->assertNull($utms->term);
        $this->assertNull($utms->content);
    }

    #[Test]
    #[TestDox('fromUrl should handle duplicate UTM parameters (last one wins)')]
    public function testFromUrlWithDuplicateParameters(): void
    {
        $url = 'https://example.com/page?utm_source=first&utm_source=second&utm_medium=email';

        $utms = UTMs::fromUrl($url);

        // parse_str behavior: last value wins
        $this->assertEquals('second', $utms->source);
        $this->assertEquals('email', $utms->medium);
    }

    #[Test]
    #[TestDox('fromUrl should handle special characters in UTM values')]
    public function testFromUrlWithSpecialCharacters(): void
    {
        $url = 'https://example.com/page?utm_source=email&utm_campaign=50%25+off&utm_content=red%26blue';

        $utms = UTMs::fromUrl($url);

        $this->assertEquals('email', $utms->source);
        $this->assertEquals('50% off', $utms->campaign);
        $this->assertEquals('red&blue', $utms->content);
    }

    #[Test]
    #[TestDox('UTMs object should be readonly')]
    public function testUtmsIsReadonly(): void
    {
        $utms = new UTMs(source: 'google');

        $reflectionClass = new \ReflectionClass($utms);
        $this->assertTrue($reflectionClass->isReadOnly(), 'UTMs class should be readonly');
    }

    public static function realWorldUrlsProvider(): Generator
    {
        yield 'Google Ads campaign' => [
            'https://example.com/product?utm_source=google&utm_medium=cpc&utm_campaign=black_friday_2024&utm_term=buy+shoes&utm_content=ad_variant_a',
            'google',
            'cpc',
            'black_friday_2024',
            'buy shoes',
            'ad_variant_a'
        ];

        yield 'Facebook organic post' => [
            'https://example.com/blog/article?utm_source=facebook&utm_medium=social&utm_campaign=awareness',
            'facebook',
            'social',
            'awareness',
            null,
            null
        ];

        yield 'Email newsletter' => [
            'https://example.com/landing?utm_source=newsletter&utm_medium=email&utm_campaign=monthly_digest&utm_content=header_link',
            'newsletter',
            'email',
            'monthly_digest',
            null,
            'header_link'
        ];

        yield 'Twitter post' => [
            'https://example.com/?utm_source=twitter&utm_medium=social&utm_campaign=product_launch',
            'twitter',
            'social',
            'product_launch',
            null,
            null
        ];

        yield 'LinkedIn sponsored content' => [
            'https://example.com/whitepaper?utm_source=linkedin&utm_medium=paid&utm_campaign=b2b_leads&utm_content=whitepaper_cta',
            'linkedin',
            'paid',
            'b2b_leads',
            null,
            'whitepaper_cta'
        ];

        yield 'Referral from partner site' => [
            'https://example.com/signup?utm_source=partner_site&utm_medium=referral&utm_campaign=partnership_q1',
            'partner_site',
            'referral',
            'partnership_q1',
            null,
            null
        ];

        yield 'YouTube video description' => [
            'https://example.com/offer?utm_source=youtube&utm_medium=video&utm_campaign=tutorial_series&utm_content=video_description',
            'youtube',
            'video',
            'tutorial_series',
            null,
            'video_description'
        ];

        yield 'URL without any UTM parameters' => [
            'https://example.com/page',
            null,
            null,
            null,
            null,
            null
        ];

        yield 'URL with only utm_source' => [
            'https://example.com/page?utm_source=instagram',
            'instagram',
            null,
            null,
            null,
            null
        ];

        yield 'Complex URL with path and multiple parameters' => [
            'https://example.com/category/product/details?id=123&color=red&utm_source=bing&utm_medium=cpc&size=large&utm_campaign=summer_sale',
            'bing',
            'cpc',
            'summer_sale',
            null,
            null
        ];
    }
}
