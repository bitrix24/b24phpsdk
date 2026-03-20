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

use Bitrix24\SDK\OpenApi\Domain\OaSchemaMethodReader;
use Bitrix24\SDK\OpenApi\Domain\OaToSdkMethodNormalizationPolicy;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class OaSchemaMethodReaderTest extends TestCase
{
    #[Test]
    public function itReadsAndNormalizesMethodsFromOpenApiSnapshot(): void
    {
        $oaSchemaMethodReader = new OaSchemaMethodReader(new Filesystem(), new OaToSdkMethodNormalizationPolicy());

        $methods = $oaSchemaMethodReader->readMethodNames(__DIR__ . '/fixtures/openapi-methods.json');

        $this->assertSame([
            'documentation',
            'main.eventlog.list',
            'tasks.task.get',
        ], $methods);
    }
}
