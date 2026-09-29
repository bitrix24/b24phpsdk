<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\Sale\Status\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Sale\Status\Batch as StatusBatch;
use Bitrix24\SDK\Services\Sale\Status\Result\StatusAddBatchResult;
use Bitrix24\SDK\Services\Sale\Status\Result\StatusItemResult;
use Bitrix24\SDK\Services\Sale\Status\Result\StatusUpdateBatchResult;
use Bitrix24\SDK\Services\Sale\Status\Service\Batch;
use Bitrix24\SDK\Tests\Unit\Services\Sale\BatchCoreStubTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Batch::class)]
#[CoversClass(StatusBatch::class)]
class BatchTest extends TestCase
{
    use BatchCoreStubTrait;

    #[Test]
    #[TestDox('add() wraps items into "fields" and returns string status id')]
    public function testAddWrapsFieldsAndReturnsStringId(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): array => [
            'status' => $parameters['fields'],
        ]);

        $results = iterator_to_array($this->makeBatch($core)->add([['id' => 'XA', 'type' => 'O']]), false);

        $this->assertCount(1, $results);
        $this->assertInstanceOf(StatusAddBatchResult::class, $results[0]);
        $this->assertSame('XA', $results[0]->getId());
        $this->assertInstanceOf(StatusItemResult::class, $results[0]->status());
        $this->assertSame(['sale.status.add', ['fields' => ['id' => 'XA', 'type' => 'O']]], $this->capturedCalls[0]);
    }

    #[Test]
    #[TestDox('update() sends string "id" and "fields" parameters')]
    public function testUpdateSendsStringIdAndFields(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): array => [
            'status' => ['id' => $parameters['id']] + $parameters['fields'],
        ]);

        $results = iterator_to_array($this->makeBatch($core)->update(['XA' => ['sort' => 200]]), false);

        $this->assertCount(1, $results);
        $this->assertInstanceOf(StatusUpdateBatchResult::class, $results[0]);
        $this->assertTrue($results[0]->isSuccess());
        $this->assertSame('XA', $results[0]->status()->id);
        $this->assertSame(['sale.status.update', ['id' => 'XA', 'fields' => ['sort' => '200']]], $this->capturedCalls[0]);
    }

    #[Test]
    #[TestDox('delete() sends string "id" parameter')]
    public function testDeleteSendsStringId(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): bool => true);

        $results = iterator_to_array($this->makeBatch($core)->delete(['XA', 'XB']), false);

        $this->assertCount(2, $results);
        $this->assertInstanceOf(DeletedItemBatchResult::class, $results[0]);
        $this->assertTrue($results[1]->isSuccess());
        $this->assertSame(['sale.status.delete', ['id' => 'XA']], $this->capturedCalls[0]);
        $this->assertSame(['sale.status.delete', ['id' => 'XB']], $this->capturedCalls[1]);
    }

    #[Test]
    #[TestDox('delete() rejects non-string identifiers')]
    public function testDeleteRejectsNonStringId(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): bool => true);

        $this->expectException(InvalidArgumentException::class);
        /** @phpstan-ignore argument.type */
        iterator_to_array($this->makeBatch($core)->delete([1]));
    }

    #[Test]
    #[TestDox('list() unwraps "statuses" result key and paginates with "start" parameter')]
    public function testListPaginatesWithStart(): void
    {
        $core = $this->makeCore(
            static function (string $apiMethod, array $parameters): array {
                $start = (int)$parameters['start'];

                return [
                    'statuses' => array_map(
                        static fn (int $number): array => ['id' => 'S' . $number],
                        range($start + 1, min($start + 50, 60))
                    ),
                ];
            },
            60
        );

        $ids = [];
        foreach ($this->makeBatch($core)->list([], ['type' => 'O']) as $item) {
            $this->assertInstanceOf(StatusItemResult::class, $item);
            $ids[] = $item->id;
        }

        $this->assertSame(array_map(static fn (int $number): string => 'S' . $number, range(1, 60)), $ids);
        $this->assertSame('sale.status.list', $this->capturedCalls[0][0]);
        $this->assertSame('50', $this->capturedCalls[2][1]['start']);
        $this->assertSame(['type' => 'O'], $this->capturedCalls[2][1]['filter']);
    }

    private function makeBatch(CoreInterface $core): Batch
    {
        return new Batch(new StatusBatch($core, new NullLogger()), new NullLogger());
    }
}
