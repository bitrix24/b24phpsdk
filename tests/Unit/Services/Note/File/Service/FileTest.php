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

use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Note\File\Result\FileFieldResult;
use Bitrix24\SDK\Services\Note\File\Result\FileFieldsResult;
use Bitrix24\SDK\Services\Note\File\Result\FileResult;
use Bitrix24\SDK\Services\Note\File\Service\File;
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

    /**
     * @return array{0: string, 1: array<string, mixed>}
     */
    private function call(callable $action): array
    {
        $method = null;
        $captured = [];
        $response = new Response(
            new MockResponse(''),
            new Command('', []),
            new ApiLevelErrorHandler(new NullLogger()),
            new NullLogger()
        );

        $core = $this->createStub(CoreInterface::class);
        $core->method('call')->willReturnCallback(
            function (string $apiMethod, array $parameters = []) use (&$method, &$captured, $response): Response {
                $method = $apiMethod;
                $captured = $parameters;

                return $response;
            }
        );

        $action(new File($core, new NullLogger()));

        return [$method, $captured];
    }
}
