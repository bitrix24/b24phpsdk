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

namespace Bitrix24\SDK\Tests\Unit\Services\Note\Collection\Service;

use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Note\Collection\Result\ArchivedCollectionResult;
use Bitrix24\SDK\Services\Note\Collection\Result\CollectionFieldResult;
use Bitrix24\SDK\Services\Note\Collection\Result\CollectionFieldsResult;
use Bitrix24\SDK\Services\Note\Collection\Result\CollectionResult;
use Bitrix24\SDK\Services\Note\Collection\Result\CollectionsResult;
use Bitrix24\SDK\Services\Note\Collection\Result\DeletedCollectionResult;
use Bitrix24\SDK\Services\Note\Collection\Service\Collection;
use Bitrix24\SDK\Services\Note\Collection\Service\CollectionListCursor;
use Bitrix24\SDK\Services\Note\Collection\Service\CollectionListPagination;
use Bitrix24\SDK\Services\Note\Collection\Service\CollectionSelectBuilder;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(Collection::class)]
class CollectionTest extends TestCase
{
    private Collection $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = new Collection(new NullCore(), new NullLogger());
    }

    #[Test]
    public function testAddReturnsCollectionResult(): void
    {
        $this->assertInstanceOf(CollectionResult::class, $this->service->add('Sales KB'));
    }

    #[Test]
    public function testArchiveReturnsArchivedCollectionResult(): void
    {
        $this->assertInstanceOf(ArchivedCollectionResult::class, $this->service->archive(1));
    }

    #[Test]
    public function testDeleteReturnsDeletedCollectionResult(): void
    {
        $this->assertInstanceOf(DeletedCollectionResult::class, $this->service->delete(1));
    }

    #[Test]
    public function testFieldGetReturnsCollectionFieldResult(): void
    {
        $this->assertInstanceOf(CollectionFieldResult::class, $this->service->fieldGet('name'));
    }

    #[Test]
    public function testFieldListReturnsCollectionFieldsResult(): void
    {
        $this->assertInstanceOf(CollectionFieldsResult::class, $this->service->fieldList());
    }

    #[Test]
    public function testGetReturnsCollectionResult(): void
    {
        $this->assertInstanceOf(CollectionResult::class, $this->service->get(1));
    }

    #[Test]
    public function testListReturnsCollectionsResult(): void
    {
        $this->assertInstanceOf(CollectionsResult::class, $this->service->list());
    }

    #[Test]
    public function testUpdateReturnsCollectionResult(): void
    {
        $this->assertInstanceOf(CollectionResult::class, $this->service->update(1, ['name' => 'Renamed']));
    }

    #[Test]
    #[TestDox('add() calls note.collection.add with a nested fields object')]
    public function testAddSendsNestedFields(): void
    {
        [$method, $captured] = $this->call(static fn (Collection $service) => $service->add('Sales KB', 5));

        $this->assertSame('note.collection.add', $method);
        $this->assertSame(['fields' => ['name' => 'Sales KB', 'position' => 5]], $captured);
    }

    #[Test]
    #[TestDox('get() forwards a plain select array unchanged')]
    public function testGetForwardsPlainSelect(): void
    {
        [$method, $captured] = $this->call(static fn (Collection $service) => $service->get(42, ['id', 'name']));

        $this->assertSame('note.collection.get', $method);
        $this->assertSame(42, $captured['id']);
        $this->assertSame(['id', 'name'], $captured['select']);
    }

    #[Test]
    #[TestDox('get() builds select from a CollectionSelectBuilder')]
    public function testGetBuildsSelectFromBuilder(): void
    {
        $select = (new CollectionSelectBuilder())->name()->position();

        [$method, $captured] = $this->call(static fn (Collection $service) => $service->get(42, $select));

        $this->assertSame('note.collection.get', $method);
        $this->assertSame($select->buildSelect(), $captured['select']);
    }

    #[Test]
    #[TestDox('list() sends no pagination key when called without arguments')]
    public function testListWithoutPaginationSendsEmptyPayload(): void
    {
        [$method, $captured] = $this->call(static fn (Collection $service) => $service->list());

        $this->assertSame('note.collection.list', $method);
        $this->assertArrayNotHasKey('pagination', $captured);
    }

    #[Test]
    #[TestDox('list() forwards limit and afterCursor from a typed CollectionListPagination')]
    public function testListForwardsTypedPagination(): void
    {
        $pagination = new CollectionListPagination(50, new CollectionListCursor(10, 99));

        [$method, $captured] = $this->call(static fn (Collection $service) => $service->list($pagination));

        $this->assertSame('note.collection.list', $method);
        $this->assertSame(
            ['limit' => 50, 'afterCursor' => ['position' => 10, 'id' => 99]],
            $captured['pagination']
        );
    }

    #[Test]
    #[TestDox('delete() forwards id when provided')]
    public function testDeleteForwardsId(): void
    {
        [$method, $captured] = $this->call(static fn (Collection $service) => $service->delete(7));

        $this->assertSame('note.collection.delete', $method);
        $this->assertSame(7, $captured['id']);
        $this->assertArrayNotHasKey('filter', $captured);
    }

    #[Test]
    #[TestDox('delete() forwards filter when no id is given')]
    public function testDeleteForwardsFilter(): void
    {
        [$method, $captured] = $this->call(
            static fn (Collection $service) => $service->delete(null, [['id', '>=', 10]])
        );

        $this->assertSame('note.collection.delete', $method);
        $this->assertArrayNotHasKey('id', $captured);
        $this->assertSame([['id', '>=', 10]], $captured['filter']);
    }

    #[Test]
    #[TestDox('update() sends id and nested fields')]
    public function testUpdateSendsIdAndFields(): void
    {
        [$method, $captured] = $this->call(
            static fn (Collection $service) => $service->update(3, ['name' => 'New name'])
        );

        $this->assertSame('note.collection.update', $method);
        $this->assertSame(3, $captured['id']);
        $this->assertSame(['name' => 'New name'], $captured['fields']);
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

        $action(new Collection($core, new NullLogger()));

        return [$method, $captured];
    }
}
