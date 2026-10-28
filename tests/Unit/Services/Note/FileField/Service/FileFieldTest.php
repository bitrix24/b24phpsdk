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

namespace Bitrix24\SDK\Tests\Unit\Services\Note\FileField\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Note\File\Result\FileFieldResult;
use Bitrix24\SDK\Services\Note\File\Result\FileFieldsResult;
use Bitrix24\SDK\Services\Note\FileField\Service\FileField;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(FileField::class)]
class FileFieldTest extends TestCase
{
    #[Test]
    public function testGetMapsNameAndOptionalSelectToV3(): void
    {
        $this->assertCall('get', ['id'], 'note.file.field.get', ['name' => 'id'], FileFieldResult::class);
        $this->assertCall('get', ['id', ['name', 'type']], 'note.file.field.get', ['name' => 'id', 'select' => ['name', 'type']], FileFieldResult::class);
    }

    #[Test]
    public function testListMapsOptionalSelectToV3(): void
    {
        $this->assertCall('list', [], 'note.file.field.list', [], FileFieldsResult::class);
        $this->assertCall('list', [['name', 'type']], 'note.file.field.list', ['select' => ['name', 'type']], FileFieldsResult::class);
    }

    #[Test]
    public function testMetadataUsesNoteScopeAndEnglishV3Endpoints(): void
    {
        $metadata = (new \ReflectionClass(FileField::class))->getAttributes(ApiServiceMetadata::class)[0]->newInstance();
        $this->assertSame(['note'], $metadata->scope->getScopeCodes());
        foreach (['get', 'list'] as $method) {
            $endpoint = (new \ReflectionMethod(FileField::class, $method))->getAttributes(ApiEndpointMetadata::class)[0]->newInstance();
            $this->assertSame('note.file.field.' . $method, $endpoint->name);
            $this->assertSame('https://apidocs.bitrix24.com/api-reference/note/file/note-file-field-' . $method . '.html', $endpoint->documentationUrl);
            $this->assertSame(ApiVersion::v3, $endpoint->apiVersion);
        }
    }

    #[Test]
    public function testCoreExceptionsPropagate(): void
    {
        $core = $this->createStub(CoreInterface::class);
        $exception = new BaseException('request rejected');
        $core->method('call')->willThrowException($exception);
        $this->expectExceptionObject($exception);
        (new FileField($core, new NullLogger()))->get('id');
    }

    private function assertCall(string $method, array $arguments, string $endpoint, array $parameters, string $resultClass): void
    {
        $response = new Response(new MockResponse('{"result":{"items":[]}}'), new Command($endpoint, $parameters), new ApiLevelErrorHandler(new NullLogger()), new NullLogger());
        $core = $this->createMock(CoreInterface::class);
        $core->expects($this->once())->method('call')->with($endpoint, $parameters, ApiVersion::v3)->willReturn($response);
        $result = (new FileField($core, new NullLogger()))->$method(...$arguments);

        $this->assertInstanceOf($resultClass, $result);
        $this->assertSame($response, $result->getCoreResponse());
    }
}
