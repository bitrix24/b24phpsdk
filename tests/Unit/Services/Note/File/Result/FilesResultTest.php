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

namespace Bitrix24\SDK\Tests\Unit\Services\Note\File\Result;

use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Note\File\Result\FileItemResult;
use Bitrix24\SDK\Services\Note\File\Result\FilesResult;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(FilesResult::class)]
#[CoversClass(FileItemResult::class)]
class FilesResultTest extends TestCase
{
    #[Test]
    public function testFilesCastFieldsAndPreserveResponseAndCursor(): void
    {
        $cursor = ['id' => '9223372036854775808', 'future' => ['token' => false]];
        $raw = ['items' => [['id' => '15', 'documentId' => '27', 'name' => 'diagram.png', 'size' => '120', 'createdAt' => '2026-10-27T14:03:02+06:00']], 'nextCursor' => $cursor];
        $response = $this->response($raw);
        $result = new FilesResult($response);
        $file = $result->getFiles()[0];

        $this->assertInstanceOf(FileItemResult::class, $file);
        $this->assertSame(15, $file->id);
        $this->assertSame(27, $file->documentId);
        $this->assertSame(120, $file->size);
        $this->assertSame('diagram.png', $file->name);
        $this->assertInstanceOf(CarbonImmutable::class, $file->createdAt);
        $this->assertSame('2026-10-27T14:03:02+06:00', $file->createdAt->toIso8601String());
        $this->assertSame($cursor, $result->getNextCursor());
        $this->assertSame($response, $result->getCoreResponse());
        $this->assertSame($raw, $result->getCoreResponse()->getResponseData()->getResult());
    }

    #[Test]
    public function testTerminalEmptyPageAndMissingCursor(): void
    {
        $result = new FilesResult($this->response(['items' => [], 'nextCursor' => null]));
        $this->assertSame([], $result->getFiles());
        $this->assertNull($result->getNextCursor());
        $this->assertNull((new FilesResult($this->response(['items' => []])))->getNextCursor());
    }

    #[Test]
    public function testEmptyCursorAndMissingLegacyDateRemainUnchanged(): void
    {
        $result = new FilesResult($this->response(['items' => [['id' => 1, 'documentId' => 27, 'name' => 'old.txt']], 'nextCursor' => []]));

        $this->assertSame([], $result->getNextCursor());
        $this->assertNull($result->getFiles()[0]->createdAt);
    }

    private function response(array $result): Response
    {
        return new Response((new MockHttpClient(new MockResponse(json_encode(['result' => $result], JSON_THROW_ON_ERROR))))->request('POST', 'https://example.test'), new Command('note.file.list', []), new ApiLevelErrorHandler(new NullLogger()), new NullLogger());
    }
}
