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

namespace Bitrix24\SDK\Tests\Unit\Services\Note\Document\Service;

use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Note\Document\Result\ArchivedDocumentResult;
use Bitrix24\SDK\Services\Note\Document\Result\DeletedDocumentResult;
use Bitrix24\SDK\Services\Note\Document\Result\DocumentFieldResult;
use Bitrix24\SDK\Services\Note\Document\Result\DocumentFieldsResult;
use Bitrix24\SDK\Services\Note\Document\Result\DocumentResult;
use Bitrix24\SDK\Services\Note\Document\Result\DocumentsResult;
use Bitrix24\SDK\Services\Note\Document\Result\DocumentState;
use Bitrix24\SDK\Services\Note\Document\Result\UnarchivedDocumentResult;
use Bitrix24\SDK\Services\Note\Document\Result\DocumentSearchFieldResult;
use Bitrix24\SDK\Services\Note\Document\Result\DocumentSearchFieldsResult;
use Bitrix24\SDK\Services\Note\Document\Result\DocumentSearchResult;
use Bitrix24\SDK\Services\Note\Document\Result\DocumentTreeFieldResult;
use Bitrix24\SDK\Services\Note\Document\Result\DocumentTreeFieldsResult;
use Bitrix24\SDK\Services\Note\Document\Result\DocumentTreeResult;
use Bitrix24\SDK\Services\Note\Document\Service\Document;
use Bitrix24\SDK\Services\Note\Document\Service\DocumentFilter;
use Bitrix24\SDK\Services\Note\Document\Service\DocumentListPagination;
use Bitrix24\SDK\Services\Note\Document\Service\DocumentSelectBuilder;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(Document::class)]
class DocumentTest extends TestCase
{
    private Document $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = new Document(new NullCore(), new NullLogger());
    }

    #[Test]
    public function testAddReturnsDocumentResult(): void
    {
        $this->assertInstanceOf(DocumentResult::class, $this->service->add(1, 'Onboarding guide'));
    }

    #[Test]
    public function testArchiveReturnsArchivedDocumentResult(): void
    {
        $this->assertInstanceOf(ArchivedDocumentResult::class, $this->service->archive(1));
    }

    #[Test]
    public function testDeleteReturnsDeletedDocumentResult(): void
    {
        $this->assertInstanceOf(DeletedDocumentResult::class, $this->service->delete(1));
    }

    #[Test]
    public function testFieldGetReturnsDocumentFieldResult(): void
    {
        $this->assertInstanceOf(DocumentFieldResult::class, $this->service->fieldGet('title'));
    }

    #[Test]
    public function testFieldListReturnsDocumentFieldsResult(): void
    {
        $this->assertInstanceOf(DocumentFieldsResult::class, $this->service->fieldList());
    }

    #[Test]
    public function testGetReturnsDocumentResult(): void
    {
        $this->assertInstanceOf(DocumentResult::class, $this->service->get(1));
    }

    #[Test]
    public function testUpdateReturnsDocumentResult(): void
    {
        $this->assertInstanceOf(DocumentResult::class, $this->service->update(1, ['title' => 'Renamed']));
    }

    #[Test]
    public function testTreeListReturnsDocumentTreeResult(): void
    {
        $this->assertInstanceOf(DocumentTreeResult::class, $this->service->treeList(1));
    }

    #[Test]
    public function testTreeFieldGetReturnsDocumentTreeFieldResult(): void
    {
        $this->assertInstanceOf(DocumentTreeFieldResult::class, $this->service->treeFieldGet('title'));
    }

    #[Test]
    public function testTreeFieldListReturnsDocumentTreeFieldsResult(): void
    {
        $this->assertInstanceOf(DocumentTreeFieldsResult::class, $this->service->treeFieldList());
    }

    #[Test]
    public function testSearchListReturnsDocumentSearchResult(): void
    {
        $this->assertInstanceOf(DocumentSearchResult::class, $this->service->searchList('onboarding'));
    }

    #[Test]
    public function testSearchFieldGetReturnsDocumentSearchFieldResult(): void
    {
        $this->assertInstanceOf(DocumentSearchFieldResult::class, $this->service->searchFieldGet('title'));
    }

    #[Test]
    public function testSearchFieldListReturnsDocumentSearchFieldsResult(): void
    {
        $this->assertInstanceOf(DocumentSearchFieldsResult::class, $this->service->searchFieldList());
    }

    #[Test]
    #[TestDox('add() sends collectionId, title, parentId and markdown as nested fields')]
    public function testAddSendsNestedFields(): void
    {
        [$method, $captured] = $this->call(
            static fn (Document $service) => $service->add(1, 'Onboarding guide', 5, '# Hello')
        );

        $this->assertSame('note.document.add', $method);
        $this->assertSame(
            ['collectionId' => 1, 'title' => 'Onboarding guide', 'parentId' => 5, 'markdown' => '# Hello'],
            $captured['fields']
        );
    }

    #[Test]
    #[TestDox('get() builds select from a DocumentSelectBuilder')]
    public function testGetBuildsSelectFromBuilder(): void
    {
        $select = (new DocumentSelectBuilder())->title()->markdown();

        [$method, $captured] = $this->call(static fn (Document $service) => $service->get(1, $select));

        $this->assertSame('note.document.get', $method);
        $this->assertSame($select->buildSelect(), $captured['select']);
    }

    #[Test]
    #[TestDox('update() forwards overwrite flag alongside id and fields')]
    public function testUpdateForwardsOverwriteFlag(): void
    {
        [$method, $captured] = $this->call(
            static fn (Document $service) => $service->update(1, ['markdown' => '# New'], [], true)
        );

        $this->assertSame('note.document.update', $method);
        $this->assertSame(1, $captured['id']);
        $this->assertSame(['markdown' => '# New'], $captured['fields']);
        $this->assertTrue($captured['overwrite']);
    }

    #[Test]
    #[TestDox('treeList() sends collectionId')]
    public function testTreeListSendsCollectionId(): void
    {
        [$method, $captured] = $this->call(static fn (Document $service) => $service->treeList(9));

        $this->assertSame('note.document.tree.list', $method);
        $this->assertSame(9, $captured['collectionId']);
    }

    #[Test]
    #[TestDox('searchList() sends query and optional limit')]
    public function testSearchListSendsQueryAndLimit(): void
    {
        [$method, $captured] = $this->call(static fn (Document $service) => $service->searchList('onboarding', 10));

        $this->assertSame('note.document.search.list', $method);
        $this->assertSame('onboarding', $captured['query']);
        $this->assertSame(10, $captured['pagination']['limit']);
    }

    #[Test]
    public function testListReturnsDocumentsResult(): void
    {
        $this->assertInstanceOf(DocumentsResult::class, $this->service->list());
    }

    #[Test]
    public function testMoveReturnsDocumentResult(): void
    {
        $this->assertInstanceOf(DocumentResult::class, $this->service->move(1));
    }

    #[Test]
    public function testRestoreReturnsDocumentResult(): void
    {
        $this->assertInstanceOf(DocumentResult::class, $this->service->restore(1));
    }

    #[Test]
    public function testUnarchiveReturnsUnarchivedDocumentResult(): void
    {
        $this->assertInstanceOf(UnarchivedDocumentResult::class, $this->service->unarchive(1));
    }

    #[Test]
    #[TestDox('list() without arguments sends an empty REST v3 payload')]
    public function testListWithoutArgumentsSendsEmptyPayload(): void
    {
        [$method, $captured, $version] = $this->call(static fn (Document $service) => $service->list());

        $this->assertSame('note.document.list', $method);
        $this->assertSame([], $captured);
        $this->assertSame(ApiVersion::v3, $version);
    }

    #[Test]
    #[TestDox('list() sends filter builder, select builder and opaque pagination cursor')]
    public function testListSendsFilterSelectAndPagination(): void
    {
        $cursor = ['stream' => 'collections', 'collectionId' => 123, 'id' => 42];
        $filter = new DocumentFilter();
        $filter->collectionId()->eq(123);
        $filter->state()->eq(DocumentState::archived->value);
        $select = (new DocumentSelectBuilder())->title();

        [, $captured] = $this->call(
            static fn (Document $service) => $service->list($filter, $select, new DocumentListPagination(1, $cursor))
        );

        $this->assertSame(
            [
                'filter'     => [['collectionId', '=', 123], ['state', '=', 'archived']],
                'select'     => $select->buildSelect(),
                'pagination' => ['limit' => 1, 'afterCursor' => $cursor],
            ],
            $captured
        );
    }

    #[Test]
    #[TestDox('list() passes an array filter and array select unchanged')]
    public function testListPassesArrayFilterAndSelect(): void
    {
        [, $captured] = $this->call(
            static fn (Document $service) => $service->list([['collectionId', '=', 5]], ['id', 'title'])
        );

        $this->assertSame(['filter' => [['collectionId', '=', 5]], 'select' => ['id', 'title']], $captured);
    }

    #[Test]
    #[TestDox('move() sends only the target coordinates that were given')]
    public function testMoveOmitsNullArguments(): void
    {
        [$method, $captured, $version] = $this->call(static fn (Document $service) => $service->move(7, null, 3));

        $this->assertSame('note.document.move', $method);
        $this->assertSame(['id' => 7, 'parentId' => 3], $captured);
        $this->assertSame(ApiVersion::v3, $version);

        [, $captured] = $this->call(static fn (Document $service) => $service->move(7, 2, 3, 1500));
        $this->assertSame(['id' => 7, 'collectionId' => 2, 'parentId' => 3, 'position' => 1500], $captured);
    }

    #[Test]
    #[TestDox('restore() sends id and the optional target collection')]
    public function testRestoreSendsOptionalCollectionId(): void
    {
        [$method, $captured, $version] = $this->call(static fn (Document $service) => $service->restore(7));

        $this->assertSame('note.document.restore', $method);
        $this->assertSame(['id' => 7], $captured);
        $this->assertSame(ApiVersion::v3, $version);

        [, $captured] = $this->call(static fn (Document $service) => $service->restore(7, 4));
        $this->assertSame(['id' => 7, 'collectionId' => 4], $captured);
    }

    #[Test]
    #[TestDox('unarchive() sends id')]
    public function testUnarchiveSendsId(): void
    {
        [$method, $captured, $version] = $this->call(static fn (Document $service) => $service->unarchive(7));

        $this->assertSame('note.document.unarchive', $method);
        $this->assertSame(['id' => 7], $captured);
        $this->assertSame(ApiVersion::v3, $version);
    }

    /**
     * @return array{0: string, 1: array<string, mixed>, 2: ApiVersion|null}
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
            function (string $apiMethod, array $parameters = [], ?ApiVersion $apiVersion = null) use (&$method, &$captured, &$version, $response): Response {
                $method = $apiMethod;
                $captured = $parameters;
                $version = $apiVersion;

                return $response;
            }
        );

        $action(new Document($core, new NullLogger()));

        return [$method, $captured, $version];
    }
}
