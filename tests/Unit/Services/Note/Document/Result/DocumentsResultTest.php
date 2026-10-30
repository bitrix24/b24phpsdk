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

namespace Bitrix24\SDK\Tests\Unit\Services\Note\Document\Result;

use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Note\Document\Result\DocumentItemResult;
use Bitrix24\SDK\Services\Note\Document\Result\DocumentsResult;
use Bitrix24\SDK\Services\Note\Document\Result\DocumentState;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(DocumentsResult::class)]
#[CoversClass(DocumentItemResult::class)]
class DocumentsResultTest extends TestCase
{
    #[Test]
    public function testDocumentsCastFieldsAndPreserveCursor(): void
    {
        $cursor = ['stream' => 'collections', 'collectionId' => 123, 'id' => 42];
        $raw = [
            'items'      => [[
                'id'               => 42,
                'collectionId'     => 123,
                'parentId'         => 10,
                'title'            => 'Release notes',
                'position'         => 2,
                'createdBy'        => 1,
                'updatedBy'        => 1,
                'createdAt'        => '2026-08-11T10:15:00+03:00',
                'updatedAt'        => '2026-08-11T10:15:00+03:00',
                'contentUpdatedAt' => '2026-08-11T11:02:00+03:00',
                'state'            => 'trashed',
                'isArchived'       => false,
                'isTrashed'        => true,
                'isOrphan'         => false,
            ]],
            'nextCursor' => $cursor,
        ];
        $result = new DocumentsResult($this->response($raw));
        $document = $result->getDocuments()[0];

        $this->assertInstanceOf(DocumentItemResult::class, $document);
        $this->assertSame(42, $document->id);
        $this->assertSame(10, $document->parentId);
        $this->assertSame(DocumentState::trashed, $document->state);
        $this->assertTrue($document->isTrashed);
        $this->assertFalse($document->isArchived);
        $this->assertFalse($document->isOrphan);
        $this->assertInstanceOf(CarbonImmutable::class, $document->contentUpdatedAt);
        $this->assertSame('2026-08-11T11:02:00+03:00', $document->contentUpdatedAt->toIso8601String());
        $this->assertNull($document->markdown);
        $this->assertSame($cursor, $result->getNextCursor());
    }

    #[Test]
    public function testNeverEditedDocumentHasNullContentUpdatedAt(): void
    {
        $result = new DocumentsResult($this->response([
            'items'      => [['id' => 2, 'collectionId' => 2, 'parentId' => null, 'title' => 'Hi', 'contentUpdatedAt' => null, 'state' => 'live']],
            'nextCursor' => null,
        ]));

        $this->assertNull($result->getDocuments()[0]->contentUpdatedAt);
        $this->assertSame(DocumentState::live, $result->getDocuments()[0]->state);
        $this->assertNull($result->getNextCursor());
    }

    #[Test]
    public function testEmptyPageWithCursorIsNotTheEnd(): void
    {
        $cursor = ['stream' => 'grants', 'id' => 7];
        $result = new DocumentsResult($this->response(['items' => [], 'nextCursor' => $cursor]));

        $this->assertSame([], $result->getDocuments());
        $this->assertSame($cursor, $result->getNextCursor());
    }

    private function response(array $result): Response
    {
        return new Response((new MockHttpClient(new MockResponse(json_encode(['result' => $result], JSON_THROW_ON_ERROR))))->request('POST', 'https://example.test'), new Command('note.document.list', []), new ApiLevelErrorHandler(new NullLogger()), new NullLogger());
    }
}
