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

namespace Bitrix24\SDK\Tests\Unit\Core;

use Bitrix24\SDK\Core\ExceptionContextSanitizer;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExceptionContextSanitizer::class)]
class ExceptionContextSanitizerTest extends TestCase
{
    #[DataProvider('messageProvider')]
    public function testRedactsCredentialQueryValues(string $input, string $expected): void
    {
        self::assertSame($expected, ExceptionContextSanitizer::redactMessage($input));
        self::assertSame($expected, ExceptionContextSanitizer::redactMessage($expected));
    }

    public static function messageProvider(): Generator
    {
        yield 'plain diagnostic' => ['Connection refused', 'Connection refused'];
        yield 'non-sensitive parameters' => [
            'https://example.test/token?grant_type=refresh_token&client_id=public&requestId=42&not_access_token=keep',
            'https://example.test/token?grant_type=refresh_token&client_id=public&requestId=42&not_access_token=keep',
        ];
        foreach (['client_secret', 'refresh_token', 'access_token', 'auth', 'CLIENT_SECRET', 'client%5Fsecret', '%61uth'] as $name) {
            yield $name => [
                'Failed for "https://example.test/token?' . $name . '=s%26e%3Dc+ret&requestId=42".',
                'Failed for "https://example.test/token?' . $name . '=[REDACTED]&requestId=42".',
            ];
        }

        yield 'multiple URLs and repeated parameters' => [
            '"https://a.test/?client_secret=one&client_secret=two" then \'https://b.test/?refresh_token=three\'',
            '"https://a.test/?client_secret=[REDACTED]&client_secret=[REDACTED]" then \'https://b.test/?refresh_token=[REDACTED]\'',
        ];
        yield 'last value' => ['https://a.test/?x=1&access_token=secret', 'https://a.test/?x=1&access_token=[REDACTED]'];
        yield 'fragment' => ['https://a.test/?auth=secret#details', 'https://a.test/?auth=[REDACTED]#details'];
        yield 'empty value' => ['https://a.test/?auth=&x=1', 'https://a.test/?auth=[REDACTED]&x=1'];
        yield 'whitespace' => ["https://a.test/?auth=secret\nTimeout", "https://a.test/?auth=[REDACTED]\nTimeout"];
    }

    public function testTraceRetainsOnlyDiagnosticMetadata(): void
    {
        $unsafeObject = new class implements \JsonSerializable {
            public function jsonSerialize(): never
            {
                throw new \LogicException('Trace objects must not be serialized');
            }
        };
        $trace = [[
            'file' => '/sdk/Core.php',
            'line' => 12,
            'class' => 'HttpClient',
            'function' => 'request',
            'type' => '->',
            'args' => [['client_secret' => 'secret', 'nested' => [$unsafeObject]]],
            'object' => $unsafeObject,
            'unknown' => 'secret',
        ], [
            'file' => 'https://example.test/source?auth=secret',
            'function' => 'callback',
        ]];
        self::assertSame([[
            'file' => '/sdk/Core.php', 'line' => 12, 'class' => 'HttpClient', 'function' => 'request', 'type' => '->',
        ], [
            'file' => 'https://example.test/source?auth=[REDACTED]', 'function' => 'callback',
        ]], ExceptionContextSanitizer::sanitizeTrace($trace));
    }
}
