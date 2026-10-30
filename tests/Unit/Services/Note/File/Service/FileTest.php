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

namespace Bitrix24\SDK\Tests\Unit\Services\Note\File\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Note\File\Result\FileFieldResult;
use Bitrix24\SDK\Services\Note\File\Result\FileFieldsResult;
use Bitrix24\SDK\Services\Note\File\Result\FileResult;
use Bitrix24\SDK\Services\Note\File\Result\FilesResult;
use Bitrix24\SDK\Services\Note\File\Service\File;
use Bitrix24\SDK\Services\Note\File\Service\FileListPagination;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(File::class)]
class FileTest extends TestCase
{
    private File $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = new File(new NullCore(), new NullLogger());
    }

    #[Test]
    public function testAddReturnsFileResult(): void
    {
        $this->assertInstanceOf(FileResult::class, $this->service->add(1, 'diagram.png', base64_encode('binary')));
    }

    #[Test]
    public function testFieldGetReturnsFileFieldResult(): void
    {
        $this->assertInstanceOf(FileFieldResult::class, $this->service->fieldGet('name'));
    }

    #[Test]
    public function testFieldListReturnsFileFieldsResult(): void
    {
        $this->assertInstanceOf(FileFieldsResult::class, $this->service->fieldList());
    }

    #[Test]
    public function testGetReturnsFileResult(): void
    {
        $this->assertInstanceOf(FileResult::class, $this->service->get(1, 2));
    }

    #[Test]
    #[TestDox('add() sends documentId, fileName and base64 fileContent')]
    public function testAddSendsDocumentIdFileNameAndContent(): void
    {
        $content = base64_encode('binary-data');

        [$method, $captured] = $this->call(static fn (File $service) => $service->add(1, 'diagram.png', $content));

        $this->assertSame('note.file.add', $method);
        $this->assertSame(1, $captured['documentId']);
        $this->assertSame('diagram.png', $captured['fileName']);
        $this->assertSame($content, $captured['fileContent']);
    }

    #[Test]
    #[TestDox('get() sends id and documentId')]
    public function testGetSendsIdAndDocumentId(): void
    {
        [$method, $captured] = $this->call(static fn (File $service) => $service->get(3, 7));

        $this->assertSame('note.file.get', $method);
        $this->assertSame(3, $captured['id']);
        $this->assertSame(7, $captured['documentId']);
    }

    #[Test]
    public function testListOmitsUnspecifiedPaginationAndUsesV3(): void
    {
        [$method, $parameters, $version] = $this->call(static fn (File $service) => $service->list(27));

        $this->assertSame('note.file.list', $method);
        $this->assertSame(['documentId' => 27], $parameters);
        $this->assertSame(ApiVersion::v3, $version);
        $this->assertInstanceOf(FilesResult::class, $this->service->list(27));
    }

    #[Test]
    public function testListForwardsOpaquePagination(): void
    {
        $cursor = ['id' => '9223372036854775808', 'future' => ['token' => false]];
        [$method, $parameters, $version] = $this->call(
            static fn (File $service) => $service->list(27, new FileListPagination(0, $cursor))
        );

        $this->assertSame('note.file.list', $method);
        $this->assertSame(['documentId' => 27, 'pagination' => ['limit' => 0, 'afterCursor' => $cursor]], $parameters);
        $this->assertSame(ApiVersion::v3, $version);
    }

    #[Test]
    public function testListDocumentsEnglishV3Endpoint(): void
    {
        $metadata = (new \ReflectionMethod(File::class, 'list'))->getAttributes(ApiEndpointMetadata::class)[0]->newInstance();

        $this->assertSame('note.file.list', $metadata->name);
        $this->assertSame('https://apidocs.bitrix24.com/api-reference/note/file/note-file-list.html', $metadata->documentationUrl);
        $this->assertSame(ApiVersion::v3, $metadata->apiVersion);
    }

    #[Test]
    public function testListPropagatesCoreException(): void
    {
        $exception = new BaseException('request rejected');
        $core = $this->createStub(CoreInterface::class);
        $core->method('call')->willThrowException($exception);

        $this->expectExceptionObject($exception);
        (new File($core, new NullLogger()))->list(27);
    }

    /**
     * @return array{0: string, 1: array<string, mixed>, 2: ApiVersion}
     */
    private function call(callable $action): array
    {
        $method = null;
        $captured = [];
        $version = null;
        $response = new Response(
            new MockResponse(''),
            new Command('', []),
            new ApiLevelErrorHandler(new NullLogger()),
            new NullLogger()
        );

        $core = $this->createStub(CoreInterface::class);
        $core->method('call')->willReturnCallback(
            function (string $apiMethod, array $parameters = [], ApiVersion $apiVersion = ApiVersion::v1) use (&$method, &$captured, &$version, $response): Response {
                $method = $apiMethod;
                $captured = $parameters;
                $version = $apiVersion;

                return $response;
            }
        );

        $action(new File($core, new NullLogger()));

        return [$method, $captured, $version];
    }
}
