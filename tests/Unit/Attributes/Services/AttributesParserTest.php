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

namespace Bitrix24\SDK\Tests\Unit\Attributes\Services;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Attributes\Services\AttributesParser;
use Bitrix24\SDK\Core\Credentials\Scope;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Typhoon\Reflection\TyphoonReflector;

#[CoversClass(AttributesParser::class)]
class AttributesParserTest extends TestCase
{
    #[Test]
    #[TestDox('getSupportedInSdkApiMethods() supports service methods with union return types')]
    public function testGetSupportedInSdkApiMethodsSupportsUnionReturnTypes(): void
    {
        $parser = new AttributesParser(TyphoonReflector::build(), new Filesystem());

        $methods = $parser->getSupportedInSdkApiMethods(
            [AttributesParserUnionReturnTypeFixture::class],
            dirname(__DIR__, 4) . DIRECTORY_SEPARATOR
        );

        $this->assertArrayHasKey('test.union.result', $methods);
        $this->assertSame('main', $methods['test.union.result']['sdk_scope']);
        $this->assertSame('unionResult', $methods['test.union.result']['sdk_method_name']);
        $this->assertSame(
            AttributesParserUnionReturnTypeFixture::class,
            $methods['test.union.result']['sdk_class_name']
        );
    }
}

#[ApiServiceMetadata(new Scope(['main']))]
final class AttributesParserUnionReturnTypeFixture
{
    #[ApiEndpointMetadata('test.union.result', 'https://example.com/test.union.result')]
    public function unionResult(int $id): int|string
    {
        return $id;
    }
}
