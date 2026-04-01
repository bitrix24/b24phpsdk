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

namespace Bitrix24\SDK\Tests\Unit\OpenApi\Domain;

use Bitrix24\SDK\OpenApi\Domain\OaFieldListMethodResolver;
use Bitrix24\SDK\OpenApi\Domain\OaSchemaMethodReader;
use Bitrix24\SDK\OpenApi\Domain\OaToSdkMethodNormalizationPolicy;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class OaFieldListMethodResolverTest extends TestCase
{
    private const string SCHEMA_FIXTURE = __DIR__ . '/fixtures/openapi-field-list-methods.json';

    #[Test]
    public function itExtractsEntityKeysFromFieldListMethodsOnly(): void
    {
        $oaFieldListMethodResolver = $this->createResolver();

        $entityKeys = $oaFieldListMethodResolver->getEntityKeys(self::SCHEMA_FIXTURE);

        $this->assertSame([
            'main.eventlog',
            'tasks.task',
            'tasks.task.access',
            'tasks.task.chat.message',
        ], $entityKeys);
    }

    #[Test]
    public function itResolvesExactEntityKeyToExactMethodName(): void
    {
        $oaFieldListMethodResolver = $this->createResolver();

        $methodName = $oaFieldListMethodResolver->resolveFieldListMethodName(self::SCHEMA_FIXTURE, 'tasks.task.access');

        $this->assertSame('tasks.task.access.field.list', $methodName);
    }

    #[Test]
    public function itRejectsPartialOrUnknownEntityKeys(): void
    {
        $oaFieldListMethodResolver = $this->createResolver();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown v3 field metadata entity "tasks.task.a"');

        $oaFieldListMethodResolver->resolveFieldListMethodName(self::SCHEMA_FIXTURE, 'tasks.task.a');
    }

    private function createResolver(): OaFieldListMethodResolver
    {
        return new OaFieldListMethodResolver(
            new OaSchemaMethodReader(new Filesystem(), new OaToSdkMethodNormalizationPolicy())
        );
    }
}
