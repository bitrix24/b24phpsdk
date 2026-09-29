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

namespace Bitrix24\SDK\Tests\Unit\Services\Sale\Payment\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Sale\Payment\Batch as PaymentBatch;
use Bitrix24\SDK\Services\Sale\Payment\Result\PaymentAddedBatchResult;
use Bitrix24\SDK\Services\Sale\Payment\Result\PaymentItemResult;
use Bitrix24\SDK\Services\Sale\Payment\Result\PaymentUpdatedBatchResult;
use Bitrix24\SDK\Services\Sale\Payment\Service\Batch;
use Bitrix24\SDK\Tests\Unit\Services\Sale\BatchCoreStubTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Batch::class)]
#[CoversClass(PaymentBatch::class)]
class BatchTest extends TestCase
{
    use BatchCoreStubTrait;

    #[Test]
    #[TestDox('add() wraps items into "fields" and returns added item id')]
    public function testAddWrapsFieldsAndReturnsId(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): array => [
            'payment' => ['id' => 11] + $parameters['fields'],
        ]);

        $results = iterator_to_array($this->makeBatch($core)->add([['xmlId' => 'a'], ['xmlId' => 'b']]), false);

        $this->assertCount(2, $results);
        $this->assertInstanceOf(PaymentAddedBatchResult::class, $results[0]);
        $this->assertSame(11, $results[0]->getId());
        $this->assertInstanceOf(PaymentItemResult::class, $results[1]->payment());
        $this->assertSame(['sale.payment.add', ['fields' => ['xmlId' => 'a']]], $this->capturedCalls[0]);
        $this->assertSame(['sale.payment.add', ['fields' => ['xmlId' => 'b']]], $this->capturedCalls[1]);
    }

    #[Test]
    #[TestDox('update() sends lowercase "id" and "fields" parameters')]
    public function testUpdateSendsLowercaseIdAndFields(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): array => [
            'payment' => ['id' => (int)$parameters['id']],
        ]);

        $results = iterator_to_array($this->makeBatch($core)->update([5 => ['xmlId' => 'x']]), false);

        $this->assertCount(1, $results);
        $this->assertInstanceOf(PaymentUpdatedBatchResult::class, $results[0]);
        $this->assertTrue($results[0]->isSuccess());
        $this->assertSame(5, $results[0]->payment()->id);
        $this->assertSame(['sale.payment.update', ['id' => '5', 'fields' => ['xmlId' => 'x']]], $this->capturedCalls[0]);
    }

    #[Test]
    #[TestDox('delete() sends lowercase "id" parameter')]
    public function testDeleteSendsLowercaseId(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): bool => true);

        $results = iterator_to_array($this->makeBatch($core)->delete([7, 8]), false);

        $this->assertCount(2, $results);
        $this->assertInstanceOf(DeletedItemBatchResult::class, $results[0]);
        $this->assertTrue($results[0]->isSuccess());
        $this->assertSame(['sale.payment.delete', ['id' => '7']], $this->capturedCalls[0]);
        $this->assertSame(['sale.payment.delete', ['id' => '8']], $this->capturedCalls[1]);
    }

    #[Test]
    #[TestDox('delete() rejects non-integer identifiers')]
    public function testDeleteRejectsNonIntegerId(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): bool => true);

        $this->expectException(InvalidArgumentException::class);
        /** @phpstan-ignore argument.type */
        iterator_to_array($this->makeBatch($core)->delete(['7']));
    }

    #[Test]
    #[TestDox('list() unwraps "payments" result key and adds "id" to select')]
    public function testListUnwrapsResultKeyAndAddsIdToSelect(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): array => [
            'payments' => [['id' => 1], ['id' => 2], ['id' => 3]],
        ]);

        $items = iterator_to_array($this->makeBatch($core)->list(['xmlId'], ['>id' => 0], [], 2), false);

        $this->assertCount(2, $items);
        $this->assertInstanceOf(PaymentItemResult::class, $items[0]);
        $this->assertSame(2, $items[1]->id);
        $this->assertSame('sale.payment.list', $this->capturedCalls[0][0]);
        $this->assertSame(['xmlId', 'id'], $this->capturedCalls[0][1]['select']);
        $this->assertSame(['id' => 'ASC'], $this->capturedCalls[0][1]['order']);
    }

    #[Test]
    #[TestDox('list() chains batch queries by the last "id" in the "payments" result key')]
    public function testListChainsBatchQueriesByLastId(): void
    {
        $core = $this->makeCore(
            static function (string $apiMethod, array $parameters): array {
                $lastId = $parameters['filter']['>id'] ?? '0';
                $firstId = str_starts_with((string)$lastId, '$result') ? 101 : (int)$lastId + 1;

                return [
                    'payments' => array_map(
                        static fn (int $id): array => ['id' => $id],
                        range($firstId, min($firstId + 49, 120))
                    ),
                ];
            },
            120
        );

        $ids = [];
        foreach ($this->makeBatch($core)->list() as $item) {
            $ids[] = $item->id;
        }

        $this->assertSame(range(1, 120), $ids);
        $this->assertSame(['>id' => '50'], $this->capturedCalls[1][1]['filter']);
        $this->assertSame(['>id' => '$result[cmd_0][payments][49][id]'], $this->capturedCalls[2][1]['filter']);
    }

    private function makeBatch(CoreInterface $core): Batch
    {
        return new Batch(new PaymentBatch($core, new NullLogger()), new NullLogger());
    }
}
