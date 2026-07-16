<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Core\ValueObjects;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\ValueObjects\Url;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Url::class)]
class UrlTest extends TestCase
{
    #[Test]
    #[TestDox('valid URL is accepted and returned unchanged')]
    #[DataProvider('validUrlProvider')]
    public function testValidUrl(string $url): void
    {
        $this->assertSame($url, (new Url($url))->getUrl());
    }

    #[Test]
    #[TestDox('invalid URL throws InvalidArgumentException')]
    #[DataProvider('invalidUrlProvider')]
    public function testInvalidUrl(string $url): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Url($url);
    }

    public static function validUrlProvider(): Generator
    {
        yield 'https' => ['https://example.com/handler'];
        yield 'https with port and path' => ['https://example.com:8443/a/b?c=d'];
        yield 'http' => ['http://example.com'];
    }

    public static function invalidUrlProvider(): Generator
    {
        yield 'empty' => [''];
        yield 'no scheme' => ['example.com/handler'];
        yield 'plain text' => ['not a url'];
    }
}
