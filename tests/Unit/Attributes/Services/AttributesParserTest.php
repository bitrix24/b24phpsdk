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
use Bitrix24\SDK\Attributes\Services\SupportedInSdkApiMethod;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
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
    #[TestDox('getSupportedInSdkApiMethods() returns readonly VO metadata and supports compound return types')]
    public function testGetSupportedInSdkApiMethodsReturnsSupportedInSdkApiMethodVo(): void
    {
        $attributesParser = new AttributesParser(TyphoonReflector::build(), new Filesystem());

        $methods = $attributesParser->getSupportedInSdkApiMethods(
            [AttributesParserReturnTypesFixture::class],
            dirname(__DIR__, 4).DIRECTORY_SEPARATOR
        );

        $this->assertCount(5, $methods);
        $this->assertContainsOnlyInstancesOf(SupportedInSdkApiMethod::class, $methods);

        $supportedInSdkApiMethod = $this->getMethodByName($methods, 'test.named.class');
        $this->assertSame('main', $supportedInSdkApiMethod->sdkScope);
        $this->assertSame('namedClassResult', $supportedInSdkApiMethod->sdkMethodName);
        $this->assertSame(ApiVersion::v1, $supportedInSdkApiMethod->apiVersion);
        $this->assertSame(
            AttributesParserReturnTypesFixture::class,
            $supportedInSdkApiMethod->sdkClassName
        );
        $this->assertSame(AttributesParserResultFixture::class, $supportedInSdkApiMethod->sdkReturnTypeClass);
        $this->assertSame(AttributesParserResultFixture::class, $supportedInSdkApiMethod->sdkReturnTypeDeclaration);
        $this->assertStringEndsWith('tests/Unit/Attributes/Services/AttributesParserTest.ph', $supportedInSdkApiMethod->sdkReturnTypeFileName);

        $scalarMethod = $this->getMethodByName($methods, 'test.scalar.result');
        $this->assertSame('int', $scalarMethod->sdkReturnTypeDeclaration);
        $this->assertNull($scalarMethod->sdkReturnTypeClass);
        $this->assertNull($scalarMethod->sdkReturnTypeFileName);

        $unionMethod = $this->getMethodByName($methods, 'test.union.result');
        $this->assertSame('int|string', $unionMethod->sdkReturnTypeDeclaration);
        $this->assertNull($unionMethod->sdkReturnTypeClass);
        $this->assertNull($unionMethod->sdkReturnTypeFileName);

        $nullableMethod = $this->getMethodByName($methods, 'test.nullable.result');
        $this->assertSame(AttributesParserResultFixture::class . '|null', $nullableMethod->sdkReturnTypeDeclaration);
        $this->assertSame(AttributesParserResultFixture::class, $nullableMethod->sdkReturnTypeClass);
        $this->assertStringEndsWith('tests/Unit/Attributes/Services/AttributesParserTest.ph', $nullableMethod->sdkReturnTypeFileName);

        $intersectionMethod = $this->getMethodByName($methods, 'test.intersection.result');
        $this->assertSame(
            AttributesParserIntersectionLeftFixture::class . '&' . AttributesParserIntersectionRightFixture::class,
            $intersectionMethod->sdkReturnTypeDeclaration
        );
        $this->assertNull($intersectionMethod->sdkReturnTypeClass);
        $this->assertNull($intersectionMethod->sdkReturnTypeFileName);
    }

    /**
     * @param list<SupportedInSdkApiMethod> $methods
     */
    private function getMethodByName(array $methods, string $methodName): SupportedInSdkApiMethod
    {
        foreach ($methods as $method) {
            if ($method->name === $methodName) {
                return $method;
            }
        }

        self::fail(sprintf('Method "%s" not found in parser output', $methodName));
    }
}

#[ApiServiceMetadata(new Scope(['main']))]
final class AttributesParserReturnTypesFixture
{
    #[ApiEndpointMetadata('test.named.class', 'https://example.com/test.named.class')]
    public function namedClassResult(): AttributesParserResultFixture
    {
        return new AttributesParserResultFixture();
    }

    #[ApiEndpointMetadata('test.scalar.result', 'https://example.com/test.scalar.result')]
    public function scalarResult(): int
    {
        return 1;
    }

    #[ApiEndpointMetadata('test.union.result', 'https://example.com/test.union.result', apiVersion: ApiVersion::v3)]
    public function unionResult(int $id): int|string
    {
        return $id;
    }

    #[ApiEndpointMetadata('test.nullable.result', 'https://example.com/test.nullable.result')]
    public function nullableResult(): ?AttributesParserResultFixture
    {
        return new AttributesParserResultFixture();
    }

    #[ApiEndpointMetadata('test.intersection.result', 'https://example.com/test.intersection.result')]
    public function intersectionResult(): AttributesParserIntersectionLeftFixture&AttributesParserIntersectionRightFixture
    {
        return new AttributesParserIntersectionResultFixture();
    }
}

final class AttributesParserResultFixture
{
}

interface AttributesParserIntersectionLeftFixture
{
}

interface AttributesParserIntersectionRightFixture
{
}

final class AttributesParserIntersectionResultFixture implements AttributesParserIntersectionLeftFixture, AttributesParserIntersectionRightFixture
{
}
